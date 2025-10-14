<?php
require_once __DIR__ . '/../auth.php';
requireRole('approver');
// Sample PO waiting for approval
$pendingPOs = [
    ['po_no' => 'PO202510-0001', 'supplier' => 'Supplier A', 'total' => 10700],
    ['po_no' => 'PO202510-0004', 'supplier' => 'Supplier C', 'total' => 32500],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คิวรออนุมัติ PO</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>คิวรออนุมัติ PO</h1>
    <table>
        <thead>
            <tr><th>เลขที่ PO</th><th>ผู้ขาย</th><th>ยอดรวม</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pendingPOs as $po): ?>
                <tr>
                    <td><?= htmlspecialchars($po['po_no']) ?></td>
                    <td><?= htmlspecialchars($po['supplier']) ?></td>
                    <td><?= number_format($po['total'], 2) ?></td>
                    <td><a href="poApprovalDetail.php?po_no=<?= urlencode($po['po_no']) ?>">ตรวจสอบ/อนุมัติ</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>