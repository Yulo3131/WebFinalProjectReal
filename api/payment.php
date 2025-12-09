<?php
// filepath: api/payment.php
require_once 'config.php';
session_start();

// 1. Security Check
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$booking_id = $_GET['booking_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$message = '';
$success = false;

// 2. Fetch Booking Details
$sql = "
    SELECT b.*, c.price as daily_rate, c.image 
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'Pending'
    LIMIT 1
";

$booking = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
}

if (!$booking) {
    header("Location: index.php");
    exit;
}

// 3. Calculate Total Price
$pickup = new DateTime($booking['pickup_date']);
$return = new DateTime($booking['return_date']);
$interval = $pickup->diff($return);
$days = $interval->days + 1; 
$total_price = $days * $booking['daily_rate'];

// 4. Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, you would verify the Reference No. here
    $updateSql = "UPDATE bookings SET status = 'Confirmed' WHERE id = ?";
    if ($upStmt = $conn->prepare($updateSql)) {
        $upStmt->bind_param("i", $booking_id);
        if ($upStmt->execute()) {
            $success = true;
        }
        $upStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay with GCash - GingerRental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container { max-width: 500px; margin: 3rem auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .payment-header { border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .amount-box { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #e9ecef; }
        .amount-value { font-size: 2rem; color: #1f4e79; font-weight: 800; }
        .qr-box { margin: 20px 0; padding: 15px; border: 2px dashed #007bff; border-radius: 10px; background: #f0f8ff; display: inline-block; }
        .qr-box img { width: 200px; height: 200px; object-fit: contain; }
        .card-form label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; text-align: left; }
        .card-form input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 1rem; font-size: 1rem; }
        .pay-btn { width: 100%; background: #007bff; color: white; padding: 14px; border: none; border-radius: 6px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: background 0.3s; }
        .pay-btn:hover { background: #0056b3; }
        .success-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.95); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1000; }
        .check-icon { font-size: 5rem; color: #28a745; margin-bottom: 1rem; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<?php if ($success): ?>
<div class="success-overlay">
    <div class="check-icon">✓</div>
    <h2>Payment Submitted!</h2>
    <p>We will verify your payment shortly.</p>
    <a href="index.php" class="cta-button" style="margin-top: 1rem;">Back to Home</a>
</div>
<?php endif; ?>

<main class="main-container">
    <div class="payment-container">
        <div class="payment-header">
            <h2>Scan to Pay</h2>
            <p>Please send the exact amount to our GCash.</p>
        </div>

        <div class="amount-box">
            <div style="font-size: 0.9rem; text-transform: uppercase; color: #666;">Total Amount</div>
            <div class="amount-value">₱<?php echo number_format($total_price, 2); ?></div>
        </div>

        <div class="qr-box">
            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" alt="GCash QR Code">
            <p style="margin: 10px 0 0 0; font-weight: 600; color: #007bff;">GingerRental Corp</p>
            <p style="margin: 0; font-size: 0.9rem;">0917 123 4567</p>
        </div>

        <form method="POST" class="card-form" style="margin-top: 1rem;">
            <label for="ref_no">Reference Number (Ref No.)</label>
            <input type="text" id="ref_no" name="ref_no" placeholder="e.g. 10023456789" required minlength="6">
            
            <button type="submit" class="pay-btn">I Have Paid</button>
            <div style="text-align:center; margin-top:1rem;">
                <a href="index.php" style="color: #666; font-size: 0.9rem;">Cancel Payment</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
