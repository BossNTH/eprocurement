<?php
session_start();
require_once("../connect.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = intval($_POST['employee_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$department_id = intval($_POST['department_id'] ?? 0);
$status = trim($_POST['status'] ?? 'Active');

if ($id <= 0 || $full_name === '' || $email === '' || $phone === '' || $department_id <= 0) {
  echo "<script>alert('⚠️ ข้อมูลไม่ครบถ้วนหรือไม่ถูกต้อง'); history.back();</script>";
  exit;
}

try {
  $conn->begin_transaction();

  $sql = "UPDATE employees 
          SET full_name=?, email=?, phone=?, department_id=?, status=? 
          WHERE employee_id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssisi", $full_name, $email, $phone, $department_id, $status, $id);
  $stmt->execute();

  $conn->commit();
  echo "<script>alert('✅ บันทึกการแก้ไขเรียบร้อย'); window.location='employeeManagement.php';</script>";

} catch (Throwable $e) {
  $conn->rollback();
  error_log("Employee Edit Error: " . $e->getMessage());
  echo "<script>alert('❌ ไม่สามารถบันทึกการแก้ไขได้'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
