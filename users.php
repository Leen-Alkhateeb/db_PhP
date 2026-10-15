<?php
require "config.php";

// لازم يكون مسجل دخول
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$id = $_SESSION["user_id"];
$msg = "";

// تعديل الاسم وحذف الحساب
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // تعديل الاسم
    if (isset($_POST["new_name"])) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST["new_name"], $id);
        $stmt->execute();
        $_SESSION["user_name"] = $_POST["new_name"]; // عشان الاسم يتغير فوق كمان
        $msg = "<p class='success'>Name updated.</p>";
    }

    // حذف الحساب
    if (isset($_POST["delete_me"])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        session_destroy();
        header("Location: register.php");
        exit;
    }
}

// الفلاتر
$search = "%" . trim($_GET["search"] ?? "") . "%";                    // للبحث بـ LIKE
$from   = ($_GET["from"] ?? "") ?: "2000-01-01";                      // لو ما حدد تاريخ بنحط قيمة واسعة
$to     = ($_GET["to"] ?? "") ?: "2100-12-31";
$roleF  = $_GET["role"] ?? "all";
$roleIn = ["all" => "1, 2", "admin" => "1", "member" => "2"][$roleF] ?? "1, 2";   // الدور
$order  = ($_GET["order"] ?? "ASC") === "DESC" ? "DESC" : "ASC";      // الترتيب

// عدد الصفوف، بس 5 او 10 او 20
$limit = (int)($_GET["limit"] ?? 10);
if (!in_array($limit, [5, 10, 20])) {
    $limit = 10;
}

// الاستعلام الرئيسي: بجيب اليوزرز مع اسم الدور (بدون الباسورد)
// $roleIn و $order و $limit بنحطهم مباشرة لانهم قيم محددة من عندنا مش من اليوزر