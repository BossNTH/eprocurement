<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include("../connect.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: employeeManagement.php");
    exit();
}

$employee_id = intval($_POST['employee_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$status = $_POST['status'] ?? 'active';
$department_id = intval($_POST['department_id'] ?? 0);
$role = $_POST['role'] ?? '';

if ($employee_id <= 0 || $full_name==='' || $email==='') {
    $_SESSION['msg'] = ['type'=>'error','text'=>'กรอกข้อมูลให้ครบถ้วน'];
    header("Location: employee_edit.php?id=$employee_id");
    exit();
}

// ตรวจสอบอีเมลซ้ำ (ยกเว้นของตัวเอง)
$check = $conn->prepare("SELECT user_id FROM users WHERE email=? AND employee_id<>?");
$check->bind_param("si", $email, $employee_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['msg'] = ['type'=>'error','text'=>'อีเมลนี้ถูกใช้งานแล้ว'];
    header("Location: employee_edit.php?id=$employee_id");
    exit();
}
$check->close();

// อัปเดต employees
$emp_sql = "UPDATE employees
            SET full_name=?, phone=?, email=?, status=?, department_id=?, updated_at=NOW()
            WHERE employee_id=?";
$stmt = $conn->prepare($emp_sql);
$stmt->bind_param("ssssii", $full_name, $phone, $email, $status, $department_id, $employee_id);
$stmt->execute();

// อัปเดต users
$user_sql = "UPDATE users
             SET email=?, role=?, status='active', updated_at=NOW()
             WHERE employee_id=?";
$u_stmt = $conn->prepare($user_sql);
$u_stmt->bind_param("ssi", $email, $role, $employee_id);
$u_stmt->execute();

$_SESSION['msg'] = ['type'=>'success','text'=>'บันทึกข้อมูลเรียบร้อยแล้ว'];
header("Location: employeeManagement.php");
exit();
