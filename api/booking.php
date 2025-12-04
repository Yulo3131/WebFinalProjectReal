<?php
// filepath: api/booking.php
require_once 'config.php'; // 1. Load config FIRST
session_start();           // 2. Then start session

// 1. Enforce Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?next=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$fullname = htmlspecialchars($_SESSION['fullname']);

// 2. Car Data
$cars = [
    ['id'=>1,'slug'=>'toyota-vios','name'=>'Toyota Vios','image'=>'https://imgcdn.zigwheels.ph/large/gallery/exterior/30/1943/toyota-vios-front-angle-low-view-945824.jpg','price'=>1500,'desc'=>'Reliable sedan, great on gas and comfortable for city trips.'],
    ['id'=>2,'slug'=>'honda-crv','name'=>'Honda CR-V','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThGoeYHIKudbhxg34cykLXg4_C7A1UolQZKw&s','price'=>3200,'desc'=>'Spacious SUV for family trips with modern features.'],
    ['id'=>3,'slug'=>'nissan-urvan','name'=>'Nissan Urvan','image'=>'https://imgcdn.zigwheels.ph/large/gallery/exterior/25/717/nissan-nv350-urvan-front-angle-low-view-612406.jpg','price'=>4000,'desc'=>'Large van ideal for group travels and tours.'],
    ['id'=>4,'slug'=>'toyota-innova','name'=>'Toyota Innova','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLhOIJz6rXbQ9h2b8ZCY12zSlaiUAcEpbluQ&s','price'=>2200,'desc'=>'Versatile MPV with roomy interior — perfect for families and long drives.'],
    ['id'=>5,'slug'=>'mitsubishi-montero-sport','name'=>'Mitsubishi Montero Sport','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm_zSicaU3zAvLnhn--tc6q32ZP8T4Myd-5A&s','price'=>3500,'desc'=>'Robust SUV with strong towing capacity and comfortable cabin.'],
    ['id'=>6,'slug'=>'suzuki-ertiga','name'=>'Suzuki Ertiga','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTA1GNFpWalPA97sUepRUwH6DRhuvTFRkfJUg&s','price'=>1400,'desc'=>'Affordable 7-seater MPV ideal for city and short intercity trips.'],
    ['id'=>7,'slug'=>'ford-ranger','name'=>'Ford Ranger','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm_zSicaU3zAvLnhn--tc6q32ZP8T4Myd-5A&s','price'=>4200,'desc'=>'Solid pickup truck with strong performance for work or adventure.'],
    ['id'=>8,'slug'=>'hyundai-accent','name'=>'Hyundai Accent','image'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThGoeYHIKudbhxg34cykLXg4_C7A1UolQZKw&s','price'=>1300,'desc'=>'Compact and fuel-efficient, great for city driving.']
];

// 3. Identify Selected Car
$selectedSlug = $_GET['car'] ?? '';
$selectedId = $_GET['id'] ?? '';
$selectedCar = null;

if ($selectedId) {
    foreach ($cars as $c) {
        if ($c['id'] == $selectedId) {
            $selectedCar = $c;
            break;
        }
    }
} elseif ($selectedSlug) {
    foreach ($cars as $c) {
        if ($c['slug'] === $selectedSlug) {
            $selectedCar = $c;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Book a Car - GingerRental</title>
  <link rel="stylesheet" href="style.css">
  <style>
      .navbar { max-width: 100% !important; padding-left: 2rem; padding-right: 2rem; box-sizing: border-box; }
      .booking-container { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top: 2rem; align-items: start; }
      @media (max-width: 768px) { .booking-container { grid-template-columns: 1fr; gap: 2rem; } }
      .booking-details { background: #fff; border-radius: 12px; border: 1px solid #eee; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
      .booking-details img { width: 100%; height: 250px; object-fit: cover; }
      .booking-info { padding: 1.5rem; }
      .booking-info h2 { margin-top: 0; color: #1f4e79; }
      .booking-price { font-size: 1.5rem; color: #ffbb33; font-weight: 800; margin-bottom: 1rem; }
      .booking-form-box { background: #f6f8fb; padding: 2rem; border-radius: 12px; border: 1px solid #eef3f9; }
      .booking-form-box h3 { margin-top: 0; color: #1f4e79; border-bottom: 2px solid #ffbb33; padding-bottom: 0.5rem; margin-bottom: 1.5rem; }
      form label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
      form input, form select { width: 100%; padding: 12px; margin-bottom: 1.2rem; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
      form input:focus, form select:focus { outline: none; border-color: #ffbb33; box-shadow: 0 0 5px rgba(255, 187, 51, 0.3); }
      .btn-confirm { background: linear-gradient(90deg, #1f4e79, #16395c); color: #fff; width: 100%; border: none; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: transform 0.2s; }
      .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(31, 78, 121, 0.3); }
      .no-car { text-align: center; padding: 4rem; color: #666; }
  </style>
</head>
<body>
<header>
  <nav class="navbar">
    <div class="nav-left">
      <a href="index.php">Home</a>
      <a href="cars.php">Cars</a>
      <a href="index.php#about">About</a>
      <a href="contact.php">Contact</a>
    </div>
    <div class="nav-right">
      <span style="color: var(--accent-2); font-weight: 600;">Hello, <?php echo $fullname; ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </nav>
</header>
<main class="main-container">
    <?php if ($selectedCar): ?>
        <div class="booking-container">
            <div class="booking-details">
                <img src="<?= htmlspecialchars($selectedCar['image']) ?>" alt="<?= htmlspecialchars($selectedCar['name']) ?>">
                <div class="booking-info">
                    <h2><?= htmlspecialchars($selectedCar['name']) ?></h2>
                    <div class="booking-price">₱<?= number_format($selectedCar['price'], 2) ?> <small style="font-size:0.6em; color:#666;">/day</small></div>
                    <p><?= htmlspecialchars($selectedCar['desc']) ?></p>
                    <hr style="border:0; border-top:1px solid #eee; margin:1rem 0;">
                    <p style="font-size:0.9rem; color:#666;">
                        <strong>Includes:</strong> Basic insurance, 24/7 roadside assistance, and standard mileage limit.
                    </p>
                </div>
            </div>
            <div class="booking-form-box">
                <h3>Finalize Reservation</h3>
                <form action="confirm_booking.php" method="POST">
                    <input type="hidden" name="car_id" value="<?= $selectedCar['id'] ?>">
                    <input type="hidden" name="car" value="<?= htmlspecialchars($selectedCar['name']) ?>">
                    <label for="pickup_date">Pick-up Date</label>
                    <input type="date" id="pickup_date" name="pickup_date" required min="<?= date('Y-m-d') ?>">
                    <label for="return_date">Return Date</label>
                    <input type="date" id="return_date" name="return_date" required min="<?= date('Y-m-d') ?>">
                    <label for="location">Pick-up Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g. NAIA Terminal 3, Makati City" required>
                    <label for="payment">Payment Method</label>
                    <select id="payment" name="payment" required>
                        <option value="" disabled selected>Select an option</option>
                        <option value="Cash on Pickup">Cash on Pickup</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="GCash">GCash / E-wallet</option>
                    </select>
                    <button type="submit" class="btn-confirm">Confirm Booking</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="no-car">
            <h2>No Car Selected</h2>
            <p>Please go back to our fleet page to select a vehicle.</p>
            <br>
            <a href="cars.php" class="btn-confirm" style="display:inline-block; width:auto; padding: 12px 24px;">Browse Cars</a>
        </div>
    <?php endif; ?>
</main>
<footer>© 2025 GingerRental. All rights reserved.</footer>
</body>
</html>
