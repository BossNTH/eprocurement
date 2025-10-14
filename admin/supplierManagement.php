<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}
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
  <title>สมาชิกทั้งหมด</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">สมาชิกทั้งหมด</h2>
      <a href="add_member.php" class="btn btn-success">เพิ่มสมาชิก</a>
    </div>

    <form class="row g-2 mb-3" method="get" action="member_list.php" onsubmit="return false;">
      <div class="col-md-5 col-sm-8">
        <input id="liveSearch" type="search" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="ค้นหาชื่อผู้ใช้หรือสิทธิ์..." autocomplete="off">
      </div>
      <div class="col-auto">
        <button id="serverSearch" class="btn btn-primary">ค้นหา (เซิร์ฟเวอร์)</button>
        <a href="member_list.php" class="btn btn-outline-secondary">ล้าง</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:80px">รหัส</th>
            <th>ชื่อผู้ใช้ (อีเมล)</th>
            <th style="width:160px">สิทธิ์</th>
            <th style="width:140px">การจัดการ</th>
          </tr>
        </thead>
        <tbody id="memberTbody">
          <?php while($row = $result->fetch_assoc()): ?>
            <tr data-username="<?= htmlspecialchars(strtolower($row['email'])) ?>" data-role="<?= htmlspecialchars(strtolower($row['role'])) ?>">
              <td><?= (int)$row['user_id'] ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['role']) ?></td>
              <td>
                <a href="edit_member.php?user_id=<?= (int)$row['user_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                <a href="#" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= (int)$row['user_id'] ?>)">ลบ</a>
              </td>
            </tr>
          <?php endwhile; ?>
          <tr id="noResults" class="d-none">
            <td colspan="4" class="text-center text-muted">ไม่พบผลลัพธ์</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function confirmDelete(id){
      if(confirm('ยืนยันการลบสมาชิก ID=' + id + ' ?')){
        // ส่งไปไฟล์ลบ (คุณต้องสร้าง delete_member.php)
        window.location = 'delete_member.php?id=' + encodeURIComponent(id);
      }
    }
    // Debounce helper
    function debounce(fn, delay){
      let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), delay); };
    }

    // Live client-side filter
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
          const match = q === '' || username.indexOf(q) !== -1 || role.indexOf(q) !== -1;
          r.style.display = match ? '' : 'none';
          if(match) visible++;
        });
        noResults.classList.toggle('d-none', visible > 0);
      }

      const debouncedFilter = debounce(function(e){ filter(e.target.value); }, 200);
      input.addEventListener('input', debouncedFilter);

      // Server-search button falls back to reloading with q param
      document.getElementById('serverSearch').addEventListener('click', function(){
        const q = encodeURIComponent(input.value || '');
        window.location = 'member_list.php' + (q ? '?q='+q : '');
      });
    })();
  </script>

  <?php
  // cleanup
  if (isset($stmt) && $stmt instanceof mysqli_stmt) {
      $stmt->close();
  }
  if (isset($result) && $result instanceof mysqli_result) {
      $result->free();
  }
  $conn->close();
  ?>
</body>
</html>
