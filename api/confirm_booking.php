<?php
require_once 'config.php'; // <--- LINE 1: Load Database first
session_start();           // <--- LINE 2: Start Session

$title = "Booking Error";
$header_message = "Booking Failed! ⚠️";
$messages = [];
$success = false;

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
    $status = 'Pending';

    if (empty($carName) || empty($pickupDate) || empty($returnDate) || empty($location) || empty($payment)) {
        $messages[] = "Please fill out all required fields.";
    }

    if (empty($messages)) {
        $sql = "INSERT INTO bookings (user_id, car_id, car_name, pickup_date, return_date, location, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssssssss', $userId, $carId, $carName, $pickupDate, $returnDate, $location, $payment, $status);
            if ($stmt->execute()) {
                $bookingId = $conn->insert_id;
                if ($payment === 'Credit Card' || $payment === 'GCash') {
                    header("Location: payment.php?booking_id=" . $bookingId);
                    exit;
                }
                $success = true;
                $title = "Booking Request Sent";
                $header_message = "Request Sent! ⏳";
                $messages[] = "Your booking request has been sent for approval.";
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
      .success-message { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
      .error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body class="booking-confirm">
    <header><div class="navbar"><div class="nav-left"><a href="index.php">Home</a></div></div></header>
    <main>
        <section class="confirmation-box">
            <h1><?php echo $header_message; ?></h1>
            <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                <?php foreach ($messages as $msg): ?>
                    <p><?php echo $msg; ?></p>
                <?php endforeach; ?>
            </div>
            <p><a href="index.php">Back to Home</a></p>
        </section>
    </main>
</body>
</html>
