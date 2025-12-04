<?php 
require_once 'config.php'; // Correct order for Vercel
session_start();

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$error = '';
$info = '';

if (isset($_GET['registered'])) {
    $info = 'Account created. Please sign in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // 1. We now select the 'role' column as well
        $sql = "SELECT id, fullname, password, role FROM users WHERE email = ? LIMIT 1";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    
                    // 2. Check Role and Redirect accordingly
                    if ($user['role'] === 'admin') {
                        header('Location: admin.php'); // Admins go here
                        exit;
                    }

                    // Standard users go to index or the page they were trying to visit
                    $next = $_GET['next'] ?? 'index.php?login=success';
                    header('Location: ' . $next);
                    exit;

                } else {
                    $error = 'Incorrect password.';
                }
            } else {
                $error = 'Email not found.';
            }
            $stmt->close();
        } else {
            $error = 'Database error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - GingerRental</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, #1f4e79, #ffbb33); }
    .auth-page { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
    .auth-page h1 { color: #1f4e79; margin-bottom: 1.5rem; text-align: center; }
    .auth-page form { display: flex; flex-direction: column; gap: 1rem; }
    .auth-page label { color: #333; font-weight: 600; }
    .auth-page input { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
    .auth-page input:focus { outline: none; border-color: #1f4e79; box-shadow: 0 0 5px rgba(31, 78, 121, 0.3); }
    .auth-page button { padding: 12px; background: linear-gradient(135deg, #1f4e79, #16395c); color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 1rem; }
    .auth-page button:hover { background: linear-gradient(135deg, #ffbb33, #ff9900); color: #000; }
    .error-box { background: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #c33; }
    .info-box { background: #efe; color: #3c3; padding: 12px; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #3c3; }
  </style>
</head>
<body>
  <main class="auth-page">
    <h1>Sign in</h1>

    <form method="post" action="login.php<?php echo isset($_GET['next']) ? '?next='.urlencode($_GET['next']) : ''; ?>" novalidate>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>

      <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
  </main>
  
  <div id="alert-box" style="display:none; position:fixed; top:1rem; right:1rem; padding:1rem 1.5rem; background:#4caf50; color:white; border-radius:5px; z-index:9999; box-shadow:0 2px 6px rgba(0,0,0,0.2); font-family:sans-serif; font-size:0.95rem;"></div>

  <script>
    const alertBox = document.getElementById('alert-box');

    <?php if ($info): ?>
      alertBox.textContent = "<?php echo e($info); ?>";
      alertBox.style.display = 'block';
      setTimeout(() => alertBox.style.display = 'none', 4000);
    <?php endif; ?>

    <?php if ($error): ?>
      alertBox.textContent = "<?php echo e($error); ?>";
      alertBox.style.backgroundColor = "#f44336";
      alertBox.style.display = 'block';
      setTimeout(() => alertBox.style.display = 'none', 4000);
    <?php endif; ?>
  </script>
</body>
</html>
