<?php
require_once 'config.php';
session_start();

$title = "Booking Error";
$header_message = "Booking Failed! ⚠️";
$messages = [];
$success = false;
$reservationDate = '';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $carId = $_POST['car_id'] ?? null; 
    $carName = $_POST['car'] ?? '';
    $pickupDate = $_POST['pickup_date'] ?? '';
    $returnDate = $_POST['return_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $payment = $_POST['payment'] ?? '';
    $refNo = $_POST['ref_no'] ?? '';
    
    // Status Logic: 
    // If GCash and Ref No is provided -> Confirmed (Simulated Success)
    // If Cash -> Pending (Approval needed)
    $status = 'Pending';
    if ($payment === 'GCash' && !empty($refNo)) {
        $status = 'Confirmed';
    }

    if (empty($carName) || empty($pickupDate) || empty($returnDate) || empty($location) || empty($payment)) {
        $messages[] = "Please fill out all required fields.";
    }

    if (empty($messages)) {
        // Save Ref No in the location or a new column if you had one. 
        // For now, we'll append it to the location or payment method text for admin visibility
        if ($payment === 'GCash') {
            $payment .= " (Ref: $refNo)";
        }

        $sql = "INSERT INTO bookings (user_id, car_id, car_name, pickup_date, return_date, location, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssssssss', $userId, $carId, $carName, $pickupDate, $returnDate, $location, $payment, $status);
            if ($stmt->execute()) {
                $success = true;
                $reservationDate = date('F j, Y', strtotime($pickupDate));
                
                // CUSTOM SUCCESS MESSAGES BASED ON FLOW
                if ($status === 'Confirmed') {
                    $title = "Reservation Success";
                    $header_message = "Car has been reserved! 🚗";
                    $messages[] = "Your payment (Ref: $refNo) has been received.";
                    $messages[] = "<strong>Car has been reserved for $reservationDate.</strong>";
                } else {
                    $title = "Booking Request Sent";
                    $header_message = "Request Sent! ⏳";
                    $messages[] = "You selected Cash on Pickup.";
                    $messages[] = "Your car has been reserved for <strong>$reservationDate</strong> (Subject to final approval).";
                }
            } else {
                $messages[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $messages[] = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
      main { display: flex; justify-content: center; align-items: center; min-height: 80vh; }
      .confirmation-box { text-align: center; padding: 40px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 10px; max-width: 600px; width: 90%; }
      .success-message { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
      .error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
      .success-icon { font-size: 4rem; margin-bottom: 1rem; display:block; }
    </style>
</head>
<body class="booking-confirm">
    <header><div class="navbar"><div class="nav-left"><a href="index.php">Home</a></div></div></header>
    <main>
        <section class="confirmation-box">
            <?php if ($success): ?>
                <div class="success-icon">✅</div>
            <?php endif; ?>
            
            <h1><?php echo $header_message; ?></h1>
            
            <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                <?php foreach ($messages as $msg): ?>
                    <p style="margin: 5px 0; font-size: 1.1rem;"><?php echo $msg; ?></p>
                <?php endforeach; ?>
            </div>
            
            <p><a href="index.php" class="cta-button">Back to Home</a></p>
        </section>
    </main>
</body>
</html>
