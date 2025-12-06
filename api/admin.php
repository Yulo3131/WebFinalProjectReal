<?php
require_once 'config.php'; // <--- THIS MUST BE FIRST
session_start();           // <--- THIS MUST BE SECOND

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

if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- 2. HANDLE ACTIONS ---
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
    header("Location: admin.php");
    exit;
}

// --- 3. FETCH DATA ---
$bookings_query = "SELECT b.*, u.fullname, u.email, c.name as car_name FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC";
$bookings = $conn->query($bookings_query);

$fleet_query = "SELECT c.*, (SELECT CONCAT(u.fullname, ' (until ', DATE_FORMAT(b.return_date, '%M %d'), ')') FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.car_id = c.id AND b.status = 'Confirmed' AND CURDATE() BETWEEN b.pickup_date AND b.return_date LIMIT 1) as current_renter FROM cars c";
$fleet = $conn->query($fleet_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <style>
    body { background-color: #f4f6f9; color: #333; }
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; background: #1f4e79; color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; }
    .badge { padding: 5px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
    .badge-Pending { background: #fff3cd; color: #856404; }
    .badge-Confirmed { background: #d4edda; color: #155724; }
    .badge-Cancelled { background: #f8d7da; color: #721c24; }
    .badge-Completed { background: #cce5ff; color: #004085; }
    .btn-approve { background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    .btn-reject { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #e9ecef; }
  </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <div><a href="index.php" style="color: #ffbb33; margin-right: 15px;">View Site</a><a href="logout.php" style="color: #fff; text-decoration: underline;">Logout</a></div>
    </div>

    <h2>Booking Requests</h2>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Ref ID</th><th>Customer</th><th>Car</th><th>Dates</th><th>Status</th><th>Approve / Reject</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <?php while($row = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br><small><?php echo htmlspecialchars($row['email']); ?></small></td>
                            <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                            <td><?php echo date('M d', strtotime($row['pickup_date'])); ?> - <?php echo date('M d', strtotime($row['return_date'])); ?></td>
                            <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td>
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <form method="POST" style="display:inline-flex; gap:5px;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="Confirmed" class="btn-approve">✓ Accept</button>
                                        <button type="submit" name="action" value="Cancelled" class="btn-reject">✕ Reject</button>
                                    </form>
                                <?php else: ?>
                                    <?php echo $row['status']; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 2rem;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
