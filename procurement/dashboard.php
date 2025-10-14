<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดฝ่ายจัดซื้อ</title>
</head>
<body>
    <h1>สวัสดีคุณ <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <h2>แดชบอร์ดฝ่ายจัดซื้อ</h2>
    <ul>
        <li><a href="rfqCreate.php">สร้าง RFQ (Request for Quotation)</a></li>
        <li><a href="rfqList.php">รายการ RFQ ของฉัน</a></li>
        <li><a href="quoteInbox.php">กล่องรับใบเสนอราคา</a></li>
        <li><a href="quoteCompare.php">เปรียบเทียบใบเสนอราคา</a></li>
        <li><a href="poCreate.php">สร้าง PO (Purchase Order)</a></li>
        <li><a href="poList.php">รายการ PO</a></li>
        <li><a href="reportPurchaseVAT.php">รายงานภาษีซื้อ</a></li>
        <li><a href="../logout.php">ออกจากระบบ</a></li>
    </ul>
</body>
</html>