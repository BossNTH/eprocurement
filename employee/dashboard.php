<?php
require_once __DIR__ . "/partials/emp_header.php";
$db = $GLOBALS['conn'] ?? null;
if (!$db) { require_once __DIR__ . "/../connect.php"; $db = $conn; }

$userId = (int)($_SESSION['user_id'] ?? 0);
$displayName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? "พนักงาน";
if ($userId <= 0) { header("Location: ../index.php"); exit; }

/* === หา employee_id โดย map users.username -> employees.employee_code
      (และ fallback ตามอีเมล หากคุณใช้แบบนั้น) === */
$empId = (int)($_SESSION['employee_id'] ?? 0);
if ($empId <= 0) {
  $stmt = $db->prepare("
    SELECT e2.employee_id AS employee_id
    FROM users u
    LEFT JOIN employees e2 ON e2.email = u.email            -- fallback กรณีแม็ปด้วยอีเมล
    WHERE u.user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (empty($row['employee_id'])) {
    die('<div class="alert alert-danger m-4">
      ไม่พบการแม็ปผู้ใช้กับพนักงาน (ตรวจสอบว่า users.username = employees.employee_code หรือ users.email = employees.email)
    </div>');
  }
  $empId = (int)$row['employee_id'];
  $_SESSION['employee_id'] = $empId;
}

/* === ตัวเลขสรุป (กรองด้วย employee_id) === */
$myTotal = $myPending = $myApproved = $myRejected = 0;

$stmt = $db->prepare("SELECT COUNT(*) c FROM purchase_requisitions WHERE pr_no=?");
$stmt->bind_param("i", $empId); $stmt->execute();
$myTotal = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) c FROM purchase_requisitions WHERE pr_no=? AND status='submitted'");
$stmt->bind_param("i", $empId); $stmt->execute();
$myPending = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) c FROM purchase_requisitions WHERE pr_no=? AND status='approved'");
$stmt->bind_param("i", $empId); $stmt->execute();
$myApproved = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) c FROM purchase_requisitions WHERE pr_no=? AND status='rejected'");
$stmt->bind_param("i", $empId); $stmt->execute();
$myRejected = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();

/* === รายการล่าสุดของฉัน (คอลัมน์ตามสคีมาจริง) === */
$recent = [];
$stmt = $db->prepare("
  SELECT pr_no, status, request_date
  FROM purchase_requisitions
  WHERE pr_no=?
  ORDER BY request_date DESC
  LIMIT 5
");
$stmt->bind_param("i", $empId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $recent[] = $row; }
$stmt->close();

function status_badge_class($status) {
  switch (strtolower($status)) {
    case 'approved': return 'bg-success';
    case 'submitted': return 'bg-warning text-dark';
    case 'rejected': return 'bg-danger';
    default: return 'bg-secondary';
  }
}
?>

<!-- ====== CONTENT (ใส่ส่วน HTML แสดงผลกลับมา) ====== -->
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h4 class="mb-0">สวัสดี, <?= htmlspecialchars($displayName) ?></h4>
      <small class="text-muted">หน้าหลักพนักงาน • อัปเดตล่าสุด <?= date('d M Y H:i') ?></small>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">PR ทั้งหมด</div>
          <div class="display-6 fw-bold"><?= $myTotal ?></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">รออนุมัติ (submitted)</div>
          <div class="display-6 fw-bold"><?= $myPending ?></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">อนุมัติแล้ว</div>
          <div class="display-6 fw-bold text-success"><?= $myApproved ?></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">ไม่อนุมัติ</div>
          <div class="display-6 fw-bold text-danger"><?= $myRejected ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent PRs -->
  <div class="card shadow-sm mt-4">
    <div class="card-header bg-white">
      <strong>รายการล่าสุดของฉัน</strong>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 140px;">PR No.</th>
              <th style="width: 160px;">สถานะ</th>
              <th style="width: 170px;">วันที่สร้าง</th>
              <th style="width: 90px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent)): ?>
              <tr><td colspan="4" class="text-center text-muted p-4">ยังไม่มีรายการ</td></tr>
            <?php else: foreach ($recent as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['pr_no']) ?></td>
                <td>
                  <span class="badge <?= status_badge_class($r['status']) ?>">
                    <?= htmlspecialchars($r['status']) ?>
                  </span>
                </td>
                <td><?= !empty($r['request_date'])
                        ? date('d M Y', strtotime($r['request_date']))
                        : '-' ?></td>
                <td><a class="btn btn-sm btn-outline-primary"
                       href="pr_view.php?pr_no=<?= urlencode($r['pr_no']) ?>">ดู</a></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="alert alert-info mt-4">
    เคล็ดลับ: สถานะ <strong>submitted</strong> = รออนุมัติ, <strong>approved</strong> = อนุมัติแล้ว, <strong>rejected</strong> = ไม่อนุมัติ
  </div>
</div>

<!-- <?php require_once __DIR__ . "/partials/staff_footer.php"; ?> -->
