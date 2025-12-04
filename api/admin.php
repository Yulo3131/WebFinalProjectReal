<?php
session_start();
require_once 'config.php';

// --- 1. SECURITY: CHECK IF ADMIN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT role, fullname FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// If user is NOT an admin, kick them back to home
if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- 2. HANDLE ACTIONS (Approve/Reject/Complete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['booking_id'])) {
        $newStatus = $_POST['action'];
        $bookingId = intval($_POST['booking_id']);
        
        $updateSql = "UPDATE bookings SET status = ? WHERE id = ?";
        if ($upStmt = $conn->prepare($updateSql)) {
            $upStmt->bind_param("si", $newStatus, $bookingId);
            $upStmt->execute();
            $upStmt->close();
        }
    }
    // Refresh page to show updates
    header("Location: admin.php");
    exit;
}

// --- 3. FETCH DATA ---

// Get Booking Requests (Who availed?)
$bookings_query = "
    SELECT b.*, u.fullname, u.email, c.name as car_name
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
";
$bookings = $conn->query($bookings_query);

// Get Fleet Status (Which cars are rented today?)
$fleet_query = "
    SELECT 
        c.*,
        (
            SELECT CONCAT(u.fullname, ' (until ', DATE_FORMAT(b.return_date, '%M %d'), ')')
            FROM bookings b 
            JOIN users u ON b.user_id = u.id
            WHERE b.car_id = c.id 
            AND b.status = 'Confirmed' 
            AND CURDATE() BETWEEN b.pickup_date AND b.return_date
            LIMIT 1
        ) as current_renter
    FROM cars c
";
$fleet = $conn->query($fleet_query);

$total_bookings = $bookings->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel - GingerRental</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <style>
    /* Admin Specific Styles */
    body { background-color: #f4f6f9; }
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    
    .admin-header {
        display: flex; justify-content: space-between; align-items: center;
        background: #1f4e79; color: white; padding: 15px 20px; border-radius: 8px;
        margin-bottom: 30px;
    }
    .admin-header h1 { margin: 0; font-size: 1.5rem; }
    .admin-header a { color: #ffbb33; text-decoration: none; font-weight: bold; }

    h2 { color: #1f4e79; border-left: 5px solid #ffbb33; padding-left: 15px; margin-top: 30px;}

    /* Fleet Grid */
    .fleet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .fleet-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: relative;}
    .fleet-card img { width: 100%; height: 160px; object-fit: cover; }
    .fleet-info { padding: 15px; }
    .status-badge { 
        position: absolute; top: 10px; right: 10px; 
        padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;
    }
    .status-available { background: #28a745; color: white; }
    .status-rented { background: #dc3545; color: white; }

    /* Bookings Table */
    .table-responsive { overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    th { background: #e9ecef; color: #333; text-align: left; padding: 12px; font-size: 0.9rem; }
    td { padding: 12px; border-bottom: 1px solid #eee; }
    
    .btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; color: white; font-size: 0.8rem; }
    .btn-approve { background: #28a745; }
    .btn-reject { background: #dc3545; }
    .btn-complete { background: #17a2b8; }

    .badge { padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold; }
    .badge-Pending { background: #fff3cd; color: #856404; }
    .badge-Confirmed { background: #d4edda; color: #155724; }
    .badge-Cancelled { background: #f8d7da; color: #721c24; }
    .badge-Completed { background: #cce5ff; color: #004085; }
  </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div>
            <h1>Admin Panel</h1>
            <small>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></small>
        </div>
        <div>
            <a href="index.php" style="margin-right: 15px;">View Website</a>
            <a href="logout.php" style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">Logout</a>
        </div>
    </div>

    <!-- FLEET STATUS -->
    <h2>Fleet Status (What car is available?)</h2>
    <div class="fleet-grid">
        <?php while($car = $fleet->fetch_assoc()): ?>
            <?php $isRented = !empty($car['current_renter']); ?>
            <div class="fleet-card">
                <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="Car">
                <span class="status-badge <?php echo $isRented ? 'status-rented' : 'status-available'; ?>">
                    <?php echo $isRented ? 'Rented' : 'Available'; ?>
                </span>
                <div class="fleet-info">
                    <strong><?php echo htmlspecialchars($car['name']); ?></strong>
                    <?php if ($isRented): ?>
                        <div style="color: #dc3545; font-size: 0.9rem; margin-top: 5px;">
                            👤 <?php echo htmlspecialchars($car['current_renter']); ?>
                        </div>
                    <?php else: ?>
                        <div style="color: #28a745; font-size: 0.9rem; margin-top: 5px;">
                            ✅ Ready for pickup
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- BOOKING REQUESTS -->
    <h2>Booking Requests (Who availed?)</h2>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings->num_rows > 0): ?>
                    <?php while($row = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                            <td>
                                <?php echo date('M d', strtotime($row['pickup_date'])); ?> to 
                                <?php echo date('M d', strtotime($row['return_date'])); ?>
                            </td>
                            <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td>
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="Confirmed" class="btn btn-approve">Approve</button>
                                        <button type="submit" name="action" value="Cancelled" class="btn btn-reject">Reject</button>
                                    </form>
                                <?php elseif ($row['status'] == 'Confirmed'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="Completed" class="btn btn-complete">Mark Returned</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#aaa;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>