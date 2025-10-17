<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

$supplier_id = $_SESSION['supplier_id'];

// ดึงใบสั่งซื้อของ supplier นี้
$sql = "
  SELECT po.po_no, po.po_date, po.grand_total, po.status, s.supplier_name,
         e.full_name AS buyer_name, po.approved_at, po.issued_at
  FROM purchase_orders po
  JOIN suppliers s ON po.supplier_id = s.supplier_id
  JOIN employees e ON po.buyer_employee_id = e.employee_id
  WHERE po.supplier_id = ?
  ORDER BY po.po_date DESC
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
  margin-left: 260px;      /* ✅ เท่ากับ sidebar width */
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
  background: linear-gradient(90deg, #0369a1, #0ea5e9);
  color: #fff;
}

.table th, .table td {
  padding: 12px 14px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table tbody tr:hover {
  background-color: rgba(14,165,233,0.1);
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
.status-badge.APPROVED { background-color: #10b981; }
.status-badge.ISSUED { background-color: #3b82f6; }
.status-badge.CANCELLED { background-color: #ef4444; }
.status-badge.PENDING_APPROVAL { background-color: #facc15; color: #111; }

.btn-info {
  background-color: #0ea5e9;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
}
.btn-info:hover {
  background-color: #38bdf8;
  transform: translateY(-2px);
}

.alert-empty {
  background-color: rgba(255,255,255,0.05);
  border-left: 4px solid #0ea5e9;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  color: #94a3b8;
  font-size: 1.1rem;
  margin-top: 40px;
}

.modal-content {
  background-color: #1e293b;
  color: #e2e8f0;
  border-radius: 12px;
}
.modal-header, .modal-footer {
  border-color: #334155;
}


</style>



<div class="page-container">
  <h2><i class="bi bi-receipt"></i> ใบสั่งซื้อของคุณ (Purchase Orders)</h2>

  <div class="table-container">
    <?php if ($result->num_rows > 0): ?>
      <table class="table">
        <thead>
          <tr>
            <th>เลขที่ใบสั่งซื้อ</th>
            <th>วันที่ออก</th>
            <th>ผู้จัดซื้อ</th>
            <th>มูลค่ารวม (บาท)</th>
            <th>สถานะ</th>
            <th>อนุมัติเมื่อ</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['po_no']) ?></strong></td>
              <td><?= htmlspecialchars($row['po_date']) ?></td>
              <td><?= htmlspecialchars($row['buyer_name']) ?></td>
              <td class="text-end"><?= number_format($row['grand_total'], 2) ?></td>
              <td><span class="status-badge <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              <td><?= htmlspecialchars($row['approved_at'] ?: '-') ?></td>
              <td>
                <button class="btn btn-info btn-sm view-po" data-id="<?= htmlspecialchars($row['po_no']) ?>">
                  <i class="bi bi-eye"></i> ดูรายละเอียด
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert-empty"><i class="bi bi-inbox"></i> ยังไม่มีใบสั่งซื้อในระบบ</div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="poModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-receipt"></i> รายละเอียดใบสั่งซื้อ</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="poDetail">
        <div class="text-center py-5 text-muted">กำลังโหลดข้อมูล...</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){
  $(".view-po").click(function(){
    const poNo = $(this).data("id");
    $("#poDetail").html('<div class="text-center py-5 text-muted">กำลังโหลดข้อมูล...</div>');
    $("#poModal").modal("show");
    $.get("po_detail_modal.php", { po_no: poNo }, function(data){
      $("#poDetail").html(data);
    });
  });
});
</script>

</body>
</html>
