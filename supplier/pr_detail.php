<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";


if (!isset($_GET['pr_no'])) {
    echo "<script>alert('ไม่พบข้อมูลใบขอซื้อ'); window.location='view_pr.php';</script>";
    exit();
}

$pr_no = $_GET['pr_no'];

// ดึงข้อมูลหัวใบขอซื้อ
$sql_pr = "
    SELECT pr.pr_no, pr.request_date, pr.need_by_date, pr.status,
           e.full_name AS requester, d.name AS department_name
    FROM purchase_requisitions pr
    JOIN employees e ON pr.requested_by = e.employee_id
    JOIN departments d ON e.department_id = d.department_id
    WHERE pr.pr_no = ?
";
$stmt = $conn->prepare($sql_pr);
$stmt->bind_param("s", $pr_no);
$stmt->execute();
$pr = $stmt->get_result()->fetch_assoc();

if (!$pr) {
    echo "<script>alert('ไม่พบข้อมูลใบขอซื้อ'); window.location='view_pr.php';</script>";
    exit();
}

// ดึงรายละเอียดสินค้าในใบขอซื้อ
$sql_items = "
    SELECT pi.product_id, p.product_name, pi.quantity, pi.uom, pi.need_by_date, pi.spec_text
    FROM pr_items pi
    JOIN products p ON pi.product_id = p.product_id
    WHERE pi.pr_no = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("s", $pr_no);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<style>
.page-container {
  margin-left: 260px;
  padding: 40px 30px;
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  min-height: 100vh;
  color: #e2e8f0;
  font-family: 'Prompt', sans-serif;
}

h2 {
  color: #34d399;
  font-weight: 600;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card {
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}

.card h4 {
  color: #22d3ee;
  font-weight: 600;
  margin-bottom: 15px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
  gap: 10px;
}

.info-item {
  padding: 8px 0;
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.table thead {
  background: linear-gradient(90deg,#047857,#10b981);
  color: #fff;
}

.table th, .table td {
  padding: 12px 14px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table tbody tr:hover {
  background-color: rgba(16,185,129,0.1);
}

.btn {
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #10b981;
  color: #fff;
}

.btn-primary:hover {
  background-color: #34d399;
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(16,185,129,0.3);
}

.btn-secondary {
  background-color: #475569;
  color: #fff;
}

.btn-secondary:hover {
  background-color: #64748b;
}
</style>

<div class="page-container">
  <h2><i class="bi bi-file-earmark-text"></i> รายละเอียดใบขอซื้อ (PR)</h2>

  <div class="card">
    <h4>ข้อมูลใบขอซื้อ</h4>
    <div class="info-grid">
      <div class="info-item"><strong>เลขที่ใบขอซื้อ:</strong> <?= htmlspecialchars($pr['pr_no']) ?></div>
      <div class="info-item"><strong>แผนก:</strong> <?= htmlspecialchars($pr['department_name']) ?></div>
      <div class="info-item"><strong>ผู้ขอซื้อ:</strong> <?= htmlspecialchars($pr['requester']) ?></div>
      <div class="info-item"><strong>วันที่ขอซื้อ:</strong> <?= htmlspecialchars($pr['request_date']) ?></div>
      <div class="info-item"><strong>วันที่ต้องการใช้:</strong> <?= htmlspecialchars($pr['need_by_date']) ?></div>
      <div class="info-item"><strong>สถานะ:</strong> 
        <span class="badge bg-success"><?= htmlspecialchars($pr['status']) ?></span>
      </div>
    </div>
  </div>

  <div class="card">
    <h4>รายการสินค้าในใบขอซื้อ</h4>
    <table class="table">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>ชื่อสินค้า</th>
          <th>รายละเอียดเพิ่มเติม</th>
          <th>จำนวน</th>
          <th>หน่วย</th>
          <th>วันที่ต้องการใช้</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i = 1;
        while ($item = $result_items->fetch_assoc()): 
        ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($item['product_name']) ?></td>
          <td><?= htmlspecialchars($item['spec_text'] ?? '-') ?></td>
          <td><?= htmlspecialchars($item['quantity']) ?></td>
          <td><?= htmlspecialchars($item['uom']) ?></td>
          <td><?= htmlspecialchars($item['need_by_date']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex; justify-content:space-between; margin-top:30px;">
    <a href="view_pr.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
    <a href="quote_create.php?pr_no=<?= urlencode($pr_no) ?>" class="btn btn-primary">
      <i class="bi bi-pencil-square"></i> เสนอราคา
    </a>
  </div>
</div>

</body>
</html>
