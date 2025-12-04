<?php
session_start();

// Sample car data
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

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['fullname']);
$fullname = $isLoggedIn ? htmlspecialchars($_SESSION['fullname']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GingerRental - Our Fleet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* FULL WIDTH NAVBAR FIX (Same as index.php) */
        .navbar {
            max-width: 100% !important; 
            padding-left: 2rem;
            padding-right: 2rem;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-left">
      <a href="index.php">Home</a>
      <a href="cars.php" class="active">Cars</a>
      <a href="index.php#about">About</a>
      <a href="contact.php">Contact</a>
    </div>
    <div class="nav-right">
      <?php if ($isLoggedIn): ?>
        <span style="color: var(--accent-2); font-weight: 600;">Hello, <?php echo $fullname; ?></span>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="signup.php">Signup</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<main class="main-container">

    <section aria-labelledby="cars-heading">
        <h2 id="cars-heading">Our Fleet</h2>
        <p>Choose from our wide range of premium vehicles.</p>
        
        <div class="cars-grid">
            <?php foreach($cars as $car): ?>
                <article class="car-card">
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                    
                    <div class="car-body">
                        <h3><?= htmlspecialchars($car['name']) ?></h3>
                        <p><?= htmlspecialchars($car['desc']) ?></p>
                        <div class="price">
                            ₱<?= number_format($car['price'], 2) ?> <small style="font-weight:400; font-size: 0.8em;">/day</small>
                        </div>
                    </div>

                    <div class="car-footer">
                        <a class="rent-btn" href="booking.php?car=<?= urlencode($car['slug']) ?>&id=<?= $car['id'] ?>">
                            Rent Now
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<footer>
  © 2025 GingerRental. All rights reserved.
</footer>

</body>
</html>