<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/manager_header.php";

// ตรวจสอบว่าได้รับ po_no มาหรือไม่
if (!isset($_GET['po_no'])) {
    echo "<script>alert('ไม่พบหมายเลขใบสั่งซื้อ'); window.location='purchase_approval.php';</script>";
    exit();
}

$po_no = $_GET['po_no'];

// ดึงข้อมูลหัวใบสั่งซื้อ
$sql = "
    SELECT po.po_no, po.po_date, po.status, po.subtotal_before_vat, po.vat_total, po.grand_total,
           po.approved_at, s.supplier_name, s.address AS supplier_address, s.phone AS supplier_phone, s.email AS supplier_email,
           e.full_name AS buyer_name
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.supplier_id
    JOIN employees e ON po.buyer_employee_id = e.employee_id
    WHERE po.po_no = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $po_no);
$stmt->execute();
$po = $stmt->get_result()->fetch_assoc();

if (!$po) {
    echo "<script>alert('ไม่พบข้อมูลใบสั่งซื้อในระบบ'); window.location='purchase_approval.php';</script>";
    exit();
}

// ดึงรายการสินค้าในใบสั่งซื้อ
$sql_items = "
    SELECT pi.product_id, p.product_name, pi.quantity, pi.unit_price, pi.discount_pct, pi.vat_rate, pi.uom
    FROM po_items pi
    JOIN products p ON pi.product_id = p.product_id
    WHERE pi.po_no = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("s", $po_no);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายละเอียดใบสั่งซื้อ | ฝ่ายจัดซื้อ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
  background: linear-gradient(180deg,#0f172a 0%,#1e293b 100%);
  color:#e2e8f0;
  font-family:'Prompt',sans-serif;
  padding-left:260px;
}
.container-fluid {
  padding:2.5rem;
}
h2 {
  color:#22d3ee;
  font-weight:600;
  margin-bottom:1.5rem;
}
.card {
  background:rgba(255,255,255,0.05);
  border-radius:14px;
  padding:25px;
  margin-bottom:25px;
  box-shadow:0 4px 15px rgba(0,0,0,0.4);
  color: #cacacaff;
}
.card h5 {
  color:#fff;
  font-weight:500;
  margin-bottom:1rem;
}
.info-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:10px;
  margin-bottom:10px;
}
.table {
  width:100%;
  border-collapse:collapse;
  color:#e2e8f0;
}
.table thead {
  background:linear-gradient(90deg,#0d9488,#14b8a6);
  color:#fff;
}
.table th,.table td {
  padding:12px 14px;
  text-align:center;
  border-bottom:1px solid rgba(255,255,255,0.1);
}
.table tbody tr:hover {
  background-color:rgba(20,184,166,0.1);
}
.summary-box {
  background:rgba(255,255,255,0.05);
  border-radius:12px;
  padding:1rem;
  max-width:400px;
  margin-left:auto;
}
.summary-box .d-flex {
  justify-content:space-between;
  padding:4px 0;
}
.btn-back {
  background:#0ea5e9;
  color:#fff;
  border:none;
  padding:8px 16px;
  border-radius:8px;
  text-decoration:none;
  transition:all 0.2s;
}
.btn-back:hover {
  background:#38bdf8;
  transform:translateY(-2px);
}
</style>
</head>

<body>
<div class="container-fluid">
  <h2><i class="bi bi-file-earmark-text"></i> รายละเอียดใบสั่งซื้อ (Purchase Order)</h2>

  <!-- ข้อมูลใบสั่งซื้อ -->
  <div class="card">
    <h5><i class="bi bi-info-circle"></i> ข้อมูลใบสั่งซื้อ</h5>
    <div class="info-grid">
      <div><strong>เลขที่ใบสั่งซื้อ:</strong> <?= htmlspecialchars($po['po_no']) ?></div>
      <div><strong>วันที่ออก:</strong> <?= htmlspecialchars($po['po_date']) ?></div>
      <div><strong>สถานะ:</strong> 
        <span class="badge bg-<?= 
          $po['status']=='APPROVED'?'success':
          ($po['status']=='REJECTED'?'danger':
          ($po['status']=='ISSUED'?'info':'secondary')) ?>">
          <?= htmlspecialchars($po['status']) ?>
        </span>
      </div>
      <div><strong>ผู้จัดซื้อ:</strong> <?= htmlspecialchars($po['buyer_name']) ?></div>
      <div><strong>วันที่อนุมัติ:</strong> <?= htmlspecialchars($po['approved_at'] ?? '-') ?></div>
    </div>
  </div>

  <!-- ข้อมูลผู้จำหน่าย -->
  <div class="card">
    <h5><i class="bi bi-building"></i> ข้อมูลผู้จำหน่าย (Supplier)</h5>
    <div class="info-grid">
      <div><strong>ชื่อผู้จำหน่าย:</strong> <?= htmlspecialchars($po['supplier_name']) ?></div>
      <div><strong>เบอร์โทร:</strong> <?= htmlspecialchars($po['supplier_phone'] ?? '-') ?></div>
      <div><strong>อีเมล:</strong> <?= htmlspecialchars($po['supplier_email'] ?? '-') ?></div>
      <div style="grid-column:1/-1;">
        <strong>ที่อยู่:</strong> <?= htmlspecialchars($po['supplier_address'] ?? '-') ?>
      </div>
    </div>
  </div>

  <!-- รายการสินค้า -->
  <div class="card">
    <h5><i class="bi bi-box-seam"></i> รายการสินค้า</h5>
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>ชื่อสินค้า</th>
          <th>จำนวน</th>
          <th>หน่วย</th>
          <th>ราคาต่อหน่วย</th>
          <th>ส่วนลด (%)</th>
          <th>ภาษี (%)</th>
          <th>รวม (บาท)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i=1; $sum=0;
        while($it = $result_items->fetch_assoc()):
          $total = $it['quantity'] * $it['unit_price'] * (1 - $it['discount_pct']/100);
          $sum += $total;
        ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($it['product_name']) ?></td>
          <td><?= number_format($it['quantity'],2) ?></td>
          <td><?= htmlspecialchars($it['uom']) ?></td>
          <td><?= number_format($it['unit_price'],2) ?></td>
          <td><?= number_format($it['discount_pct'],2) ?></td>
          <td><?= number_format($it['vat_rate'],2) ?></td>
          <td><?= number_format($total,2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- สรุปรวม -->
    <div class="summary-box mt-4">
      <div class="d-flex"><span>ยอดก่อนภาษี:</span><span><?= number_format($po['subtotal_before_vat'],2) ?> ฿</span></div>
      <div class="d-flex"><span>ภาษีมูลค่าเพิ่ม:</span><span><?= number_format($po['vat_total'],2) ?> ฿</span></div>
      <hr class="border-info">
      <div class="d-flex fw-bold"><span>ยอดรวมสุทธิ:</span><span><?= number_format($po['grand_total'],2) ?> ฿</span></div>
    </div>
  </div>

  <!-- ปุ่มย้อนกลับ -->
  <div class="text-end">
    <a href="purchase_approval.php" class="btn-back"><i class="bi bi-arrow-left"></i> กลับ</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
