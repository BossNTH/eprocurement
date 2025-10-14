<?php
require_once __DIR__ . '/../auth.php';
requireRole('supplier');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดคู่ค้า</title>
</head>
<body>
    <h1>สวัสดีคุณ <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <h2>แดชบอร์ดคู่ค้า</h2>
    <ul>
        <li><a href="rfqMyInvitations.php">คำเชิญ RFQ ของฉัน</a></li>
        <li><a href="quoteSubmit.php">ส่งใบเสนอราคา</a></li>
        <li><a href="poMyOrders.php">รายการ PO ของฉัน</a></li>
        <li><a href="../logout.php">ออกจากระบบ</a></li>
    </ul>
</body>
</html>