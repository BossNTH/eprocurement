<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

// ตรวจสอบการส่งค่า quote_no
if (!isset($_GET['quote_no'])) {
    echo "<script>alert('ไม่พบใบเสนอราคา'); window.location='manage_quotes.php';</script>";
    exit();
}

$quote_no = $_GET['quote_no'];
$supplier_id = $_SESSION['supplier_id'] ?? null;

if (empty($supplier_id)) {
    echo "<script>alert('ไม่พบข้อมูลผู้ขาย กรุณาเข้าสู่ระบบใหม่'); window.location='../login.php';</script>";
    exit();
}

// ดึงข้อมูลหัวใบเสนอราคา
$sql_quote = "
    SELECT q.*, pr.request_date, pr.pr_no
    FROM purchase_quotes q
    JOIN purchase_requisitions pr ON q.pr_no = pr.pr_no
    WHERE q.quote_no = ? AND q.supplier_id = ?
";
$stmt = $conn->prepare($sql_quote);
$stmt->bind_param("si", $quote_no, $supplier_id);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();

if (!$quote) {
    echo "<script>alert('ไม่พบใบเสนอราคานี้'); window.location='manage_quotes.php';</script>";
    exit();
}

// ดึงรายการสินค้าในใบเสนอราคา
$sql_items = "
    SELECT qi.*, p.product_name 
    FROM quote_items qi
    JOIN products p ON qi.product_id = p.product_id
    WHERE qi.quote_no = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("s", $quote_no);
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
  color: #22d3ee;
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
  backdrop-filter: blur(10px);
}

.card h4 {
  color: #34d399;
  font-weight: 600;
  margin-bottom: 15px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
  gap: 10px;
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
  padding: 10px 12px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table tbody tr:hover {
  background-color: rgba(16,185,129,0.1);
}

.btn-secondary {
  background-color: #475569;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  transition: all 0.2s;
  text-decoration: none;
}
.btn-secondary:hover {
  background-color: #64748b;
}

.summary-box {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.summary-box table {
  width: 350px;
  background-color: rgba(255,255,255,0.05);
  border-radius: 10px;
  overflow: hidden;
}
.summary-box th, .summary-box td {
  padding: 8px 12px;
}
.summary-box th {
  text-align: left;
  color: #94a3b8;
}
.summary-box td {
  text-align: right;
  color: #e2e8f0;
}
</style>

<div class="page-container">
  <h2><i class="bi bi-file-earmark-ruled"></i> รายละเอียดใบเสนอราคา (Quote)</h2>

  <div class="card">
    <h4>ข้อมูลใบเสนอราคา</h4>
    <div class="info-grid">
      <div><strong>เลขที่ใบเสนอราคา:</strong> <?= htmlspecialchars($quote['quote_no']) ?></div>
      <div><strong>เลขที่ใบขอซื้อ:</strong> <?= htmlspecialchars($quote['pr_no']) ?></div>
      <div><strong>วันที่เสนอราคา:</strong> <?= htmlspecialchars($quote['quote_date']) ?></div>
      <div><strong>วันหมดอายุ:</strong> <?= htmlspecialchars($quote['valid_until']) ?></div>
      <div><strong>สถานะ:</strong> 
        <span class="badge bg-info"><?= htmlspecialchars($quote['status']) ?></span>
      </div>
    </div>
  </div>

  <div class="card">
    <h4>รายการสินค้า</h4>
    <table class="table">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>ชื่อสินค้า</th>
          <th>จำนวน</th>
          <th>หน่วย</th>
          <th>ราคาต่อหน่วย</th>
          <th>ส่วนลด (%)</th>
          <th>VAT (%)</th>
          <th>ระยะเวลาจัดส่ง (วัน)</th>
          <th>รวมสุทธิ (บาท)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i = 1;
        $subtotal = 0;
        while($item = $result_items->fetch_assoc()): 
          $net = ($item['quantity'] * $item['unit_price']) * (1 - $item['discount_pct'] / 100);
          $vat = $net * ($item['vat_rate'] / 100);
          $total = $net + $vat;
          $subtotal += $total;
        ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($item['product_name']) ?></td>
          <td><?= htmlspecialchars($item['quantity']) ?></td>
          <td><?= htmlspecialchars($item['uom']) ?></td>
          <td><?= number_format($item['unit_price'], 2) ?></td>
          <td><?= htmlspecialchars($item['discount_pct']) ?></td>
          <td><?= htmlspecialchars($item['vat_rate']) ?></td>
          <td><?= htmlspecialchars($item['lead_time_days']) ?></td>
          <td><?= number_format($total, 2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <div class="summary-box">
      <table>
        <tr><th>ยอดก่อน VAT:</th><td><?= number_format($quote['subtotal_before_vat'], 2) ?></td></tr>
        <tr><th>VAT:</th><td><?= number_format($quote['vat_total'], 2) ?></td></tr>
        <tr><th><strong>ยอดรวมสุทธิ:</strong></th><td><strong><?= number_format($quote['grand_total'], 2) ?> ฿</strong></td></tr>
      </table>
    </div>
  </div>

  <div class="card">
    <h4>เงื่อนไขเพิ่มเติม</h4>
    <div class="info-grid">
      <div><strong>เงื่อนไขการชำระเงิน:</strong> <?= htmlspecialchars($quote['payment_terms']) ?></div>
      <div><strong>เงื่อนไขการส่งมอบ:</strong> <?= htmlspecialchars($quote['delivery_terms']) ?></div>
    </div>
  </div>

  <div style="text-align:right;">
    <a href="manage_quotes.php" class="btn-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
  </div>
</div>

</body>
</html>
