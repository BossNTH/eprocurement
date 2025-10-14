<?php
require_once __DIR__ . '/../auth.php';
requireRole('manager');
// Sample PRs pending manager approval
$pendingPRs = [
    ['pr_no' => 'PR20251014-0001', 'employee' => 'Employee User', 'date' => '2025-10-14'],
    ['pr_no' => 'PR20251014-0003', 'employee' => 'Employee User', 'date' => '2025-10-14'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คิวรออนุมัติ PR</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>คิวรออนุมัติ PR</h1>
    <table>
        <thead>
            <tr><th>เลขที่ PR</th><th>ผู้ร้องขอ</th><th>วันที่</th><th>การดำเนินการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pendingPRs as $pr): ?>
                <tr>
                    <td><?= htmlspecialchars($pr['pr_no']) ?></td>
                    <td><?= htmlspecialchars($pr['employee']) ?></td>
                    <td><?= htmlspecialchars($pr['date']) ?></td>
                    <td><a href="prApprovalDetail.php?pr_no=<?= urlencode($pr['pr_no']) ?>">ตรวจสอบ/อนุมัติ</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ดผู้จัดการ</a></p>
</body>
</html>