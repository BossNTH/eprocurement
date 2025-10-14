<?php
session_start();
require_once "connect.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function inpost($k){ return trim($_POST[$k] ?? ''); }

// ===== รับค่าจากฟอร์ม =====
$email       = strtolower(inpost('username'));  // ใช้อีเมลเป็น username
$password    = inpost('password');
$confirm     = inpost('confirm_password');
$sup_name    = inpost('supplier_name');
$contact     = inpost('contact_info');
$address     = inpost('address');
$phone       = inpost('phone');
$tax_id      = inpost('tax_id');
$role        = 'supplier'; // บังคับเป็น supplier

// ===== ตรวจสอบข้อมูลเบื้องต้น =====
if ($email === '' || $password === '' || $confirm === '' || $sup_name === '') {
  echo "<script>alert('❌ กรุณากรอกข้อมูลให้ครบถ้วน'); history.back();</script>"; exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo "<script>alert('❌ รูปแบบอีเมลไม่ถูกต้อง'); history.back();</script>"; exit;
}
if ($password !== $confirm) {
  echo "<script>alert('❌ รหัสผ่านไม่ตรงกัน'); history.back();</script>"; exit;
}
if (mb_strlen($password) < 6) {
  echo "<script>alert('❌ รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'); history.back();</script>"; exit;
}

try {
  $conn->begin_transaction();

  // ===== ตรวจสอบอีเมลซ้ำใน users =====
  $chk = $conn->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
  $chk->bind_param("s", $email);
  $chk->execute();
  $chk->store_result();
  if ($chk->num_rows > 0) {
    echo "<script>alert('❌ อีเมลนี้ถูกใช้งานแล้ว'); history.back();</script>";
    $chk->close(); $conn->rollback(); exit;
  }
  $chk->close();

  // ===== สร้าง hash รหัสผ่าน =====
  $hash = password_hash($password, PASSWORD_DEFAULT);

  // ===== เพิ่มข้อมูลผู้ขายใน suppliers =====
  $status = 'active';
  $insSup = $conn->prepare("
    INSERT INTO suppliers (supplier_name, contact_name, address, phone, email, tax_id, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $insSup->bind_param("sssssss", $sup_name, $contact, $address, $phone, $email, $tax_id, $status);
  $insSup->execute();
  $supplier_id = $conn->insert_id;
  $insSup->close();

  // ===== เพิ่มข้อมูลผู้ใช้ใน users =====
  $insUser = $conn->prepare("
    INSERT INTO users (email, password_hash, role, status, supplier_id)
    VALUES (?, ?, ?, 'active', ?)
  ");
  $insUser->bind_param("sssi", $email, $hash, $role, $supplier_id);
  $insUser->execute();
  $insUser->close();

  $conn->commit();

  echo "<script>alert('✅ สมัครสมาชิกผู้ขายเรียบร้อย'); window.location='login.php';</script>";
  exit;

} catch (Throwable $e) {
  if ($conn->errno) $conn->rollback();
  // error_log($e->getMessage());
  echo "<script>alert('❌ สมัครสมาชิกไม่สำเร็จ กรุณาลองใหม่'); window.location='supplierRegister.php';</script>";
  exit;
} finally {
  if ($conn && $conn->ping()) $conn->close();
}
?>
