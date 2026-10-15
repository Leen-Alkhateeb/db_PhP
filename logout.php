<?php
session_start();
session_destroy(); // حذف بيانات الجلسة بالكامل (تسجيل الخروج)
header("Location: login.php");
exit; // إيقاف السكربت بعد التحويل