<?php
session_start();
require_once "../connect.php";

$department_id     = intval($_POST['department_id'] ?? 0);
$name               = trim($_POST['name'] ?? '');
$head_employee_id   = trim($_POST['head_employee_id'] ?? '');
$head_employee_id   = ($head_employee_id === '') ? null : intval($head_employee_id);

if ($department_id <= 0 || $name === '') {
  echo "<script>alert('ข้อมูลไม่ครบถ้วน'); history.back();</script>"; exit();
}

/* โหลดข้อมูลเดิมของแผนก (เพื่อรู้หัวหน้าเก่า) */
$stmt = $conn->prepare("SELECT name, head_employee_id FROM departments WHERE department_id=? LIMIT 1");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$old) {
  echo "<script>alert('ไม่พบแผนก'); location='departmentManagement.php';</script>"; exit();
}
$old_head_id = $old['head_employee_id'] ? intval($old['head_employee_id']) : null;

/* === ฟังก์ชันช่วย === */
function emailByEmpId(mysqli $conn, int $empId): string {
  if ($empId <= 0) return '';
  $q = $conn->prepare("SELECT LOWER(email) email FROM employees WHERE employee_id=? LIMIT 1");
  $q->bind_param("i", $empId);
  $q->execute();
  $r = $q->get_result()->fetch_assoc();
  $q->close();
  return strtolower(trim((string)($r['email'] ?? '')));
}

function upsertUserRoleByEmail(mysqli $conn, string $email, string $role): void {
  if ($email === '') return;
  // ตรวจว่ามี user นี้หรือยัง
  $u = $conn->prepare("SELECT user_id FROM users WHERE LOWER(email)=? LIMIT 1");
  $u->bind_param("s", $email);
  $u->execute();
  $ur = $u->get_result();

  if ($ur->num_rows > 0) {
    // มีอยู่แล้ว → update role
    $id = intval($ur->fetch_assoc()['user_id']);
    $u->close();
    $upd = $conn->prepare("UPDATE users SET role=? WHERE user_id=?");
    $upd->bind_param("si", $role, $id);
    $upd->execute();
    $upd->close();
  } else {
    // ยังไม่มี → สร้างใหม่
    $u->close();
    $pwd = password_hash("Emp123456", PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO users(email, password_hash, role, status) VALUES(?, ?, ?, 'active')");
    $ins->bind_param("sss", $email, $pwd, $role);
    $ins->execute();
    $ins->close();
  }
}

function demoteEmpIdToBase(mysqli $conn, ?int $empId, string $baseRole): void {
  if (empty($empId)) return;
  $email = emailByEmpId($conn, $empId);
  if ($email === '') return;
  upsertUserRoleByEmail($conn, $email, $baseRole);
}

/* === Logic สำหรับ mapping แผนก → role === */
$is_proc_dept = (mb_strtolower($name, 'UTF-8') === mb_strtolower('จัดซื้อ', 'UTF-8'));
$base_role = $is_proc_dept ? 'procurement' : 'employee';
$head_role = $is_proc_dept ? 'procurement_manager' : 'manager';

try {
  $conn->begin_transaction();

  /* 1) อัปเดตข้อมูลแผนก */
  $sql = "UPDATE departments SET name=?, head_employee_id=? WHERE department_id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $name, $head_employee_id, $department_id);
  $stmt->execute();
  $stmt->close();

  /* 2) อัปเดต Role ผู้ใช้ */
  if (is_null($head_employee_id)) {
    // ไม่มีหัวหน้าใหม่ → ลดระดับหัวหน้าเก่า
    demoteEmpIdToBase($conn, $old_head_id, $base_role);
  } else {
    if (!empty($old_head_id) && $old_head_id !== $head_employee_id) {
      // เปลี่ยนหัวหน้า → ลดระดับหัวหน้าเก่าคนเดิม
      demoteEmpIdToBase($conn, $old_head_id, $base_role);
    }

    // โปรโมตหัวหน้าใหม่
    $newHeadEmail = emailByEmpId($conn, $head_employee_id);
    if ($newHeadEmail !== '') {
      if ($is_proc_dept) {
        // มีเพียง 1 procurement_manager เท่านั้น
        $dem = $conn->prepare("UPDATE users SET role='procurement' WHERE role='procurement_manager' AND LOWER(email)<>?");
        $dem->bind_param("s", $newHeadEmail);
        $dem->execute();
        $dem->close();
      }
      upsertUserRoleByEmail($conn, $newHeadEmail, $head_role);
    }
  }

  $conn->commit();
  echo "<script>alert('✅ บันทึกสำเร็จและอัปเดต Role เรียบร้อยแล้ว'); location='departmentManagement.php';</script>";
  exit();

} catch (Throwable $e) {
  $conn->rollback();
  $msg = addslashes($e->getMessage());
  echo "<script>alert('❌ บันทึกไม่สำเร็จ: {$msg}'); history.back();</script>";
  exit();
}
?>
