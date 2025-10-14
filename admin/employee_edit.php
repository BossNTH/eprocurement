<?php
session_start();

include("../connect.php");

// รับ id
$employee_id = intval($_GET['id'] ?? 0);
if ($employee_id <= 0) {
    header("Location: employeeManagement.php");
    exit();
}

/* ===== ดึงข้อมูลพนักงาน ===== */
$sql = "SELECT e.*, u.role, u.status AS user_status
        FROM employees e
        LEFT JOIN users u ON e.employee_id = u.employee_id
        WHERE e.employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$emp = $result->fetch_assoc();
if (!$emp) {
    header("Location: employeeManagement.php");
    exit();
}

/* ===== ดึงแผนก ===== */
$departments = [];
$res = $conn->query("SELECT department_id, name FROM departments ORDER BY name ASC");
while ($row = $res->fetch_assoc()) $departments[] = $row;

/* ===== Role ===== */
$defaults = ['employee','manager','admin','seller','procurement','procurement_manager'];
$roles = [];
$rres = $conn->query("SELECT DISTINCT role FROM users WHERE role <> ''");
while ($r = $rres->fetch_assoc()) $roles[] = trim($r['role']);
$roles = array_values(array_unique(array_merge($defaults, $roles)));

require __DIR__ . '/partials/admin_header.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แก้ไขพนักงาน</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body{ background:#f6f7fb; }
    .card{ border:0; border-radius:18px; box-shadow:0 10px 25px rgba(0,0,0,.06); }
    .input-group-text{ background:#f1f4f9; border:none; }
    .required:after{ content:" *"; color:#dc3545; }
  </style>
</head>

<body style="background:#f4f6fb;">
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
      <div class="card mb-4">
        <div class="bg-primary text-white p-4 rounded-top-4">
          <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลพนักงาน</h3>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <form id="editForm" action="employee_update.php" method="POST" novalidate>
            <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label required">ชื่อพนักงาน</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                  <input type="text" name="full_name" class="form-control" required
                         value="<?= htmlspecialchars($emp['full_name']) ?>">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label required">Email (Username)</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                  <input type="email" name="email" class="form-control" required
                         value="<?= htmlspecialchars($emp['email']) ?>">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">เบอร์โทรศัพท์</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                  <input type="tel" name="phone" class="form-control"
                         value="<?= htmlspecialchars($emp['phone']) ?>">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label required">แผนก</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                  <select name="department_id" class="form-select" required>
                    <?php foreach ($departments as $d): ?>
                      <option value="<?= $d['department_id'] ?>"
                        <?= ($emp['department_id']==$d['department_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label required">สถานะ</label>
                <select name="status" class="form-select">
                  <option value="active" <?= $emp['status']=='active'?'selected':'' ?>>Active</option>
                  <option value="inactive" <?= $emp['status']=='inactive'?'selected':'' ?>>Inactive</option>
                  <option value="terminated" <?= $emp['status']=='terminated'?'selected':'' ?>>Terminated</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label required">สิทธิ์การใช้งาน (Role)</label>
                <select name="role" class="form-select" required>
                  <?php foreach ($roles as $r): ?>
                    <option value="<?= htmlspecialchars($r) ?>"
                      <?= ($emp['role']==$r)?'selected':'' ?>>
                      <?= htmlspecialchars($r) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
              <a href="employeeManagement.php" class="btn btn-light">ย้อนกลับ</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> บันทึกการเปลี่ยนแปลง
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

<?php require __DIR__ . '/partials/admin_footer.php'; ?>
