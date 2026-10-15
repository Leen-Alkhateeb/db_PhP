<?php
require "config.php";
$msg = "";

// اذا ضغط على زر التسجيل
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $pass  = $_POST["password"];
    $birth = $_POST["birth_date"] ?: null;   // اذا تركه فاضي بينحط NULL
    $bio   = trim($_POST["bio"]) ?: null;    // نفس الشي

    // بشوف اذا الايميل مسجل من قبل
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result(); // لازم قبل num_rows

    if ($check->num_rows > 0) {
        $msg = "<p class='error'>This email is already registered.</p>";
    } else {
        // بشفر الباسورد قبل ما نخزنه
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // بضيف اليوزر الجديد
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, bio, birth_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hash, $bio, $birth);
        $stmt->execute();
        $msg = "<p class='success'>Account created. <a href='login.php'>Log in</a></p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="box">
    <h2>Register</h2>
    <!-- الرسالة بتطلع هون -->
    <?= $msg ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password (min 6)" minlength="6" required>
        <input type="date" name="birth_date">
        <textarea name="bio" placeholder="Bio (optional)"></textarea>
        <button>Register</button>
    </form>
    <p>Have an account? <a href="login.php">Log in</a></p>
</div>
</body>
</html>