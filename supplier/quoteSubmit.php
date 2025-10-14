<?php
require_once __DIR__ . '/../auth.php';
requireRole('supplier');
$rfqNo = $_GET['rfq_no'] ?? '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = $_POST['price'] ?? '';
    $delivery = $_POST['delivery'] ?? '';
    $warranty = $_POST['warranty'] ?? '';
    // In a real system, the quote would be saved to the database
    $message = 'ส่งใบเสนอราคาเรียบร้อยแล้ว (ตัวอย่าง).';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ส่งใบเสนอราคา</title>
</head>
<body>
    <h1>ส่งใบเสนอราคา</h1>
    <?php if ($rfqNo): ?>
        <p>สำหรับ RFQ: <?= htmlspecialchars($rfqNo) ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p style="color: green;"> <?= htmlspecialchars($message) ?> </p>
        <p><a href="dashboard.php">กลับแดชบอร์ดคู่ค้า</a></p>
    <?php else: ?>
        <form method="post">
            <label>ราคาเสนอ (บาท):
                <input type="number" step="0.01" name="price" required>
            </label><br><br>
            <label>ระยะเวลาส่งมอบ:
                <input type="text" name="delivery" required>
            </label><br><br>
            <label>การรับประกัน (เดือน):
                <input type="number" name="warranty" required>
            </label><br><br>
            <button type="submit">ส่งใบเสนอราคา</button>
        </form>
        <p><a href="rfqMyInvitations.php">กลับไปคำเชิญ RFQ</a></p>
    <?php endif; ?>
</body>
</html>