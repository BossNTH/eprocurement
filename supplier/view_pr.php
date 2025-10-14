<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

// ดึงใบขอซื้อที่สถานะ "MANAGER_APPROVED" (เปิดให้เสนอราคา)
$sql = "
    SELECT pr.pr_no, pr.request_date, pr.need_by_date, d.name AS department_name, e.full_name AS requester
    FROM purchase_requisitions pr
    JOIN employees e ON pr.requested_by = e.employee_id
    JOIN departments d ON e.department_id = d.department_id
    WHERE pr.status = 'APPROVE'
    ORDER BY pr.request_date DESC
";
$result = $conn->query($sql);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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
  margin-bottom: 30px;
  letter-spacing: .5px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.table-container {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.table thead {
  background: linear-gradient(90deg, #047857, #10b981);
  color: #fff;
}

.table th, .table td {
  padding: 12px 16px;
  text-align: center;
}

.table tbody tr {
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: all 0.2s;
}

.table tbody tr:hover {
  background-color: rgba(16,185,129,0.08);
  transform: scale(1.01);
}

.btn {
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-info {
  background-color: #10b981;
  color: white;
}

.btn-info:hover {
  background-color: #34d399;
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(16,185,129,0.3);
}

.alert-empty {
  background-color: rgba(255,255,255,0.05);
  border-left: 4px solid #22d3ee;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  color: #94a3b8;
  font-size: 1.1rem;
  margin-top: 40px;
}
</style>

<div class="page-container">
  <h2><i class="bi bi-file-earmark-text"></i> รายการขอซื้อที่เปิดให้เสนอราคา</h2>

  <div class="table-container">
    <?php if ($result->num_rows > 0): ?>
      <table class="table">
        <thead>
          <tr>
            <th>เลขที่ใบขอซื้อ</th>
            <th>แผนก</th>
            <th>ผู้ขอซื้อ</th>
            <th>วันที่ขอซื้อ</th>
            <th>วันที่ต้องการใช้</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['pr_no']) ?></strong></td>
              <td><?= htmlspecialchars($row['department_name']) ?></td>
              <td><?= htmlspecialchars($row['requester']) ?></td>
              <td><?= htmlspecialchars($row['request_date']) ?></td>
              <td><?= htmlspecialchars($row['need_by_date']) ?></td>
              <td>
                <a href="pr_detail.php?pr_no=<?= urlencode($row['pr_no']) ?>" class="btn btn-info">
                  <i class="bi bi-eye"></i> ดูรายละเอียด
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert-empty">
        <i class="bi bi-inbox"></i> ไม่มีรายการขอซื้อที่เปิดให้เสนอราคาในขณะนี้
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
