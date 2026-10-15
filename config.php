<?php
// بدء الجلسة (Session) لتتمكن من تخزين بيانات المستخدم بين الصفحات
session_start();

// إنشاء اتصال بقاعدة بيانات MySQL باستخدام mysqli
// المعاملات بالترتيب: الخادم، اسم المستخدم، كلمة المرور، اسم قاعدة البيانات
$conn = new mysqli("localhost", "root", "", "auth_db");

// التحقق من نجاح الاتصال
if ($conn->connect_error) {
    // في حال فشل الاتصال: إيقاف تنفيذ السكربت وعرض رسالة الخطأ
    die("Connection failed: " . $conn->connect_error);
}