<?php
session_start();
require_once("../connect.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  echo "<script>alert('ไม่พบรหัสพนักงานนี้'); window.location='employeeManagement.php';</script>";
  exit;
}

try {
  // เริ่ม Transaction
  $conn->begin_transaction();

  // ลบข้อมูลผู้ใช้ที่ผูกกับ employee_id นี้ก่อน
  $stmtUser = $conn->prepare("DELETE FROM users WHERE employee_id = ?");
  $stmtUser->bind_param("i", $id);
  $stmtUser->execute();

  // ลบพนักงาน
  $stmtEmp = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
  $stmtEmp->bind_param("i", $id);
  $stmtEmp->execute();

  // ยืนยันการลบ
  $conn->commit();

  echo "<script>alert('✅ ลบข้อมูลพนักงานและบัญชีผู้ใช้เรียบร้อยแล้ว'); window.location='employeeManagement.php';</script>";
} catch (Throwable $e) {
  $conn->rollback();
  echo "<script>alert('❌ ลบข้อมูลไม่สำเร็จ: {$e->getMessage()}'); window.location='employeeManagement.php';</script>";
}
?>
