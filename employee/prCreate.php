<?php
require_once __DIR__ . '/../auth.php';
requireRole('employee');

// In a real application, form submission would save a new PR to the database.
// Here we simply display a confirmation message when the form is posted.
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    $items = trim($_POST['items'] ?? '');
    // Validate required fields in a real system
    $message = 'PR ของคุณถูกสร้างเรียบร้อยแล้ว (ตัวอย่าง).';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้าง PR</title>
</head>
<body>
    <h1>สร้าง PR</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>เหตุผลความจำเป็น:<br>
            <textarea name="reason" rows="3" cols="50" required></textarea>
        </label><br><br>
        <label>รายการสินค้า (ระบุชื่อสินค้าและจำนวน):<br>
            <textarea name="items" rows="5" cols="50" required></textarea>
        </label><br><br>
        <button type="submit">บันทึก PR</button>
    </form>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>