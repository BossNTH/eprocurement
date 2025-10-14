<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}
include("../connect.php");

// ดึงข้อมูลแผนกทั้งหมด
$sql = "SELECT d.department_id, d.name, 
        e.full_name AS head_name 
        FROM departments d
        LEFT JOIN employees e ON d.head_employee_id = e.employee_id
        ORDER BY d.name ASC";
$result = $conn->query($sql);

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
    <a href="department_add.php" class="btn btn-add">
      <i class="bi bi-plus-lg me-1"></i> เพิ่มแผนก
    </a>
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
