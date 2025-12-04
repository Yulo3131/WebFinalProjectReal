<?php
// filepath: api/index.php
require_once 'config.php'; // Must be before session_start
session_start();

// Hardcoded cars data
$cars = [
    [
        'id'   => 1, 'slug' => 'toyota-vios', 'name' => 'Toyota Vios', 
        'image'=> 'https://imgcdn.zigwheels.ph/large/gallery/exterior/30/1943/toyota-vios-front-angle-low-view-945824.jpg',
        'price'=> 1500, 'desc' => 'Reliable sedan, great on gas and comfortable for city trips.'
    ],
    [
        'id'   => 2, 'slug' => 'honda-crv', 'name' => 'Honda CR-V', 
        'image'=> 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThGoeYHIKudbhxg34cykLXg4_C7A1UolQZKw&s',
        'price'=> 3200, 'desc' => 'Spacious SUV for family trips with modern features.'
    ],
    [
        'id'   => 3, 'slug' => 'nissan-urvan', 'name' => 'Nissan Urvan', 
        'image'=> 'https://imgcdn.zigwheels.ph/large/gallery/exterior/25/717/nissan-nv350-urvan-front-angle-low-view-612406.jpg',
        'price'=> 4000, 'desc' => 'Large van ideal for group travels and tours.'
    ],
];

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['fullname']);
$fullname = $isLoggedIn ? htmlspecialchars($_SESSION['fullname']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GingerRental - Rent Cars Easily</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ... Keep your existing CSS styles ... */
    /* Ensure the navbar styles and everything else remain the same */
    .navbar { max-width: 100% !important; padding-left: 2rem; padding-right: 2rem; box-sizing: border-box; }
    .hero { background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1493238792000-8113da705763?q=80&w=2070') no-repeat center center/cover !important; color: white !important; min-height: 550px; display: flex; align-items: center; justify-content: center; text-align: center; border-radius: 0 !important; margin: 0 !important; max-width: 100% !important; box-shadow: none !important; }
    .hero-content { max-width: 800px; padding: 20px; z-index: 2; }
    .hero h1 { color: white !important; font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
    .hero p { color: #e0e0e0 !important; font-size: 1.3rem; margin-bottom: 2rem; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
    .hero-buttons { display: flex; justify-content: center; gap: 1.5rem; }
    .cta-button { background: #ffbb33 !important; color: #000 !important; padding: 14px 32px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease; border: none; }
    .cta-button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(255, 187, 51, 0.4); }
    .cta-outline { background: transparent !important; border: 2px solid white !important; color: white !important; padding: 12px 30px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.2s ease; }
    .cta-outline:hover { background: white !important; color: #1f4e79 !important; transform: translateY(-3px); }
    .about-intro { text-align: center; max-width: 700px; margin: 0 auto 3rem auto; color: #555; font-size: 1.1rem; }
    .about-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem; }
    .about-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.08); transition: transform 0.3s ease; text-align: center; border: 1px solid #eee; }
    .about-card:hover { transform: translateY(-10px); }
    .about-card img { width: 100%; height: 220px; object-fit: cover; }
    .about-content { padding: 1.5rem; }
    .about-content h3 { color: #1f4e79; margin-bottom: 0.5rem; font-size: 1.4rem; }
    .about-content p { color: #666; font-size: 0.95rem; }
    .main-container { max-width: 1100px; margin: 0 auto; padding: 3rem 1.5rem; }
  </style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-left">
      <a href="index.php" class="active">Home</a>
      <a href="cars.php">Cars</a>
      <a href="#about">About</a>
      <a href="#contact">Contact</a>
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

<section id="home" class="hero">
  <div class="hero-content">
    <h1>Drive Your Journey with GingerRental</h1>
    <p>Premium cars. Unforgettable memories. The freedom to explore.</p>
    <div class="hero-buttons">
      <a href="cars.php" class="cta-button">Browse Cars</a>
      <a href="booking.php" class="cta-outline">Reserve Now</a>
    </div>
  </div>
</section>

<main class="main-container">
  
  <section id="cars" aria-labelledby="cars-heading">
    <h2 id="cars-heading">Available Cars</h2>
    
    <div class="cars-grid">
      <?php foreach ($cars as $car): ?>
      <article class="car-card">
        <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
        
        <div class="car-body">
          <h3><?php echo htmlspecialchars($car['name']); ?></h3>
          <p><?php echo htmlspecialchars($car['desc']); ?></p>
          
          <div class="price">
            ₱<?php echo number_format($car['price'], 2); ?> <small style="font-weight:400; font-size: 0.8em;">/day</small>
          </div>
        </div>
        
        <div class="car-footer">
          <a class="rent-btn" href="booking.php?car=<?php echo urlencode($car['name']); ?>&id=<?php echo $car['id']; ?>">
            Rent Now
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a class="cta-button" style="background: transparent !important; color: var(--accent) !important; border: 2px solid var(--accent) !important; box-shadow:none;" href="cars.php">See all cars →</a>
    </div>
  </section>

  <section id="about" aria-labelledby="about-heading">
    <h2 id="about-heading" style="text-align: center;">Who We Are</h2>
    <p class="about-intro">GingerRental is more than just a car rental company. We are your partners in exploration, dedicated to providing safe, reliable, and affordable vehicles for every journey.</p>
    
    <div class="about-grid">
        <div class="about-card">
            <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=600&q=80" alt="Clean car interior">
            <div class="about-content">
                <h3>Premium Fleet</h3>
                <p>We pride ourselves on maintaining a modern fleet. Every vehicle is regularly serviced, sanitized, and inspected to ensure your safety and comfort.</p>
            </div>
        </div>

        <div class="about-card">
            <img src="https://images.unsplash.com/photo-1516733725897-1aa73b87c8e8?auto=format&fit=crop&w=600&q=80" alt="Keys in hand">
            <div class="about-content">
                <h3>Seamless Booking</h3>
                <p>No hidden fees, no complicated paperwork. Our digital-first booking system gets you behind the wheel in minutes, not hours.</p>
            </div>
        </div>

        <div class="about-card">
            <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=600&q=80" alt="Road trip scenery">
            <div class="about-content">
                <h3>Limitless Journeys</h3>
                <p>Whether it's a city trip or a mountain adventure, we provide the reliability you need to explore with confidence and peace of mind.</p>
            </div>
        </div>
    </div>
  </section>

  <section id="contact" aria-labelledby="contact-heading">
    <h2 id="contact-heading">Contact Us</h2>
    <ul>
      <li><strong>Email:</strong> <a href="mailto:support@gingerrental.com">support@gingerrental.com</a></li>
      <li><strong>Phone:</strong> <a href="tel:+639158876948">+63 915 887 6948</a></li>
    </ul>
  </section>

</main>

<footer>
  © 2025 GingerRental. All rights reserved.
</footer>

</body>
</html>