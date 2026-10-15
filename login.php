<?php
require "config.php";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $pass  = $_POST["password"];

    // AND: الايميل صح + الحساب فعّال
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pass, $user["password"])) {
        $_SESSION["user_id"]   = $user["id"];     // نحفظ حالة تسجيل الدخول
        $_SESSION["user_name"] = $user["name"];
        header("Location: users.php");
        exit;
    }
    $msg = "<p class='error'>Incorrect email or password.</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="box">
    <h2>Login</h2>
    <?= $msg ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button>Log in</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
