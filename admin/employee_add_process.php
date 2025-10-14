<?php
session_start();
require_once("../connect.php");



// รับค่าจากฟอร์ม
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$department_id = intval($_POST['department_id'] ?? 0);
$status = trim($_POST['status'] ?? 'Active');

// ตรวจสอบความถูกต้องเบื้องต้น
if ($full_name === '' || $email === '' || $phone === '' || $department_id <= 0) {
  echo "<script>alert('⚠️ กรุณากรอกข้อมูลให้ครบถ้วน'); history.back();</script>";
  exit;
}

try {
  $conn->begin_transaction();

  $sql = "INSERT INTO employees (full_name, email, phone, department_id, status)
          VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssis", $full_name, $email, $phone, $department_id, $status);
  $stmt->execute();

  $conn->commit();
  echo "<script>alert('✅ เพิ่มข้อมูลพนักงานเรียบร้อย'); window.location='employeeManagement.php';</script>";

} catch (Throwable $e) {
  $conn->rollback();
  error_log("Employee Add Error: " . $e->getMessage());
  echo "<script>alert('❌ เกิดข้อผิดพลาดในการเพิ่มพนักงาน'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
