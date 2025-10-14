<?php
require_once __DIR__ . '/../auth.php';
requireRole('employee');
// Get PR number from query string
$prNo = $_GET['pr_no'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด PR</title>
</head>
<body>
    <h1>รายละเอียด PR</h1>
    <?php if ($prNo): ?>
        <p>เลขที่ PR: <?= htmlspecialchars($prNo) ?></p>
        <p>รายละเอียดตัวอย่างของ PR เลขที่นี้ (ในระบบจริงจะดึงข้อมูลจากฐานข้อมูล).</p>
    <?php else: ?>
        <p>ไม่พบ PR ที่คุณต้องการ</p>
    <?php endif; ?>
    <p><a href="prList.php">กลับไปหน้ารายการ PR</a></p>
</body>
</html>