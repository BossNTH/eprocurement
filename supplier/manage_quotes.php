<?php

require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

// ตรวจสอบว่ามี supplier_id หรือไม่
if (empty($_SESSION['supplier_id'])) {
    echo "<script>alert('ไม่พบข้อมูลผู้ขาย กรุณาเข้าสู่ระบบใหม่'); window.location='../login.php';</script>";
    exit();
}

$supplier_id = $_SESSION['supplier_id'];

// ดึงรายการใบเสนอราคาทั้งหมดของ supplier นี้
$sql = "
  SELECT q.quote_no, q.pr_no, q.quote_date, q.grand_total, q.valid_until, q.status, pr.request_date
  FROM purchase_quotes q
  JOIN purchase_requisitions pr ON q.pr_no = pr.pr_no
  WHERE q.supplier_id = ?
  ORDER BY q.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
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
  color: #22d3ee;
  font-weight: 600;
  margin-bottom: 25px;
}

.table-container {
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 20px;
  backdrop-filter: blur(10px);
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
  padding: 12px 14px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table tbody tr:hover {
  background-color: rgba(16,185,129,0.1);
  transform: scale(1.01);
}

.status-badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #fff;
}
.status-badge.SUBMITTED { background-color: #10b981; }
.status-badge.EVALUATING { background-color: #fbbf24; color: #111; }
.status-badge.SELECTED { background-color: #3b82f6; }
.status-badge.NOT_SELECTED { background-color: #ef4444; }
.status-badge.CLOSED { background-color: #6b7280; }

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
  <h2><i class="bi bi-file-earmark-ruled"></i> จัดการใบเสนอราคา (Quotes)</h2>

  <div class="table-container">
    <?php if ($result->num_rows > 0): ?>
      <table class="table">
        <thead>
          <tr>
            <th>เลขที่ใบเสนอราคา</th>
            <th>เลขที่ใบขอซื้อ</th>
            <th>วันที่เสนอราคา</th>
            <th>วันที่ขอซื้อ</th>
            <th>มูลค่ารวม (บาท)</th>
            <th>วันหมดอายุ</th>
            <th>สถานะ</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
            <?php
              $statusClass = match ($row['status']) {
                'SUPPLIER_SUBMITTED' => 'SUBMITTED',
                'EVALUATING' => 'EVALUATING',
                'SELECTED' => 'SELECTED',
                'NOT_SELECTED' => 'NOT_SELECTED',
                'CLOSED' => 'CLOSED',
                default => 'SUBMITTED'
              };
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['quote_no']) ?></strong></td>
              <td><?= htmlspecialchars($row['pr_no']) ?></td>
              <td><?= htmlspecialchars($row['quote_date']) ?></td>
              <td><?= htmlspecialchars($row['request_date']) ?></td>
              <td class="text-end"><?= number_format($row['grand_total'], 2) ?></td>
              <td><?= htmlspecialchars($row['valid_until']) ?></td>
              <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              <td>
                <a href="quote_detail.php?quote_no=<?= urlencode($row['quote_no']) ?>" class="btn btn-info">
                  <i class="bi bi-eye"></i> ดูรายละเอียด
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert-empty">
        <i class="bi bi-inbox"></i> ยังไม่มีใบเสนอราคาที่คุณส่งไว้
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
