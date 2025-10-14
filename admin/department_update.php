<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php"); exit();
}
require_once "../connect.php";

$department_id     = intval($_POST['department_id'] ?? 0);
$name   = trim($_POST['name'] ?? '');
$head_employee_id  = trim($_POST['head_employee_id'] ?? '');
$head_employee_id  = ($head_employee_id === '') ? null : intval($head_employee_id);

if ($department_id <= 0 || $name === '') {
  echo "<script>alert('ข้อมูลไม่ครบถ้วน'); history.back();</script>"; exit();
}

/* โหลดข้อมูลเดิมของแผนก (สำหรับรู้หัวหน้าเก่า) */
$stmt = $conn->prepare("SELECT name, head_employee_id FROM departments WHERE department_id=? LIMIT 1");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$old) {
  echo "<script>alert('ไม่พบแผนก'); location='departmentManagement.php';</script>"; exit();
}
$old_head_id = $old['head_employee_id'] ? intval($old['head_employee_id']) : null;

/* ถ้ากำหนดหัวหน้าใหม่ ตรวจว่าอยู่แผนกนี้จริง */
if (!is_null($head_employee_id)) {
  $chk = $conn->prepare("SELECT 1 FROM employees WHERE employee_id=? AND department_id=?");
  $chk->bind_param("ii", $head_employee_id, $department_id);
  $chk->execute();
  $ok = $chk->get_result()->num_rows > 0;
  $chk->close();
  if (!$ok) {
    echo "<script>alert('พนักงานที่เลือกไม่ได้อยู่ในแผนกนี้'); history.back();</script>"; exit();
  }
}

/* === Helpers (ผูก employees.email ↔ users.username เท่านั้น) === */
function emailByEmpId(mysqli $conn, int $empId): string {
  if ($empId <= 0) return '';
  $q = $conn->prepare("SELECT LOWER(email) email FROM employees WHERE employee_id=? LIMIT 1");
  $q->bind_param("i", $empId);
  $q->execute();
  $r = $q->get_result()->fetch_assoc();
  $q->close();
  $email = strtolower(trim((string)($r['email'] ?? '')));
  return $email;
}
function upsertUserRoleByEmail(mysqli $conn, string $email, string $role): void {
  if ($email === '') return;
  $u = $conn->prepare("SELECT user_id FROM users WHERE LOWER(email)=? LIMIT 1");
  $u->bind_param("s", $email);
  $u->execute();
  $ur = $u->get_result();
  if ($ur->num_rows > 0) {
    $id = intval($ur->fetch_assoc()['id']);
    $u->close();
    $upd = $conn->prepare("UPDATE users SET role=? WHERE user_id=?");
    $upd->bind_param("si", $role, $id);
    $upd->execute(); $upd->close();
  } else {
    $u->close();
    $pwd = password_hash("Emp123456", PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO users(email, password, role) VALUES(?, ?, ?)");
    $ins->bind_param("sss", $email, $pwd, $role);
    $ins->execute(); $ins->close();
  }
}
function demoteEmpIdToBase(mysqli $conn, ?int $empId, string $baseRole): void {
  if (empty($empId)) return;
  $email = emailByEmpId($conn, $empId);
  if ($email === '') return;
  upsertUserRoleByEmail($conn, $email, $baseRole);
}

/* กำหนดชนิดแผนก → base/head role */
$is_proc_dept = (mb_strtolower($name, 'UTF-8') === mb_strtolower('จัดซื้อ', 'UTF-8'));
$base_role = $is_proc_dept ? 'procurement' : 'employee';
$head_role = $is_proc_dept ? 'procurement_manager' : 'manager';

try {
  $conn->begin_transaction();

  /* 1) อัปเดตตาราง departments */
  $sql = "UPDATE departments SET name=?, head_employee_id=? WHERE department_id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $name, $head_employee_id, $department_id);
  if (!$stmt->execute()) throw new Exception($stmt->error);
  $stmt->close();

  /* 2) จัดการ role ตาม mapping: departments.head_employee_id -> employees.employee_id -> employees.email <-> users.username */
  if (is_null($head_employee_id)) {
    // ลบหัวหน้าออก → ลดระดับหัวหน้าเก่าของ "แผนกนี้" เท่านั้น
    demoteEmpIdToBase($conn, $old_head_id, $base_role);
  } else {
    // มีหัวหน้าใหม่
    if (!empty($old_head_id) && $old_head_id !== $head_employee_id) {
      // ลดระดับหัวหน้าเก่าของ "แผนกนี้" เท่านั้น
      demoteEmpIdToBase($conn, $old_head_id, $base_role);
    }

    // โปรโมตหัวหน้าใหม่ของแผนกนี้ตามประเภทแผนก
    $newHeadEmail = emailByEmpId($conn, $head_employee_id);
    if ($newHeadEmail !== '') {
      // ถ้าเป็น "จัดซื้อ" ⇒ คง unique procurement_manager ทั้งระบบ
      if ($is_proc_dept) {
        $dem = $conn->prepare("UPDATE users SET role='procurement' WHERE role='procurement_manager' AND LOWER(username)<>?");
        $dem->bind_param("s", $newHeadEmail);
        $dem->execute(); $dem->close();
      }
      // ตั้ง role ให้หัวหน้าใหม่
      upsertUserRoleByEmail($conn, $newHeadEmail, $head_role);
    }
  }

  $conn->commit();
  echo "<script>alert('✅ บันทึกสำเร็จ'); location='departmentManagement.php';</script>";
  exit();
} catch (Throwable $e) {
  $conn->rollback();
  $msg = addslashes($e->getMessage());
  echo "<script>alert('❌ บันทึกไม่สำเร็จ: {$msg}'); history.back();</script>";
  exit();
}
