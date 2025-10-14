<?php
require_once __DIR__ . '/../auth.php';
requireRole('supplier');
// Sample PO orders for this supplier
$myPOs = [
    ['po_no' => 'PO202510-0002', 'status' => 'ISSUED', 'date' => '2025-10-12'],
    ['po_no' => 'PO202510-0005', 'status' => 'PENDING_APPROVAL', 'date' => '2025-10-14'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการ PO ของฉัน</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>รายการ PO ของฉัน</h1>
    <table>
        <thead>
            <tr><th>เลขที่ PO</th><th>สถานะ</th><th>วันที่</th></tr>
        </thead>
        <tbody>
            <?php foreach ($myPOs as $po): ?>
                <tr>
                    <td><?= htmlspecialchars($po['po_no']) ?></td>
                    <td><?= htmlspecialchars($po['status']) ?></td>
                    <td><?= htmlspecialchars($po['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>