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

// 2. Fetch Booking Details to verify ownership and calculate price
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
    // If booking not found or already confirmed, redirect
    header("Location: index.php");
    exit;
}

// 3. Calculate Total Price
$pickup = new DateTime($booking['pickup_date']);
$return = new DateTime($booking['return_date']);
$interval = $pickup->diff($return);
$days = $interval->days + 1; // Include pickup day
$total_price = $days * $booking['daily_rate'];

// 4. Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate processing...
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
    <title>Secure Payment - GingerRental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container { max-width: 500px; margin: 3rem auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .payment-header { text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .amount-box { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; text-align: center; margin-bottom: 2rem; border: 1px solid #e9ecef; }
        .amount-label { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .amount-value { font-size: 2.5rem; color: #1f4e79; font-weight: 800; margin-top: 0.5rem; }
        .card-form label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
        .card-form input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 1rem; font-size: 1rem; }
        .pay-btn { width: 100%; background: #28a745; color: white; padding: 14px; border: none; border-radius: 6px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: background 0.3s; }
        .pay-btn:hover { background: #218838; }
        .success-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.95); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1000; }
        .check-icon { font-size: 5rem; color: #28a745; margin-bottom: 1rem; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<?php if ($success): ?>
<div class="success-overlay">
    <div class="check-icon">✓</div>
    <h2>Payment Successful!</h2>
    <p>Your booking for <strong><?php echo htmlspecialchars($booking['car_name']); ?></strong> is now confirmed.</p>
    <a href="index.php" class="cta-button" style="margin-top: 1rem;">Back to Home</a>
</div>
<?php endif; ?>

<main class="main-container">
    <div class="payment-container">
        <div class="payment-header">
            <h2>Complete Payment</h2>
            <p>Method: <strong><?php echo htmlspecialchars($booking['payment_method']); ?></strong></p>
        </div>

        <div class="amount-box">
            <div class="amount-label">Total to Pay</div>
            <div class="amount-value">₱<?php echo number_format($total_price, 2); ?></div>
            <div style="margin-top: 0.5rem; color: #666; font-size: 0.9rem;">
                (<?php echo $days; ?> days × ₱<?php echo number_format($booking['daily_rate']); ?>)
            </div>
        </div>

        <form method="POST" class="card-form">
            <?php if ($booking['payment_method'] === 'Credit Card'): ?>
                <label>Card Number</label>
                <input type="text" placeholder="0000 0000 0000 0000" required maxlength="19">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>Expiry</label>
                        <input type="text" placeholder="MM/YY" required maxlength="5">
                    </div>
                    <div>
                        <label>CVC</label>
                        <input type="text" placeholder="123" required maxlength="3">
                    </div>
                </div>
            <?php else: ?>
                <label>GCash Number</label>
                <input type="text" placeholder="09XX XXX XXXX" required>
            <?php endif; ?>
            
            <button type="submit" class="pay-btn">Pay Now ₱<?php echo number_format($total_price, 2); ?></button>
            <div style="text-align:center; margin-top:1rem;">
                <a href="index.php" style="color: #666; font-size: 0.9rem;">Cancel Payment</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>