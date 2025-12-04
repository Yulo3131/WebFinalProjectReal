<?php
require_once 'config.php'; // Correct order for Vercel
session_start();

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$errors = [];
$success = false;
$next = '';

// ... (Rest of your signup.php code logic stays exactly the same) ...
// accept next from GET (when redirected from booking) and carry it into POST
if (!empty($_GET['next'])) {
    $next = $_GET['next'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Your existing POST logic) ...
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nextPost = trim($_POST['next'] ?? '');

    if ($fullname === '' || $email === '' || $username === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } else {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Username or email already taken.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error.';
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ins = "INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($ins)) {
            $stmt->bind_param('ssss', $fullname, $email, $username, $hashed);
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                $_SESSION['user_id'] = $userId;
                $_SESSION['fullname'] = $fullname;

                $redirectTo = 'index.php';
                $candidate = $nextPost !== '' ? $nextPost : ($next ?? '');
                $allowRedirect = false;
                if ($candidate !== '') {
                    $parsed = parse_url($candidate);
                    if (!isset($parsed['scheme']) && !isset($parsed['host']) && strpos($candidate, '//') !== 0) {
                        $allowRedirect = true;
                    }
                }
                if ($allowRedirect) {
                    header('Location: ' . $candidate);
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $errors[] = 'Could not create account. Try again later.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign up - GingerRental</title>
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
    .error-box, .info-box { display: none; position: fixed; top: 1rem; right: 1rem; padding: 1rem 1.5rem; border-radius: 5px; z-index: 9999; box-shadow: 0 2px 6px rgba(0,0,0,0.2); font-family: sans-serif; font-size: 0.95rem; }
    .error-box { background-color: #f44336; color: #fff; }
    .info-box { background-color: #4caf50; color: #fff; }
  </style>
</head>
<body>
  <main class="auth-page">
    <h1>Create an account</h1>

    <form method="post" action="signup.php" novalidate>
      <input type="hidden" name="next" value="<?php echo e($_GET['next'] ?? $next ?? ''); ?>">
      <label for="fullname">Full name</label>
      <input id="fullname" name="fullname" type="text" value="<?php echo e($_POST['fullname'] ?? ''); ?>" required>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>

      <label for="username">Username</label>
      <input id="username" name="username" type="text" value="<?php echo e($_POST['username'] ?? ''); ?>" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" minlength="6" required>

      <button type="submit">Sign up</button>
    </form>

    <p>Already have an account? <a href="login.php<?php echo !empty($next) ? '?next='.urlencode($next) : ''; ?>">Login here</a></p>
  </main>

  <div id="alert-success" class="info-box"></div>
  <div id="alert-error" class="error-box"></div>

  <script>
    const alertSuccess = document.getElementById('alert-success');
    const alertError = document.getElementById('alert-error');

    <?php if (!empty($errors)): ?>
      alertError.innerHTML = "<?php echo e(implode(' ', $errors)); ?>";
      alertError.style.display = 'block';
      setTimeout(() => alertError.style.display = 'none', 4000);
    <?php endif; ?>
  </script>
</body>
</html>