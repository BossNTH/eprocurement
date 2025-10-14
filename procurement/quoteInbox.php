<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
// Sample quotes
$quotes = [
    ['quote_no' => 'Q202510-0001', 'supplier' => 'Supplier A', 'rfq_no' => 'RFQ202510-0001', 'status' => 'SUPPLIER_SUBMITTED'],
    ['quote_no' => 'Q202510-0002', 'supplier' => 'Supplier B', 'rfq_no' => 'RFQ202510-0001', 'status' => 'SUPPLIER_SUBMITTED'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กล่องรับใบเสนอราคา</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>กล่องรับใบเสนอราคา</h1>
    <table>
        <thead>
            <tr><th>เลขที่ใบเสนอราคา</th><th>คู่ค้า</th><th>RFQ</th><th>สถานะ</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($quotes as $quote): ?>
                <tr>
                    <td><?= htmlspecialchars($quote['quote_no']) ?></td>
                    <td><?= htmlspecialchars($quote['supplier']) ?></td>
                    <td><?= htmlspecialchars($quote['rfq_no']) ?></td>
                    <td><?= htmlspecialchars($quote['status']) ?></td>
                    <td><a href="quoteCompare.php?rfq_no=<?= urlencode($quote['rfq_no']) ?>">เปรียบเทียบ</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>