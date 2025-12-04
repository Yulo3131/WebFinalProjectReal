<?php
// 1. Include config to load the Custom Session Handler
require_once 'config.php';

// 2. Start session
session_start();

// 3. Unset and destroy
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// 4. Redirect
header("Location: login.php");
exit;
?>