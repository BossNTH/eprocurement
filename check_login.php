<?php
session_start();
require_once "connect.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function inpost($k){ return trim($_POST[$k] ?? ''); }

$email_input = strtolower(inpost('username'));
$password_input = inpost('password');

if ($email_input === '' || $password_input === '') {
    echo "<script>alert('กรุณากรอกอีเมลและรหัสผ่าน'); window.location='index.php';</script>";
    exit;
}

try {
    // ===== ค้นหาผู้ใช้จาก email =====
    $stmt = $conn->prepare("SELECT user_id, email, password_hash, role, status, supplier_id, employee_id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('❌ ไม่พบบัญชีผู้ใช้นี้'); window.location='index.php';</script>";
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // ===== ตรวจสอบสถานะ =====
    if ($user['status'] !== 'active') {
        echo "<script>alert('บัญชีของคุณถูกระงับการใช้งาน'); window.location='index.php';</script>";
        exit;
    }

    // ===== ตรวจสอบรหัสผ่าน =====
    if (!password_verify($password_input, $user['password_hash'])) {
        echo "<script>alert('❌ รหัสผ่านไม่ถูกต้อง'); window.location='index.php';</script>";
        exit;
    }

    // ===== สร้าง session =====
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // ===== เปลี่ยนหน้าไปตาม role =====
    switch ($user['role']) {
        case 'admin':              header("Location: admin/dashboard.php"); break;
        case 'employee':           header("Location: employee/dashboard.php"); break;
        case 'manager':            header("Location: manager/dashboard.php"); break;
        case 'procurement':        header("Location: procurement/dashboard.php"); break;
        case 'procurement_manager':header("Location: procurement_manager/dashboard.php"); break;
        case 'supplier':           header("Location: supplier/dashboard.php"); break;
        default:
            echo "<script>alert('ไม่พบสิทธิ์การใช้งานที่รองรับ'); window.location='index.php';</script>";
    }
    exit;

} catch (Throwable $e) {
    // บันทึก error ลง log (ใน production)
    // error_log($e->getMessage());
    echo "<script>alert('เกิดข้อผิดพลาดในการเข้าสู่ระบบ'); window.location='index.php';</script>";
    exit;
}
?>
