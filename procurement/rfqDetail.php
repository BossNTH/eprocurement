<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
$rfqNo = $_GET['rfq_no'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด RFQ</title>
</head>
<body>
    <h1>รายละเอียด RFQ</h1>
    <?php if ($rfqNo): ?>
        <p>เลขที่ RFQ: <?= htmlspecialchars($rfqNo) ?></p>
        <p>รายละเอียด RFQ (ตัวอย่างข้อมูล). เช่น รายการสินค้าและคู่ค้าที่ได้รับเชิญ</p>
    <?php else: ?>
        <p>ไม่พบ RFQ ที่ระบุ</p>
    <?php endif; ?>
    <p><a href="rfqList.php">กลับไปหน้ารายการ RFQ</a></p>
</body>
</html>