<?php
require_once __DIR__ . '/../auth.php';
requireRole('manager');
$prNo = $_GET['pr_no'] ?? '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $message = 'คุณได้อนุมัติ PR เรียบร้อยแล้ว (ตัวอย่าง).';
    } elseif ($action === 'reject') {
        $reason = trim($_POST['reject_reason'] ?? '');
        $message = 'คุณได้ปฏิเสธ PR พร้อมเหตุผล: ' . htmlspecialchars($reason);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด PR สำหรับอนุมัติ</title>
</head>
<body>
    <h1>รายละเอียด PR สำหรับอนุมัติ</h1>
    <?php if ($prNo): ?>
        <p>เลขที่ PR: <?= htmlspecialchars($prNo) ?></p>
        <p>รายละเอียด PR (ตัวอย่างข้อมูล)</p>
        <?php if ($message): ?>
            <p style="color: green;"><?= $message ?></p>
        <?php else: ?>
            <form method="post">
                <button type="submit" name="action" value="approve">อนุมัติ</button>
                <br><br>
                <label>ปฏิเสธ พร้อมเหตุผล:<br>
                    <textarea name="reject_reason" rows="3" cols="50"></textarea>
                </label><br>
                <button type="submit" name="action" value="reject">ปฏิเสธ</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>ไม่พบ PR ที่ระบุ</p>
    <?php endif; ?>
    <p><a href="prApprovalQueue.php">กลับไปคิวรออนุมัติ</a></p>
</body>
</html>