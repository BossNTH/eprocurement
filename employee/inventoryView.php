<?php
require_once __DIR__ . '/../auth.php';
requireRole('employee');
// Sample inventory data; in a real application this would come from products table
$sampleInventory = [
    ['product' => 'โน้ตบุ๊ก Lenovo', 'category' => 'คอมพิวเตอร์', 'stock' => 15],
    ['product' => 'เครื่องพิมพ์ Epson', 'category' => 'อุปกรณ์สำนักงาน', 'stock' => 5],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ดูรายการสินค้า/คงคลัง</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>รายการสินค้า/คงคลัง</h1>
    <table>
        <thead>
            <tr><th>สินค้า</th><th>หมวดหมู่</th><th>จำนวนคงคลัง</th></tr>
        </thead>
        <tbody>
            <?php foreach ($sampleInventory as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['stock']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>