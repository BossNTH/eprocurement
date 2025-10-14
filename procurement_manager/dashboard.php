<?php
require_once __DIR__ . '/../auth.php';
requireRole('approver');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้อนุมัติจัดซื้อ</title>
</head>
<body>
    <h1>สวัสดีคุณ <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <h2>แดชบอร์ดผู้อนุมัติจัดซื้อ</h2>
    <ul>
        <li><a href="poApprovalQueue.php">คิวรออนุมัติ PO</a></li>
        <li><a href="../logout.php">ออกจากระบบ</a></li>
    </ul>
</body>
</html>