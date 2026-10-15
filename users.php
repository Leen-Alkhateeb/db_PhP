<?php
require "config.php";

// صفحة محمية
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$id = $_SESSION["user_id"];
$msg = "";

/* ---- Group 4: UPDATE و DELETE مع WHERE ---- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["new_name"])) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST["new_name"], $id);
        $stmt->execute();
        $_SESSION["user_name"] = $_POST["new_name"];
        $msg = "<p class='success'>Name updated.</p>";
    }
    if (isset($_POST["delete_me"])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        session_destroy();
        header("Location: register.php");
        exit;
    }
}

/* ---- الفلاتر (كلها اختيارية) ---- */
$search = "%" . trim($_GET["search"] ?? "") . "%";                    // LIKE
$from   = ($_GET["from"] ?? "") ?: "2000-01-01";                      // BETWEEN
$to     = ($_GET["to"] ?? "") ?: "2100-12-31";
$roleF  = $_GET["role"] ?? "all";
$roleIn = ["all" => "1, 2", "admin" => "1", "member" => "2"][$roleF] ?? "1, 2";   // IN
$order  = ($_GET["order"] ?? "ASC") === "DESC" ? "DESC" : "ASC";      // ORDER BY

// LIMIT (هاد السطر اللي انصلح)
$limit = (int)($_GET["limit"] ?? 10);
if (!in_array($limit, [5, 10, 20])) {
    $limit = 10;
}

/* ---- Group 5: SELECT مع alias + AND/OR + ORDER BY + LIMIT (بدون password) ---- */
$sql = "SELECT u.id, u.name AS full_name, u.email, r.name AS role_name, u.created_at
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE (u.name LIKE ? OR u.email LIKE ?)
          AND u.role_id IN ($roleIn)
          AND DATE(u.created_at) BETWEEN ? AND ?
        ORDER BY u.id $order
        LIMIT $limit";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $search, $search, $from, $to);
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="box wide">
    <p class="top">Hello, <b><?= htmlspecialchars($_SESSION["user_name"]) ?></b> · <a href="logout.php">Log out</a></p>
    <h2>Registered users</h2>
    <?= $msg ?>

    <form method="GET" class="row">
        <input type="text" name="search" placeholder="Search name or email" value="<?= htmlspecialchars($_GET["search"] ?? "") ?>">
        <select name="role">
            <option value="all">All roles</option>
            <option value="admin"  <?= $roleF === "admin" ? "selected" : "" ?>>Admin</option>
            <option value="member" <?= $roleF === "member" ? "selected" : "" ?>>Member</option>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($_GET["from"] ?? "") ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($_GET["to"] ?? "") ?>">
        <select name="order">
            <option value="ASC">Oldest first</option>
            <option value="DESC" <?= $order === "DESC" ? "selected" : "" ?>>Newest first</option>
        </select>
        <select name="limit">
            <?php foreach ([5, 10, 20] as $n): ?>
                <option value="<?= $n ?>" <?= $limit === $n ? "selected" : "" ?>><?= $n ?> rows</option>
            <?php endforeach; ?>
        </select>
        <button>Filter</button>
    </form>

    <div class="table-wrap">
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th></tr>
        <?php while ($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row["id"] ?></td>
                <td><?= htmlspecialchars($row["full_name"]) ?></td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td><?= htmlspecialchars($row["role_name"]) ?></td>
                <td><?= $row["created_at"] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    </div>

    <h3>My account</h3>
    <form method="POST" class="row">
        <input type="text" name="new_name" placeholder="New name" required>
        <button>Update my name</button>
    </form>
    <form method="POST" onsubmit="return confirm('Delete your account?');">
        <input type="hidden" name="delete_me" value="1">
        <button class="danger">Delete my account</button>
    </form>
</div>
</body>
</html>