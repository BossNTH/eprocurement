<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
$rfqNo = $_GET['rfq_no'] ?? '';
// Sample quotes for comparison; normally this would be pulled from DB
$sampleQuotes = [
    ['supplier' => 'Supplier A', 'price' => 10000, 'delivery' => '7 วัน'],
    ['supplier' => 'Supplier B', 'price' => 9500, 'delivery' => '10 วัน'],
];
$selectionMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedSupplier = $_POST['selected_supplier'] ?? '';
    $selectionMessage = 'คุณได้เลือก ' . htmlspecialchars($selectedSupplier) . ' เป็นผู้ชนะ (ตัวอย่าง).';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เปรียบเทียบใบเสนอราคา</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>เปรียบเทียบใบเสนอราคา</h1>
    <?php if ($rfqNo): ?>
        <p>RFQ: <?= htmlspecialchars($rfqNo) ?></p>
    <?php endif; ?>
    <?php if ($selectionMessage): ?>
        <p style="color: green;"><?= $selectionMessage ?></p>
    <?php endif; ?>
    <form method="post">
        <table>
            <thead>
                <tr><th>เลือก</th><th>คู่ค้า</th><th>ราคา</th><th>ระยะเวลาส่งมอบ</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sampleQuotes as $quote): ?>
                    <tr>
                        <td><input type="radio" name="selected_supplier" value="<?= htmlspecialchars($quote['supplier']) ?>" required></td>
                        <td><?= htmlspecialchars($quote['supplier']) ?></td>
                        <td><?= number_format($quote['price'], 2) ?></td>
                        <td><?= htmlspecialchars($quote['delivery']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">เลือกผู้ชนะ</button>
    </form>
    <p><a href="quoteInbox.php">กลับไปกล่องรับใบเสนอราคา</a></p>
</body>
</html>