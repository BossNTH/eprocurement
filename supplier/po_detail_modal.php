<?php
require_once "../connect.php";
session_start();

if (!isset($_GET['po_no'])) {
    echo "<div class='text-danger text-center'>ไม่พบข้อมูลใบสั่งซื้อ</div>";
    exit;
}

$po_no = $_GET['po_no'];
$supplier_id = $_SESSION['supplier_id'] ?? 0;

// ดึงข้อมูลหัวใบสั่งซื้อ
$sql = "
  SELECT po.*, e.full_name AS buyer_name, s.supplier_name
  FROM purchase_orders po
  JOIN employees e ON po.buyer_employee_id = e.employee_id
  JOIN suppliers s ON po.supplier_id = s.supplier_id
  WHERE po.po_no = ? AND po.supplier_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $po_no, $supplier_id);
$stmt->execute();
$po = $stmt->get_result()->fetch_assoc();

if (!$po) {
    echo "<div class='text-center text-danger'>ไม่พบใบสั่งซื้อนี้</div>";
    exit;
}

// ดึงรายการสินค้า
$sql_items = "
  SELECT pi.*, p.product_name
  FROM po_items pi
  JOIN products p ON pi.product_id = p.product_id
  WHERE pi.po_no = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("s", $po_no);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<h5 class="mb-3 text-info">เลขที่ใบสั่งซื้อ: <?= htmlspecialchars($po['po_no']) ?></h5>
<p><strong>วันที่ออกใบสั่งซื้อ:</strong> <?= htmlspecialchars($po['po_date']) ?></p>
<p><strong>ผู้จัดซื้อ:</strong> <?= htmlspecialchars($po['buyer_name']) ?></p>
<p><strong>สถานะ:</strong> <?= htmlspecialchars($po['status']) ?></p>
<p><strong>อนุมัติเมื่อ:</strong> <?= htmlspecialchars($po['approved_at'] ?: '-') ?></p>

<table class="table table-dark table-hover mt-3">
  <thead class="table-primary">
    <tr>
      <th>ลำดับ</th>
      <th>ชื่อสินค้า</th>
      <th>จำนวน</th>
      <th>หน่วย</th>
      <th>ราคาต่อหน่วย</th>
      <th>ส่วนลด (%)</th>
      <th>VAT (%)</th>
      <th>วันที่ส่งมอบ</th>
      <th>รวมสุทธิ (บาท)</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $i = 1; $grand = 0;
    while($item = $result_items->fetch_assoc()):
      $net = ($item['quantity'] * $item['unit_price']) * (1 - $item['discount_pct']/100);
      $vat = $net * ($item['vat_rate']/100);
      $total = $net + $vat;
      $grand += $total;
    ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($item['product_name']) ?></td>
      <td><?= htmlspecialchars($item['quantity']) ?></td>
      <td><?= htmlspecialchars($item['uom']) ?></td>
      <td><?= number_format($item['unit_price'], 2) ?></td>
      <td><?= htmlspecialchars($item['discount_pct']) ?></td>
      <td><?= htmlspecialchars($item['vat_rate']) ?></td>
      <td><?= htmlspecialchars($item['delivery_date']) ?></td>
      <td><?= number_format($total, 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<hr>
<h5 class="text-end">ยอดรวมสุทธิ: <?= number_format($po['grand_total'], 2) ?> ฿</h5>

<p class="mt-3"><strong>ที่อยู่จัดส่ง:</strong> <?= htmlspecialchars($po['ship_to_address'] ?? '-') ?></p>
<p><strong>สกุลเงิน:</strong> <?= htmlspecialchars($po['currency']) ?></p>
