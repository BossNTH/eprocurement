<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
// Sample RFQ list
$rfqs = [
    ['rfq_no' => 'RFQ202510-0001', 'status' => 'SENT_TO_SUPPLIERS', 'due_date' => '2025-10-20'],
    ['rfq_no' => 'RFQ202510-0002', 'status' => 'CREATED', 'due_date' => '2025-10-25'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการ RFQ</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>รายการ RFQ</h1>
    <table>
        <thead>
            <tr><th>เลขที่ RFQ</th><th>สถานะ</th><th>Due Date</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rfqs as $rfq): ?>
                <tr>
                    <td><?= htmlspecialchars($rfq['rfq_no']) ?></td>
                    <td><?= htmlspecialchars($rfq['status']) ?></td>
                    <td><?= htmlspecialchars($rfq['due_date']) ?></td>
                    <td><a href="rfqDetail.php?rfq_no=<?= urlencode($rfq['rfq_no']) ?>">ดูรายละเอียด</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>