<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
$poNo = $_GET['po_no'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด PO</title>
</head>
<body>
    <h1>รายละเอียด PO</h1>
    <?php if ($poNo): ?>
        <p>เลขที่ PO: <?= htmlspecialchars($poNo) ?></p>
        <p>รายละเอียด PO (ตัวอย่างข้อมูล) เช่น รายการสินค้า ราคาต่อหน่วย จำนวน และเงื่อนไขการชำระเงิน</p>
    <?php else: ?>
        <p>ไม่พบ PO ที่ระบุ</p>
    <?php endif; ?>
    <p><a href="poList.php">กลับไปหน้ารายการ PO</a></p>
</body>
</html>