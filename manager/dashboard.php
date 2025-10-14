<?php
require_once __DIR__ . '/../auth.php';
requireRole('manager');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้จัดการ</title>
</head>
<body>
    <h1>สวัสดีคุณ <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <h2>แดชบอร์ดผู้จัดการ</h2>
    <ul>
        <li><a href="prApprovalQueue.php">คิวรออนุมัติ PR</a></li>
        <li><a href="../logout.php">ออกจากระบบ</a></li>
    </ul>
</body>
</html>