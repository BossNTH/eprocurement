<?php
session_start();
// Registration page for suppliers
$usersFile = __DIR__ . '/../users.json';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    if (!$username || !$password || !$name) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {
        $users = [];
        if (file_exists($usersFile)) {
            $users = json_decode(file_get_contents($usersFile), true) ?: [];
        }
        // Check for duplicate username
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $error = 'ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาเลือกชื่ออื่น';
                break;
            }
        }
        if (!$error) {
            $users[] = [
                'username' => $username,
                'password' => $password,
                'role' => 'supplier',
                'name' => $name
            ];
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'ลงทะเบียนสำเร็จ! คุณสามารถเข้าสู่ระบบได้ทันที';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียนคู่ค้า</title>
    <style>
        form { max-width: 300px; margin: auto; }
        label { display: block; margin-top: 1rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>ลงทะเบียนคู่ค้า</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
        <p><a href="../index.php">กลับไปหน้าเข้าสู่ระบบ</a></p>
    <?php else: ?>
        <form method="post">
            <label>ชื่อผู้ใช้:
                <input type="text" name="username" required>
            </label>
            <label>รหัสผ่าน:
                <input type="password" name="password" required>
            </label>
            <label>ชื่อ/ชื่อบริษัท:
                <input type="text" name="name" required>
            </label>
            <button type="submit">ลงทะเบียน</button>
        </form>
        <p><a href="../index.php">กลับไปหน้าเข้าสู่ระบบ</a></p>
    <?php endif; ?>
</body>
</html>