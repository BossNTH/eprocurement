<?php
session_start();
include("../connect.php");

// handle optional search
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
  $stmt = $conn->prepare("SELECT user_id, email, role FROM users WHERE email LIKE ? ORDER BY user_id");
  $like = "%" . $search . "%";
  $stmt->bind_param('s', $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT user_id, email, role FROM users ORDER BY user_id");
}

require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>สมาชิกทั้งหมด | ระบบจัดซื้อสมุนไพร</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
 body {
        background: linear-gradient(180deg, #f0fdfa, #f9fafb);
        color: #0f172a;
        font-family: "Prompt", sans-serif;
    }

  .main-wrapper {
    padding: 2rem;
  }

  .page-head {
    background: linear-gradient(135deg, #2563eb, #14b8a6);
    color: #fff;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 4px 15px rgba(37,99,235,0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .page-head h2 {
    font-size: 1.5rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .btn-add {
    background: linear-gradient(135deg, #14b8a6, #0d9488);
    color: #fff;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(13,148,136,0.3);
    transition: 0.2s;
  }

  .btn-add:hover {
    transform: translateY(-1px);
    opacity: .9;
  }

  .search-bar {
    margin-top: 1.5rem;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
  }

  .card {
    background: #ffffffff;
    border: 1px solid rgba(20,184,166,0.3);
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
    margin-top: 1.5rem;
  }

  table {
    color: #e2e8f0;
  }

  table thead {
    background: #334155;
    color: #a5f3fc;
  }

  table tbody tr:hover {
    background-color: rgba(20,184,166,0.1);
  }

  .btn-warning {
    background-color: #f59e0b;
    border: none;
    color: #fff;
  }
  .btn-warning:hover {
    background-color: #d97706;
  }

  .btn-danger {
    background-color: #ef4444;
    border: none;
  }
  .btn-danger:hover {
    background-color: #dc2626;
  }

  input.form-control {
    background-color: #ccccccff;
    border: 1px solid #475569;
    color: #737373ff;
  }

  input.form-control:focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 2px rgba(20,184,166,0.2);
  }

  .btn-outline-secondary {
    border-color: #64748b;
    color: #585858ff;
  }
  .btn-outline-secondary:hover {
    background-color: #475569;
  }
</style>
</head>
<body>

<div class="main-wrapper">
  <!-- Header -->
  <div class="page-head">
    <h2><i class="bi bi-people-fill"></i> สมาชิกทั้งหมด</h2>
  </div>

  <!-- Search bar -->
  <div class="search-bar">
    <form class="row g-2" method="get" action="supplierManagement.php"" onsubmit="return false;">
      <div class="col-md-5 col-sm-8">
        <input id="liveSearch" type="search" name="q" value="<?= htmlspecialchars($search) ?>"
               class="form-control" placeholder="ค้นหาชื่อผู้ใช้หรือสิทธิ์..." autocomplete="off">
      </div>
      <div class="col-auto">
        <button id="serverSearch" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา (Server)</button>
        <a href="supplierManagement.php" class="btn btn-outline-secondary">ล้าง</a>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle table-hover">
          <thead>
            <tr>
              <th style="width:80px">รหัส</th>
              <th>อีเมลผู้ใช้</th>
              <th style="width:160px">สิทธิ์</th>
            </tr>
          </thead>
          <tbody id="memberTbody">
            <?php while($row = $result->fetch_assoc()): ?>
              <tr data-username="<?= htmlspecialchars(strtolower($row['email'])) ?>" data-role="<?= htmlspecialchars(strtolower($row['role'])) ?>">
                <td><?= (int)$row['user_id'] ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                
              </tr>
            <?php endwhile; ?>
            <tr id="noResults" class="d-none">
              <td colspan="4" class="text-center text-muted">ไม่พบผลลัพธ์</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ลบสมาชิก (SweetAlert)
function confirmDelete(id) {
  Swal.fire({
    title: 'ยืนยันการลบ?',
    text: 'คุณแน่ใจหรือไม่ว่าต้องการลบสมาชิกนี้?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = 'delete_member.php?id=' + encodeURIComponent(id);
    }
  });
}

// Debounce helper
function debounce(fn, delay){
  let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), delay); };
}

// Live search filter (Client-side)
(function(){
  const input = document.getElementById('liveSearch');
  const tbody = document.getElementById('memberTbody');
  const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.id);
  const noResults = document.getElementById('noResults');

  function filter(value){
    const q = (value||'').trim().toLowerCase();
    let visible = 0;
    rows.forEach(r => {
      const username = r.getAttribute('data-username') || '';
      const role = r.getAttribute('data-role') || '';
      const match = q === '' || username.includes(q) || role.includes(q);
      r.style.display = match ? '' : 'none';
      if(match) visible++;
    });
    noResults.classList.toggle('d-none', visible > 0);
  }

  const debouncedFilter = debounce(function(e){ filter(e.target.value); }, 200);
  input.addEventListener('input', debouncedFilter);

  // Server search button
  document.getElementById('serverSearch').addEventListener('click', function(){
    const q = encodeURIComponent(input.value || '');
    window.location = 'supplierManagement.php' + (q ? '?q=' + q : '');
  });
})();
</script>

<?php
// cleanup
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($result) && $result instanceof mysqli_result) { $result->free(); }
$conn->close();
?>
</body>
</html>
