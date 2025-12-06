<?php
require_once 'config.php'; // <--- LINE 1: Load Database first
session_start();           // <--- LINE 2: Start Session

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

// --- 2. CAR DATA (Hardcoded to match booking.php) ---
// This ensures images show up even if your database 'cars' table is empty.
$car_db = [
    1 => ['name' => 'Toyota Vios', 'image' => 'https://imgcdn.zigwheels.ph/large/gallery/exterior/30/1943/toyota-vios-front-angle-low-view-945824.jpg'],
    2 => ['name' => 'Honda CR-V', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThGoeYHIKudbhxg34cykLXg4_C7A1UolQZKw&s'],
    3 => ['name' => 'Nissan Urvan', 'image' => 'https://imgcdn.zigwheels.ph/large/gallery/exterior/25/717/nissan-nv350-urvan-front-angle-low-view-612406.jpg'],
    4 => ['name' => 'Toyota Innova', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLhOIJz6rXbQ9h2b8ZCY12zSlaiUAcEpbluQ&s'],
    5 => ['name' => 'Mitsubishi Montero Sport', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm_zSicaU3zAvLnhn--tc6q32ZP8T4Myd-5A&s'],
    6 => ['name' => 'Suzuki Ertiga', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTA1GNFpWalPA97sUepRUwH6DRhuvTFRkfJUg&s'],
    7 => ['name' => 'Ford Ranger', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm_zSicaU3zAvLnhn--tc6q32ZP8T4Myd-5A&s'],
    8 => ['name' => 'Hyundai Accent', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThGoeYHIKudbhxg34cykLXg4_C7A1UolQZKw&s']
];

// --- 3. HANDLE APPROVAL ACTIONS ---
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

// --- 4. FETCH BOOKING REQUESTS (All History) ---
$requests_query = "
    SELECT b.*, u.fullname, u.email
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
    ORDER BY FIELD(b.status, 'Pending', 'Confirmed', 'Completed', 'Cancelled'), b.created_at DESC
";
$requests = $conn->query($requests_query);

// --- 5. FETCH ACTIVE RENTALS (Confirmed Only) ---
$active_query = "
    SELECT b.*, u.fullname
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.status = 'Confirmed'
    ORDER BY b.return_date ASC
";
$active_rentals = $conn->query($active_query);
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
    .btn-return { background: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }

    /* Active Fleet Grid */
    .fleet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .fleet-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; border-left: 5px solid #28a745; }
    .fleet-card img { width: 100%; height: 150px; object-fit: cover; background: #eee; }
    .fleet-info { padding: 15px; }
    .fleet-info h3 { margin: 0 0 5px 0; color: #1f4e79; font-size: 1.1rem; }
    .fleet-info p { margin: 0; font-size: 0.9rem; color: #555; }
    .rented-badge { position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }

    table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #e9ecef; }
  </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <div>
            <a href="index.php" style="color: #ffbb33; margin-right: 15px;">View Site</a>
            <a href="logout.php" style="color: #fff; text-decoration: underline;">Logout</a>
        </div>
    </div>

    <h2>Reserved Cars (Active Rentals)</h2>
    
    <?php if ($active_rentals && $active_rentals->num_rows > 0): ?>
        <div class="fleet-grid">
            <?php while($row = $active_rentals->fetch_assoc()): ?>
                <?php 
                    // Use Hardcoded Image if available, otherwise fallback
                    $carId = $row['car_id'];
                    $imgSrc = $car_db[$carId]['image'] ?? ''; 
                ?>
                <div class="fleet-card">
                    <span class="rented-badge">CONFIRMED</span>
                    
                    <?php if (!empty($imgSrc)): ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Car">
                    <?php else: ?>
                        <div style="height:150px; background: #ddd; display:flex; align-items:center; justify-content:center; color:#777;">
                            No Image Found
                        </div>
                    <?php endif; ?>
                    
                    <div class="fleet-info">
                        <h3><?php echo htmlspecialchars($row['car_name']); ?></h3>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['fullname']); ?></p>
                        <p><strong>Return:</strong> <?php echo date('M d, Y', strtotime($row['return_date'])); ?></p>
                        <p style="margin-top:8px; font-size:0.85rem; color:#888;">
                            Ref ID: #<?php echo $row['id']; ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="background:white; padding:20px; border-radius:8px; margin-bottom:30px; text-align:center; color:#666;">
            No confirmed reservations yet. Accept a booking request below!
        </div>
    <?php endif; ?>

    <h2>Booking Management</h2>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($requests && $requests->num_rows > 0): ?>
                    <?php while($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['fullname'] ?? 'Guest'); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['email'] ?? '-'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                            <td>
                                <?php echo date('M d', strtotime($row['pickup_date'])); ?> - 
                                <?php echo date('M d', strtotime($row['return_date'])); ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $row['status']; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <form method="POST" style="display:inline-flex; gap:5px;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="Confirmed" class="btn-approve" title="Approve">✓ Accept</button>
                                        <button type="submit" name="action" value="Cancelled" class="btn-reject" title="Reject">✕ Reject</button>
                                    </form>
                                <?php elseif ($row['status'] == 'Confirmed'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="Completed" class="btn-return" title="Mark Returned">⟲ Return Car</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#aaa;">-</span>
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
