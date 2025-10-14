<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include("../connect.php");

// ป้องกันการ submit โดยไม่มีข้อมูล
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: employee_add.php");
    exit();
}

// รับค่าจากฟอร์ม
$full_name     = trim($_POST['full_name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$status        = $_POST['status'] ?? 'active';
$department_id = intval($_POST['department_id'] ?? 0);
$role          = $_POST['role'] ?? '';
$password      = $_POST['password'] ?? '';

// ตรวจสอบค่าว่าง
if ($full_name === '' || $email === '' || $department_id === 0) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'กรอกข้อมูลให้ครบถ้วน'];
    header("Location: employee_add.php");
    exit();
}

// ตรวจสอบอีเมลซ้ำใน users
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'อีเมลนี้มีอยู่ในระบบแล้ว'];
    header("Location: employee_add.php");
    exit();
}
$stmt->close();

// ถ้าไม่กรอกรหัสผ่าน ใช้ค่าเริ่มต้น
if ($password === '') $password = 'Emp123456';

// เข้ารหัสรหัสผ่าน
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// เพิ่มข้อมูลพนักงาน
$emp_sql = "INSERT INTO employees (full_name, phone, email, status, department_id)
            VALUES (?, ?, ?, ?, ?)";
$emp_stmt = $conn->prepare($emp_sql);
$emp_stmt->bind_param("ssssi", $full_name, $phone, $email, $status, $department_id);

if (!$emp_stmt->execute()) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'บันทึกพนักงานไม่สำเร็จ: ' . $conn->error];
    header("Location: employee_add.php");
    exit();
}

// ดึง employee_id ล่าสุด
$employee_id = $conn->insert_id;

// เพิ่มข้อมูลเข้า users
$user_sql = "INSERT INTO users (email, password_hash, role, status, employee_id, created_at)
             VALUES (?, ?, ?, 'active', ?, NOW())";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("sssi", $email, $password_hash, $role, $employee_id);

if ($user_stmt->execute()) {
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'เพิ่มพนักงานใหม่เรียบร้อยแล้ว'];
    header("Location: employeeManagement.php");
} else {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'บันทึกผู้ใช้ไม่สำเร็จ: ' . $conn->error];
    header("Location: employee_add.php");
}

$conn->close();
exit();
