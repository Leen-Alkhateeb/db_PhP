<?php
require "config.php";

// صفحة محمية: لازم يكون المستخدم مسجّل دخول
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$id = $_SESSION["user_id"];
$msg = "";

/* ---- UPDATE و DELETE مع WHERE (على حساب المستخدم الحالي فقط) ---- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["new_name"])) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST["new_name"], $id);
        $stmt->execute();
        $_SESSION["user_name"] = $_POST["new_name"]; // تحديث الاسم المعروض في الجلسة
        $msg = "<p class='success'>Name updated.</p>";
    }
    if (isset($_POST["delete_me"])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        session_destroy(); // إنهاء الجلسة بعد حذف الحساب
        header("Location: register.php");
        exit;
    }
}

/* ---- الفلاتر (كلها اختيارية) ---- */
$search = "%" . trim($_GET["search"] ?? "") . "%";                    // LIKE: بحث جزئي
$from   = ($_GET["from"] ?? "") ?: "2000-01-01";                      // BETWEEN: قيم افتراضية واسعة
$to     = ($_GET["to"] ?? "") ?: "2100-12-31";
$roleF  = $_GET["role"] ?? "all";
// قائمة بيضاء للأدوار: القيمة تُحدَّد من مصفوفة ثابتة وليس من إدخال المستخدم
$roleIn = ["all" => "1, 2", "admin" => "1", "member" => "2"][$roleF] ?? "1, 2";   // IN
$order  = ($_GET["order"] ?? "ASC") === "DESC" ? "DESC" : "ASC";      // ORDER BY: قيمتان فقط

// LIMIT: تحويل لرقم صحيح + السماح بقيم محددة فقط (لأنه لا يُربط بـ bind_param هنا)
$limit = (int)($_GET["limit"] ?? 10);
if (!in_array($limit, [5, 10, 20])) {
    $limit = 10;
}

/* ---- SELECT مع alias + JOIN + AND/OR + ORDER BY + LIMIT (بدون password) ---- */
// المتغيرات المدمجة مباشرة