<?php
require_once __DIR__ . '/../auth.php';
requireRole('employee');
// In a real system, this would fetch PRs created by the logged-in employee from the database.
$samplePRs = [
    ['pr_no' => 'PR20251014-0001', 'status' => 'SUBMITTED', 'date' => '2025-10-14'],
    ['pr_no' => 'PR20251014-0002', 'status' => 'DRAFT', 'date' => '2025-10-14'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการ PR ของฉัน</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>รายการ PR ของฉัน</h1>
    <table>
        <thead>
            <tr><th>เลขที่ PR</th><th>สถานะ</th><th>วันที่</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($samplePRs as $pr): ?>
                <tr>
                    <td><?= htmlspecialchars($pr['pr_no']) ?></td>
                    <td><?= htmlspecialchars($pr['status']) ?></td>
                    <td><?= htmlspecialchars($pr['date']) ?></td>
                    <td><a href="prDetail.php?pr_no=<?= urlencode($pr['pr_no']) ?>">ดูรายละเอียด</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>