<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}
require_once("../connect.php");

// ดึงข้อมูลประเภทสินค้า
$sql = "SELECT category_id, name FROM product_categories ORDER BY category_id ASC";
$res = $conn->query($sql);
require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการประเภทสินค้า</title>
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
      <h2 class="mb-1"><i class="bi bi-layers me-2"></i>จัดการประเภทสินค้า</h2>
      <div class="small opacity-75">เพิ่ม แก้ไข หรือลบประเภทสินค้าภายในระบบ</div>
    </div>
    <a href="#" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-circle me-1"></i> เพิ่มประเภทสินค้า
    </a>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr >
              <th width="10%" class="text-center">รหัส</th>
              <th>ชื่อประเภทสินค้า</th>
              <th width="20%" class="text-center">การจัดการ</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($res->num_rows>0): ?>
            <?php while($row=$res->fetch_assoc()): ?>
              <tr>
                <td class="text-center"><?= $row['category_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal<?= $row['category_id'] ?>">
                    <i class="bi bi-pencil-square"></i> แก้ไข
                  </button>
                  <button class="btn btn-sm btn-outline-danger"
                          onclick="confirmDelete(<?= $row['category_id'] ?>)">
                    <i class="bi bi-trash"></i> ลบ
                  </button>
                </td>
              </tr>

              <!-- Modal แก้ไข -->
              <div class="modal fade" id="editModal<?= $row['category_id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content bg-slate-800 text-light" style="background:#1e293b;">
                    <form action="product_category_edit.php" method="POST">
                      <div class="modal-header border-info text-info">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> แก้ไขประเภทสินค้า</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $row['category_id'] ?>">
                        <div class="mb-3">
                          <label class="form-label">ชื่อประเภทสินค้า</label>
                          <input type="text" name="name" class="form-control bg-dark text-light border-0"
                                 value="<?= htmlspecialchars($row['name']) ?>" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal เพิ่ม -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:#1e293b; color:#e2e8f0;">
      <form action="product_category_save.php" method="POST">
        <div class="modal-header border-success text-success">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทสินค้า</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">ชื่อประเภทสินค้า</label>
            <input type="text" name="category_name" class="form-control bg-dark text-light border-0" required maxlength="100">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึก</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id){
  Swal.fire({
    title:'ยืนยันการลบ?',
    text:'คุณแน่ใจหรือไม่ว่าต้องการลบประเภทสินค้านี้',
    icon:'warning',
    showCancelButton:true,
    confirmButtonColor:'#d33',
    cancelButtonColor:'#6c757d',
    confirmButtonText:'ลบข้อมูล',
    cancelButtonText:'ยกเลิก'
  }).then((result)=>{
    if(result.isConfirmed){
      window.location='product_category_delete.php?id='+id;
    }
  });
}
</script>
</body>
</html>

<?php require __DIR__ . '/partials/admin_footer.php'; ?>
