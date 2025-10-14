<?php
session_start();
require_once("../connect.php");



$page_title = "จัดการพนักงาน";
$active_menu = "employees";
require __DIR__ . '/partials/admin_header.php';

// ดึงข้อมูลพนักงาน
$sql = "SELECT e.employee_id, e.full_name, e.email, e.phone, e.status, d.name AS department
        FROM employees e
        JOIN departments d ON e.department_id = d.department_id
        ORDER BY e.employee_id DESC";
$result = $conn->query($sql);
?>

<style>
  body {
    background: linear-gradient(180deg, #dcdcdcff, #f9fafb);
    color: #1e293b;
    font-family: "Prompt", sans-serif;
  }

  .page-container {
    max-width: 1200px;
    margin: 0 ;
    padding: 1.5rem;
  }

  /* ===== หัวข้อหน้า ===== */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .page-header h2 {
    font-weight: 700;
    color: #000000ff;
  }

  .btn-add {
    background: #3c369eff;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: .2s;
  }

  .btn-add:hover {
    background: #11165eff;
  }

  /* ===== ส่วนค้นหา ===== */
  .filter-box {
    background: #ffffff;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .filter-box input,
  .filter-box select {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: .95rem;
    outline: none;
    transition: 0.2s;
  }

  .filter-box input:focus,
  .filter-box select:focus {
    border-color: #3c369eff;
    box-shadow: 0 0 0 2px rgba(13,148,136,0.2);
  }

  /* ===== ตาราง ===== */
  .card-table {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  table thead {
    background: #3c369eff;
    color: #ffffff;
  }

  table th, table td {
    padding: 14px 16px;
    text-align: left;
  }

  table tbody tr:nth-child(even) {
    background: #f9fafb;
  }

  table tbody tr:hover {
    background: #635bff18;
  }

  /* ===== สถานะ ===== */
  .status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: .85rem;
    font-weight: 600;
    color: #065f46;
    background: #a7f3d0;
  }

  /* ===== ปุ่มจัดการ ===== */
  .action-btns {
    display: flex;
    gap: 8px;
  }

  .btn-edit, .btn-del {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: .9rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #fff;
    text-decoration: none;
    transition: .2s;
  }

  .btn-edit {
    background: #f59e0b;
  }
  .btn-edit:hover {
    background: #b45309;
  }

  .btn-del {
    background: #dc2626;
  }
  .btn-del:hover {
    background: #991b1b;
  }

  @media (max-width: 768px) {
    .page-container { padding: 1rem; }
    .filter-box { flex-direction: column; align-items: stretch; }
    table th, table td { font-size: .9rem; }
  }
</style>

<div class="page-container">
  <!-- หัวข้อหน้า -->
  <div class="page-header">
    <h2>จัดการพนักงาน</h2>
    <a href="employee_add.php" class="btn-add">
      <i class="bi bi-person-plus"></i> เพิ่มพนักงาน
    </a>
  </div>

  <!-- ฟิลเตอร์และค้นหา -->
  <div class="filter-box">
    <input type="text" id="searchInput" placeholder="พิมพ์ชื่อ, อีเมล หรือเบอร์โทร..." oninput="filterEmployees()">
    <select id="statusFilter" onchange="filterEmployees()">
      <option value="">สถานะทั้งหมด</option>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select>
    <select id="deptFilter" onchange="filterEmployees()">
      <option value="">แผนกทั้งหมด</option>
      <?php
      $deptRes = $conn->query("SELECT * FROM departments ORDER BY name");
      while($d = $deptRes->fetch_assoc()){
        echo "<option value='".htmlspecialchars($d['name'])."'>".htmlspecialchars($d['name'])."</option>";
      }
      ?>
    </select>
  </div>

  <!-- ตารางพนักงาน -->
  <div class="card-table">
    <table id="employeeTable">
      <thead>
        <tr>
          <th>รหัส</th>
          <th>ชื่อ - สกุล</th>
          <th>เบอร์โทร</th>
          <th>อีเมล</th>
          <th>แผนก</th>
          <th>สถานะ</th>
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['employee_id'] ?></td>
              <td><?= htmlspecialchars($row['full_name']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['department']) ?></td>
              <td><span class="status-badge"><?= ucfirst($row['status']) ?></span></td>
              <td>
                <div class="action-btns">
                  <a href="employee_edit.php?id=<?= $row['employee_id'] ?>" class="btn-edit">
                    <i class="bi bi-pencil-square"></i> แก้ไข
                  </a>
                  <a href="employee_delete.php?id=<?= $row['employee_id'] ?>" class="btn-del" onclick="return confirm('ต้องการลบพนักงานคนนี้หรือไม่?');">
                    <i class="bi bi-trash"></i> ลบ
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center; color:#64748b;">ไม่มีข้อมูลพนักงานในระบบ</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filterEmployees() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('statusFilter').value.toLowerCase();
  const dept = document.getElementById('deptFilter').value.toLowerCase();

  document.querySelectorAll('#employeeTable tbody tr').forEach(tr => {
    const text = tr.innerText.toLowerCase();
    const matchSearch = text.includes(q);
    const matchStatus = !status || text.includes(status);
    const matchDept = !dept || text.includes(dept);
    tr.style.display = (matchSearch && matchStatus && matchDept) ? '' : 'none';
  });
}
</script>

<?php require __DIR__ . '/partials/admin_footer.php'; ?>
