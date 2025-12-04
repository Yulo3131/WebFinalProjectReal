<?php
// filepath: GingerRental/confirm_booking.php
session_start();
require_once 'config.php';

$title = "Booking Error";
$header_message = "Booking Failed! ⚠️";
$messages = [];
$success = false;

// Check if the user is logged in
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

    // Basic validation
    if (empty($carName) || empty($pickupDate) || empty($returnDate) || empty($location) || empty($payment)) {
        $messages[] = "Please fill out all required fields.";
    } elseif (strtotime($pickupDate) > strtotime($returnDate)) {
        $messages[] = "Return date must be the same as or after the pickup date.";
    }

    if (empty($messages)) {
        // SQL statement assumes a 'bookings' table exists.
        $sql = "INSERT INTO bookings (user_id, car_id, car_name, pickup_date, return_date, location, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // carId is passed as a string/null, assuming database can handle it.
            $stmt->bind_param('ssssssss', $userId, $carId, $carName, $pickupDate, $returnDate, $location, $payment, $status);

            if ($stmt->execute()) {
                $bookingId = $conn->insert_id;
                $success = true;
                $title = "Booking Confirmed";
                $header_message = "Booking Confirmed! 🎉";
                $messages[] = "Your reservation for the " . htmlspecialchars($carName) . " has been successfully submitted.";
                $messages[] = "Your reference number is: <strong>" . $bookingId . "</strong>. You will be contacted soon.";
            } else {
                $messages[] = "Booking failed. Database error.";
            }
            $stmt->close();
        } else {
            $messages[] = "Database preparation error: " . $conn->error;
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
      .confirmation-box {
        text-align: center;
        padding: 40px;
        border-radius: 10px;
        background: #f7f7f7;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        color: #333;
        max-width: 600px;
        width: 90%;
      }
      .success-message {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
      }
      .error-message {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
      }
      h1 { margin-bottom: 20px; }
    </style>
</head>
<body class="booking-confirm">
    <header>
        <div class="navbar">
          <div class="nav-left"><a href="index.php">Home</a></div>
          <div class="nav-right"><a href="logout.php">Logout</a></div>
        </div>
    </header>
    <main>
        <section class="confirmation-box">
            <h1><?php echo $header_message; ?></h1>
            <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                <?php foreach ($messages as $msg): ?>
                    <p><?php echo $msg; ?></p>
                <?php endforeach; ?>
            </div>
            <p><a href="index.php">Continue browsing cars</a> or <a href="booking.php">make a new reservation</a>.</p>
        </section>
    </main>
</body>
</html>