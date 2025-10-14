<?php
session_start();
$usersFile = __DIR__ . '/../users.json';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $users = [];
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
    }
    foreach ($users as $user) {
        if ($user['role'] === 'supplier' && $user['username'] === $username && $user['password'] === $password) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบคู่ค้า</title>
    <style>
        form { max-width: 300px; margin: auto; }
        label { display: block; margin-top: 1rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>เข้าสู่ระบบสำหรับคู่ค้า</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>ชื่อผู้ใช้:
            <input type="text" name="username" required>
        </label>
        <label>รหัสผ่าน:
            <input type="password" name="password" required>
        </label>
        <button type="submit">เข้าสู่ระบบ</button>
    </form>
    <p><a href="supplierRegister.php">ลงทะเบียนคู่ค้าใหม่</a></p>
    <p><a href="../index.php">กลับไปหน้าเข้าสู่ระบบหลัก</a></p>
</body>
</html>