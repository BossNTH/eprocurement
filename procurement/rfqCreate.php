<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dueDate = $_POST['due_date'] ?? '';
    $suppliers = $_POST['suppliers'] ?? [];
    // Here you'd validate and create a new RFQ in the database
    $message = 'RFQ ถูกสร้างเรียบร้อยแล้ว (ตัวอย่าง).';
}
// Sample supplier list for invitation
$sampleSuppliers = ['Supplier A', 'Supplier B', 'Supplier C'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้าง RFQ</title>
</head>
<body>
    <h1>สร้าง RFQ</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>กำหนดวันครบกำหนด (Due Date):
            <input type="date" name="due_date" required>
        </label><br><br>
        <label>เชิญคู่ค้า (เลือกอย่างน้อย 2):</label><br>
        <?php foreach ($sampleSuppliers as $s): ?>
            <label><input type="checkbox" name="suppliers[]" value="<?= htmlspecialchars($s) ?>"> <?= htmlspecialchars($s) ?></label><br>
        <?php endforeach; ?>
        <br>
        <button type="submit">บันทึก RFQ</button>
    </form>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>