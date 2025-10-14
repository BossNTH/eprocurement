<?php
require_once __DIR__ . '/../auth.php';
requireRole('supplier');
// Sample RFQ invitations
$rfqInvitations = [
    ['rfq_no' => 'RFQ202510-0001', 'due_date' => '2025-10-20'],
    ['rfq_no' => 'RFQ202510-0003', 'due_date' => '2025-10-25'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำเชิญ RFQ ของฉัน</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h1>คำเชิญ RFQ ของฉัน</h1>
    <table>
        <thead>
            <tr><th>เลขที่ RFQ</th><th>Due Date</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rfqInvitations as $rfq): ?>
                <tr>
                    <td><?= htmlspecialchars($rfq['rfq_no']) ?></td>
                    <td><?= htmlspecialchars($rfq['due_date']) ?></td>
                    <td><a href="quoteSubmit.php?rfq_no=<?= urlencode($rfq['rfq_no']) ?>">ส่งใบเสนอราคา</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>