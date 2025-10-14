<?php
session_start();
include("../connect.php");

// ดึงข้อมูลแผนกทั้งหมด
$sql = "SELECT d.department_id, d.name, e.full_name AS head_name 
        FROM departments d
        LEFT JOIN employees e ON d.head_employee_id = e.employee_id
        ORDER BY d.name ASC";
$result = $conn->query($sql);

// ดึงรายชื่อพนักงาน (สำหรับเลือกหัวหน้าใน Modal)
$employees = $conn->query("
    SELECT employee_id, full_name
    FROM employees
    WHERE status='active'
    ORDER BY full_name
")->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการแผนก</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background:#f6f7fb; }
    .page-head {
      background:linear-gradient(135deg,#0d6efd,#5b9dff);
      color:#fff;
      border-radius:18px;
      padding:20px 22px;
      box-shadow:0 10px 25px rgba(13,110,253,.25);
    }
    .card {
      border:0;
      border-radius:18px;
      box-shadow:0 10px 25px rgba(0,0,0,.06);
    }
    table th {
      background:#f1f4f9;
      font-weight:600;
    }
    .btn-add {
      background:linear-gradient(135deg,#198754,#20c997);
      color:#fff;
      border:0;
      border-radius:10px;
      box-shadow:0 4px 10px rgba(25,135,84,.2);
    }
    .btn-add:hover { opacity:.9; }
    .modal-content {
      border-radius:16px;
      border:0;
      box-shadow:0 10px 25px rgba(0,0,0,.2);
    }
    .modal-header {
      background:linear-gradient(135deg,#0d6efd,#20c997);
      color:#fff;
      border-radius:16px 16px 0 0;
    }
    label { font-weight:500; }
    select, input {
      border-radius:8px !important;
    }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <!-- Header -->
  <div class="page-head d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1"><i class="bi bi-diagram-3 me-2"></i>จัดการแผนก</h2>
      <div class="small opacity-75">เพิ่ม แก้ไข หรือลบข้อมูลแผนกในระบบ</div>
    </div>
    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addDeptModal">
      <i class="bi bi-plus-lg me-1"></i> เพิ่มแผนก
    </button>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th width="10%"  class="text-center">รหัสแผนก</th>
              <th>ชื่อแผนก</th>
              <th>หัวหน้าแผนก</th>
              <th width="20%"  class="text-center">การจัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td class="text-center"><?= $row['department_id'] ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['head_name'] ?? '-') ?></td>
                  <td class="text-center">
                    <a href="department_edit.php?id=<?= $row['department_id'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                       <i class="bi bi-pencil-square"></i> แก้ไข
                    </a>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmDelete(<?= $row['department_id'] ?>)">
                      <i class="bi bi-trash"></i> ลบ
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูลแผนก</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ======================== Modal เพิ่มแผนก ======================== -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="department_save.php">
        <div class="modal-header">
          <h5 class="modal-title" id="addDeptModalLabel">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มแผนกใหม่
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">ชื่อแผนก</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="เช่น ฝ่ายจัดซื้อ / ฝ่ายบัญชี" required>
          </div>

          <div class="mb-3">
            <label for="head_employee_id" class="form-label">หัวหน้าแผนก</label>
            <select class="form-select" id="head_employee_id" name="head_employee_id">
              <option value="">-- เลือกหัวหน้าแผนก --</option>
              <?php foreach ($employees as $e): ?>
                <option value="<?= $e['employee_id'] ?>"><?= htmlspecialchars($e['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save2 me-1"></i> บันทึก
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> ยกเลิก
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================== Script ======================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
  Swal.fire({
    title: 'ยืนยันการลบ?',
    text: "คุณแน่ใจหรือไม่ว่าต้องการลบแผนกนี้",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'ลบข้อมูล',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = 'department_delete.php?id=' + id;
    }
  });
}
</script>

</body>
</html>

<?php require __DIR__ . '/partials/admin_footer.php'; ?>
