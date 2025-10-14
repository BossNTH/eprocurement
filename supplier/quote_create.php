<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/supplier_header.php";

if (!isset($_GET['pr_no'])) {
    echo "<script>alert('ไม่พบใบขอซื้อ'); window.location='view_pr.php';</script>";
    exit();
}

$pr_no = $_GET['pr_no'];
$supplier_id = $_SESSION['supplier_id'];

// ดึงรายการสินค้าใน PR
$sql = "
  SELECT pi.product_id, p.product_name, pi.quantity, pi.uom, pi.need_by_date
  FROM pr_items pi
  JOIN products p ON pi.product_id = p.product_id
  WHERE pi.pr_no = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pr_no);
$stmt->execute();
$result_items = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_terms = $_POST['delivery_terms'];
    $payment_terms = $_POST['payment_terms'];
    $valid_until = $_POST['valid_until'];
    $currency = 'THB';
    $quote_no = 'Q' . date('YmdHis');

    // คำนวณราคารวม
    $subtotal = 0;
    foreach ($_POST['product_id'] as $index => $pid) {
        $qty = $_POST['quantity'][$index];
        $price = $_POST['unit_price'][$index];
        $discount = $_POST['discount_pct'][$index];
        $vat_rate = $_POST['vat_rate'][$index];
        $net = ($qty * $price) * (1 - ($discount / 100));
        $subtotal += $net * (1 + ($vat_rate / 100));
    }
    $vat_total = $subtotal * 0.07;
    $grand_total = $subtotal + $vat_total;

    // บันทึกหัวใบเสนอราคา
    $sql_quote = "
      INSERT INTO purchase_quotes
        (quote_no, pr_no, quote_date, supplier_id, subtotal_before_vat, vat_total, grand_total,
         valid_until, delivery_terms, payment_terms, currency, status)
      VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, 'SUPPLIER_SUBMITTED')
    ";
    $stmt_quote = $conn->prepare($sql_quote);
    $stmt_quote->bind_param(
        "ssiddsssss",
        $quote_no,
        $pr_no,
        $supplier_id,
        $subtotal,
        $vat_total,
        $grand_total,
        $valid_until,
        $delivery_terms,
        $payment_terms,
        $currency
    );

    $stmt_quote->execute();

    // บันทึกรายการสินค้า
    foreach ($_POST['product_id'] as $index => $pid) {
        $qty = $_POST['quantity'][$index];
        $price = $_POST['unit_price'][$index];
        $uom = $_POST['uom'][$index];
        $lead = $_POST['lead_time_days'][$index];
        $discount = $_POST['discount_pct'][$index];
        $vat_rate = $_POST['vat_rate'][$index];

        $sql_item = "
          INSERT INTO quote_items (quote_no, product_id, quantity, unit_price, uom, lead_time_days, discount_pct, vat_rate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt_item = $conn->prepare($sql_item);
        $stmt_item->bind_param("siddsidd", $quote_no, $pid, $qty, $price, $uom, $lead, $discount, $vat_rate);
        $stmt_item->execute();
    }

    echo "<script>alert('ส่งใบเสนอราคาเรียบร้อยแล้ว'); window.location='manage_quotes.php';</script>";
    exit();
}
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
    }

    .form-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        color: #e2e8f0;
    }

    .table th {
        background: linear-gradient(90deg, #047857, #10b981);
        color: #fff;
        padding: 10px;
    }

    .table td {
        padding: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
    }

    .table input {
        width: 100%;
        text-align: center;
        border: none;
        border-radius: 6px;
        padding: 6px;
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .btn-submit {
        background-color: #10b981;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        background-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    }
</style>

<div class="page-container">
    <h2><i class="bi bi-currency-exchange"></i> ใบเสนอราคา (Quote) สำหรับ PR: <?= htmlspecialchars($pr_no) ?></h2>

    <form method="POST">
        <div class="form-card">
            <h4>รายการสินค้า</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>หน่วย</th>
                        <th>ราคาต่อหน่วย (บาท)</th>
                        <th>ส่วนลด (%)</th>
                        <th>VAT (%)</th>
                        <th>ระยะเวลาจัดส่ง (วัน)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $result_items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['product_name']) ?>
                                <input type="hidden" name="product_id[]" value="<?= $item['product_id'] ?>">
                            </td>
                            <td>
                                <input type="text" name="quantity[]" value="<?= $item['quantity'] ?>" readonly>
                            </td>
                            <td>
                                <input type="text" name="uom[]" value="<?= $item['uom'] ?>" readonly>
                            </td>
                            <td><input type="number" name="unit_price[]" step="0.01" required></td>
                            <td><input type="number" name="discount_pct[]" step="0.01" value="0"></td>
                            <td><input type="number" name="vat_rate[]" step="0.01" value="7"></td>
                            <td><input type="number" name="lead_time_days[]" step="1" value="3"></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="form-card" style="margin-top:25px;">
            <h4>เงื่อนไขการเสนอราคา</h4>
            <div class="row" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:15px;">
                <div>
                    <label>เงื่อนไขการชำระเงิน</label>
                    <input type="text" name="payment_terms" class="form-control" required placeholder="เช่น เครดิต 30 วัน">
                </div>
                <div>
                    <label>เงื่อนไขการส่งมอบ</label>
                    <input type="text" name="delivery_terms" class="form-control" required placeholder="เช่น ส่งภายใน 7 วัน">
                </div>
                <div>
                    <label>วันหมดอายุใบเสนอราคา</label>
                    <input type="date" name="valid_until" class="form-control" required>
                </div>
            </div>
        </div>

        <div style="margin-top:25px; text-align:right;">
            <button type="submit" class="btn-submit"><i class="bi bi-send"></i> ส่งใบเสนอราคา</button>
            <a href="view_pr.php" class="btn btn-secondary">ยกเลิก</a>
        </div>
    </form>
</div>

</body>

</html>