<?php
session_start();
require_once("../connect.php");
require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้ระบบ</title>
</head>
<body>
    <h1>จัดการผู้ใช้ระบบ</h1>
    <p>หน้านี้ใช้สำหรับเพิ่ม ลบ หรือปรับสิทธิ์ผู้ใช้ระบบ.</p>
    <p>ยังไม่รวมฟังก์ชันเต็มรูปแบบ แต่มีไว้เป็นตัวอย่างโครงสร้างไฟล์.</p>
    <p><a href="dashboard.php">กลับไปแดชบอร์ดผู้ดูแล</a></p>
</body>
</html>