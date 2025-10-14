<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

// ดึงข้อมูลสรุปจากฐานข้อมูล
$supplier_id = $_SESSION['supplier_id'] ?? 0;

// จำนวนใบขอซื้อที่เปิดให้เสนอราคา
$sql_pr = "SELECT COUNT(*) AS total_pr FROM purchase_requisitions WHERE status = 'APPROVE'";
$pr_count = $conn->query($sql_pr)->fetch_assoc()['total_pr'] ?? 0;

// จำนวนใบเสนอราคาที่ส่งแล้ว
$sql_quote = "SELECT COUNT(*) AS total_quotes FROM purchase_quotes WHERE supplier_id = $supplier_id";
$quote_count = $conn->query($sql_quote)->fetch_assoc()['total_quotes'] ?? 0;

// จำนวนใบสั่งซื้อที่อนุมัติแล้ว
$sql_po = "SELECT COUNT(*) AS total_po FROM purchase_orders WHERE supplier_id = $supplier_id AND status = 'APPROVED'";
$po_count = $conn->query($sql_po)->fetch_assoc()['total_po'] ?? 0;

// มูลค่ารวมใบสั่งซื้อทั้งหมด
$sql_total = "SELECT SUM(grand_total) AS total_value FROM purchase_orders WHERE supplier_id = $supplier_id AND status = 'APPROVED'";
$total_value = $conn->query($sql_total)->fetch_assoc()['total_value'] ?? 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แดชบอร์ดซัพพลายเออร์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  color: #e2e8f0;
  font-family: 'Prompt', sans-serif;
  margin: 0;
}
.page-container {
  margin-left: 260px;
  padding: 40px 30px;
  min-height: 100vh;
}

/* ===== Dashboard Title ===== */
h2 {
  color: #22d3ee;
  font-weight: 600;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ===== Summary Cards ===== */
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
  gap: 20px;
}

.card-box {
  border-radius: 14px;
  padding: 24px;
  color: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.4);
  backdrop-filter: blur(8px);
  transition: transform 0.2s, box-shadow 0.3s;
}
.card-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(16,185,129,0.4);
}
.card-box h5 {
  font-weight: 500;
  margin-bottom: 8px;
}
.card-box h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 4px;
}

/* Colors per card */
.card-pr { background: linear-gradient(135deg,#10b981,#064e3b); }
.card-quote { background: linear-gradient(135deg,#14b8a6,#0f766e); }
.card-po { background: linear-gradient(135deg,#22d3ee,#0369a1); }
.card-value { background: linear-gradient(135deg,#06b6d4,#155e75); }

/* ===== Table Section ===== */
.section-title {
  color: #a7f3d0;
  font-size: 1.2rem;
  margin-top: 40px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.table-container {
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.4);
  overflow: hidden;
}
.table {
  width: 100%;
  border-collapse: collapse;
  color: #e2e8f0;
}
.table thead {
  background: linear-gradient(90deg, #047857, #10b981);
  color: #fff;
}
.table th, .table td {
  padding: 12px 14px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.table tbody tr:hover {
  background-color: rgba(20,184,166,0.1);
}
.text-muted {
  color: #94a3b8 !important;
}
</style>
</head>

<body>
<div class="page-container">
  <h2><i class="bi bi-speedometer2 text-success"></i> แดชบอร์ดซัพพลายเออร์</h2>

  <!-- สรุปข้อมูล -->
  <div class="dashboard-cards">
    <div class="card-box card-pr">
      <h5>รายการขอซื้อที่เปิด</h5>
      <h2><?= number_format($pr_count) ?></h2>
      <p class="text-light small">ใบขอซื้อที่เปิดให้เสนอราคา</p>
    </div>

    <div class="card-box card-quote">
      <h5>ใบเสนอราคาของฉัน</h5>
      <h2><?= number_format($quote_count) ?></h2>
      <p class="text-light small">ทั้งหมดที่เคยส่งเสนอราคา</p>
    </div>

    <div class="card-box card-po">
      <h5>ใบสั่งซื้อที่ได้รับ</h5>
      <h2><?= number_format($po_count) ?></h2>
      <p class="text-light small">ใบสั่งซื้อที่อนุมัติแล้ว</p>
    </div>

    <div class="card-box card-value">
      <h5>มูลค่ารวมใบสั่งซื้อ</h5>
      <h2><?= number_format($total_value,2) ?> ฿</h2>
      <p class="text-light small">รวมมูลค่าใบสั่งซื้อทั้งหมด</p>
    </div>
  </div>

  <hr style="margin:40px 0; border-color:#334155;">

  <!-- ตารางล่าสุด -->
  <h4 class="section-title"><i class="bi bi-box-seam"></i> ใบสั่งซื้อล่าสุดของคุณ</h4>

  <div class="table-container">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>เลขที่ใบสั่งซื้อ</th>
          <th>วันที่ออก</th>
          <th>มูลค่า (บาท)</th>
          <th>สถานะ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql_recent = "SELECT po_no, po_date, grand_total, status
                       FROM purchase_orders 
                       WHERE supplier_id = $supplier_id
                       ORDER BY created_at DESC
                       LIMIT 5";
        $result = $conn->query($sql_recent);
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['po_no']) ?></strong></td>
              <td><?= htmlspecialchars($row['po_date']) ?></td>
              <td class="text-end"><?= number_format($row['grand_total'],2) ?></td>
              <td>
                <?php
                  $statusColor = [
                    'APPROVED' => 'success',
                    'ISSUED' => 'info',
                    'CANCELLED' => 'danger'
                  ][$row['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $statusColor ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4" class="text-center text-muted py-3">ไม่มีใบสั่งซื้อในระบบ</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
