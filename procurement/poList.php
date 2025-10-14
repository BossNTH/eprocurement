<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
// Sample PO list
$poList = [
    ['po_no' => 'PO202510-0001', 'supplier' => 'Supplier A', 'status' => 'PENDING_APPROVAL'],
    ['po_no' => 'PO202510-0002', 'supplier' => 'Supplier B', 'status' => 'ISSUED'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการ PO</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>รายการ PO</h1>
    <table>
        <thead>
            <tr><th>เลขที่ PO</th><th>ผู้ขาย</th><th>สถานะ</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($poList as $po): ?>
                <tr>
                    <td><?= htmlspecialchars($po['po_no']) ?></td>
                    <td><?= htmlspecialchars($po['supplier']) ?></td>
                    <td><?= htmlspecialchars($po['status']) ?></td>
                    <td><a href="poDetail.php?po_no=<?= urlencode($po['po_no']) ?>">ดูรายละเอียด</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>