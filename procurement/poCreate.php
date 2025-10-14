<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier = $_POST['supplier'] ?? '';
    $total = $_POST['total'] ?? '';
    // Validation and creation of PO would happen here
    $message = 'PO ถูกสร้างเรียบร้อยแล้ว (ตัวอย่าง).';
}
// Sample suppliers for selection
$suppliers = ['Supplier A', 'Supplier B'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้าง PO</title>
</head>
<body>
    <h1>สร้าง PO</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>
    <form method="post">
        <label>เลือกผู้ขาย:
            <select name="supplier" required>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>
        <label>ยอดรวม (รวมภาษี):
            <input type="number" step="0.01" name="total" required>
        </label><br><br>
        <button type="submit">บันทึก PO</button>
    </form>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>