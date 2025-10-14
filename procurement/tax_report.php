<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

/* ====== รับค่าปี (ค่าเริ่มต้น = ปีปัจจุบัน) ====== */
$year = (int)($_GET['year'] ?? date('Y'));

/* ====== ดึงข้อมูลภาษีซื้อรายเดือน ====== */
$sql = "
  SELECT 
    MONTH(po_date) AS month,
    SUM(vat_total) AS vat_sum,
    SUM(grand_total) AS grand_sum
  FROM purchase_orders
  WHERE YEAR(po_date) = ?
    AND status IN ('APPROVED','RECEIVED','COMPLETED','DRAFT','PENDING_APPROVAL')
  GROUP BY MONTH(po_date)
  ORDER BY MONTH(po_date)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$vat_values = [];
$grand_values = [];
while ($row = $result->fetch_assoc()) {
  $months[] = $row['month'];
  $vat_values[] = $row['vat_sum'];
  $grand_values[] = $row['grand_sum'];
}

/* ====== ดึงรายละเอียดใบสั่งซื้อ ====== */
$stmt2 = $conn->prepare("
  SELECT p.po_no, p.po_date, s.supplier_name, p.subtotal_before_vat, p.vat_total, p.grand_total, p.status
  FROM purchase_orders p
  LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
  WHERE YEAR(p.po_date) = ?
  ORDER BY p.po_date DESC
");
$stmt2->bind_param("i", $year);
$stmt2->execute();
$list = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานภาษีซื้อ | ฝ่ายจัดซื้อ</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
  background-color:#0f172a;
  color:#e2e8f0;
  font-family:'Prompt',sans-serif;
  padding-left:260px;
}
.container-fluid { padding:2rem; }
h2 { color:#5eead4; }
.card {
  background:#1e293b;
  border:1px solid rgba(20,184,166,0.3);
  border-radius:16px;
  box-shadow:0 2px 10px rgba(0,0,0,.3);
}
.table thead {
  background:#334155;
  color:#a5f3fc;
}
.table tbody tr:hover { background-color:rgba(20,184,166,0.1); }
.select-year {
  max-width: 160px;
  background-color:#1e293b;
  color:#e2e8f0;
  border:1px solid rgba(20,184,166,0.4);
  border-radius:8px;
}
.chart-card {
  background:#1e293b;
  border:1px solid rgba(14,165,233,0.4);
  border-radius:16px;
  padding:1rem;
}
.badge-status {
  font-size:.8rem;
  padding:4px 10px;
  border-radius:8px;
}
</style>
</head>
<body>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>รายงานภาษีซื้อ (Input VAT)</h2>
    <form method="get" class="d-flex align-items-center gap-2">
      <label for="year" class="fw-semibold">เลือกปี:</label>
      <select id="year" name="year" class="form-select select-year" onchange="this.form.submit()">
        <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
          <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y+543 ?></option>
        <?php endfor; ?>
      </select>
    </form>
  </div>

  <!-- ======= กราฟรายเดือน ======= -->
  <div class="chart-card mb-4">
    <canvas id="vatChart" height="100"></canvas>
  </div>

  <!-- ======= ตารางรายละเอียด ======= -->
  <div class="card p-4">
    <h5 class="text-info mb-3"><i class="bi bi-table me-2"></i>รายละเอียดใบสั่งซื้อในปี <?= $year+543 ?></h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr class="text-center">
            <th>PO No.</th>
            <th>วันที่สั่งซื้อ</th>
            <th>ผู้จำหน่าย</th>
            <th>มูลค่าสินค้า</th>
            <th>VAT</th>
            <th>รวมสุทธิ</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php if($list->num_rows == 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีข้อมูลในปีนี้</td></tr>
          <?php else: while($r=$list->fetch_assoc()): ?>
            <tr>
              <td class="text-center"><?= htmlspecialchars($r['po_no']) ?></td>
              <td class="text-center"><?= date('d/m/Y', strtotime($r['po_date'])) ?></td>
              <td><?= htmlspecialchars($r['supplier_name']) ?></td>
              <td class="text-end"><?= number_format($r['subtotal_before_vat'],2) ?></td>
              <td class="text-end text-warning"><?= number_format($r['vat_total'],2) ?></td>
              <td class="text-end text-success"><?= number_format($r['grand_total'],2) ?></td>
              <td class="text-center">
                <span class="badge-status 
                  <?= $r['status']=='APPROVED'?'bg-success':
                      ($r['status']=='DRAFT'?'bg-secondary':
                      ($r['status']=='REJECTED'?'bg-danger':'bg-info text-dark')) ?>">
                  <?= htmlspecialchars($r['status']) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const ctx = document.getElementById('vatChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($m)=>"เดือน ".(int)$m, $months)) ?>,
    datasets: [
      {
        label: 'ภาษีมูลค่าเพิ่ม (VAT)',
        data: <?= json_encode($vat_values) ?>,
        backgroundColor: 'rgba(20,184,166,0.7)',
        borderColor: 'rgba(45,212,191,1)',
        borderWidth: 1
      },
      {
        label: 'มูลค่ารวม (รวม VAT)',
        data: <?= json_encode($grand_values) ?>,
        backgroundColor: 'rgba(59,130,246,0.6)',
        borderColor: 'rgba(147,197,253,1)',
        borderWidth: 1
      }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { labels:{ color:'#e2e8f0' } } },
    scales: {
      x: { ticks:{ color:'#94a3b8' }, grid:{ color:'rgba(255,255,255,0.05)' } },
      y: { ticks:{ color:'#94a3b8' }, grid:{ color:'rgba(255,255,255,0.05)' } }
    }
  }
});
</script>

</body>
</html>
