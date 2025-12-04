<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['fullname']);
$fullname = $isLoggedIn ? htmlspecialchars($_SESSION['fullname']) : '';

$messageSent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    if ($name && $email && $message) {
        $messageSent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Us - GingerRental</title>
  <link rel="stylesheet" href="style.css">
  <style>
      /* FULL WIDTH NAVBAR FIX (Same as index.php) */
      .navbar {
          max-width: 100% !important; 
          padding-left: 2rem;
          padding-right: 2rem;
          box-sizing: border-box;
      }

      /* Page-specific styles */
      .contact-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 3rem;
          margin-top: 2rem;
      }

      @media (max-width: 768px) {
          .contact-grid {
              grid-template-columns: 1fr;
              gap: 2rem;
          }
      }

      .contact-info-box {
          background: #f6f8fb;
          padding: 2rem;
          border-radius: 12px;
          border: 1px solid #eef3f9;
      }

      .contact-info-box h3 {
          color: #1f4e79;
          margin-bottom: 1rem;
          font-size: 1.3rem;
      }

      .contact-info-box ul {
          list-style: none;
          padding: 0;
      }

      .contact-info-box li {
          margin-bottom: 1rem;
          border-bottom: 1px solid #e0e0e0;
          padding-bottom: 0.5rem;
      }

      .contact-info-box li strong {
          color: #1f4e79;
          display: block;
          margin-bottom: 0.2rem;
      }
      
      form label {
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 600;
          color: #333;
      }

      form input, form textarea {
          width: 100%;
          padding: 12px;
          margin-bottom: 1.2rem;
          border: 1px solid #ddd;
          border-radius: 8px;
          font-family: inherit;
          transition: border-color 0.3s;
      }

      form input:focus, form textarea:focus {
          outline: none;
          border-color: #ffbb33;
          box-shadow: 0 0 5px rgba(255, 187, 51, 0.3);
      }

      .btn-submit {
          background: linear-gradient(90deg, #1f4e79, #16395c);
          color: #fff;
          border: none;
          padding: 12px 24px;
          border-radius: 50px;
          font-weight: 700;
          cursor: pointer;
          transition: transform 0.2s;
          display: inline-block;
      }

      .btn-submit:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(31, 78, 121, 0.3);
      }
      
      .success-msg {
          background-color: #d4edda;
          color: #155724;
          padding: 1rem;
          border-radius: 8px;
          margin-bottom: 1.5rem;
          border: 1px solid #c3e6cb;
      }
  </style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-left">
      <a href="index.php">Home</a>
      <a href="cars.php">Cars</a>
      <a href="index.php#about">About</a>
      <a href="contact.php" class="active">Contact</a>
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
  
  <section aria-labelledby="contact-heading">
    <h2 id="contact-heading">Contact Us</h2>
    <p>Have questions? We're here to help you plan your next journey.</p>

    <?php if ($messageSent): ?>
        <div class="success-msg">
            ✅ Thank you! Your message has been sent. We will contact you shortly.
        </div>
    <?php endif; ?>

    <div class="contact-grid">
        <!-- Left Column: Contact Info -->
        <div class="contact-details">
            <div class="contact-info-box">
                <h3>Get in Touch</h3>
                <ul>
                    <li>
                        <strong>Email</strong>
                        <a href="mailto:support@gingerrental.com" style="color:#1f4e79; text-decoration:none;">support@gingerrental.com</a>
                    </li>
                    <li>
                        <strong>Phone</strong>
                        <a href="tel:+639158876948" style="color:#1f4e79; text-decoration:none;">+63 915 887 6948</a>
                    </li>
                    <li style="border-bottom:none;">
                        <strong>Address</strong>
                        GingerRental HQ<br>
                        Metro Manila, Philippines
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="contact-form">
            <form action="contact.php" method="POST">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" placeholder="Juan dela Cruz" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="juan@example.com" required>

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
  </section>

</main>

<footer>
  © 2025 GingerRental. All rights reserved.
</footer>

</body>
</html>