<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

/* ==================== รับค่า PR ที่เลือก ==================== */
$selected_pr = trim($_GET['pr_no'] ?? '');

/* ------------------ รับ quote_no ------------------ */
$quote_no = trim($_GET['quote_no'] ?? '');
if ($quote_no === '') {
    echo "<div class='alert alert-danger m-5'>❌ ไม่พบเลขที่ใบเสนอราคา</div>";
    exit;
}

/* ------------------ ดึงข้อมูลใบเสนอราคา ------------------ */
$stmt = $conn->prepare("
  SELECT q.quote_no, q.pr_no, q.supplier_id, q.quote_date, q.valid_until, 
         q.subtotal_before_vat, q.vat_total, q.grand_total, q.status,
         s.supplier_name, s.address, s.phone, s.email
  FROM purchase_quotes q
  LEFT JOIN suppliers s ON q.supplier_id = s.supplier_id
  WHERE q.quote_no = ?
  LIMIT 1
");
$stmt->bind_param("s", $quote_no);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quote) {
    echo "<div class='alert alert-danger m-5'>❌ ไม่พบข้อมูลใบเสนอราคาในระบบ</div>";
    exit;
}

/* ------------------ ดึงรายการสินค้าในใบเสนอราคา ------------------ */
$q2 = $conn->prepare("
  SELECT qi.product_id, p.product_name, qi.quantity, qi.unit_price, qi.discount_pct, qi.vat_rate, qi.uom
  FROM quote_items qi
  LEFT JOIN products p ON qi.product_id = p.product_id
  WHERE qi.quote_no = ?
");
$q2->bind_param("s", $quote_no);
$q2->execute();
$items = $q2->get_result();

/* ------------------ บันทึกใบสั่งซื้อ ------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_SESSION['employee_id'] ?? 0;
    $supplier_id = $quote['supplier_id'];
    $pr_no = $quote['pr_no'];

    // สร้างรหัส PO ใหม่
    $prefix = "PO" . date("Y");
    $res = $conn->query("SELECT COUNT(*) AS c FROM purchase_orders WHERE YEAR(po_date) = YEAR(NOW())");
    $count = (int)$res->fetch_assoc()['c'] + 1;
    $po_no = sprintf("%s-%04d", $prefix, $count);

    $conn->begin_transaction();
    try {
        // ✅ INSERT ให้ตรง schema
        $stmt = $conn->prepare("
      INSERT INTO purchase_orders 
      (po_no, quote_no, supplier_id, buyer_employee_id, po_date, subtotal_before_vat, vat_total, grand_total, status)
      VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, 'DRAFT')
    ");
        $stmt->bind_param(
            "ssiiddd",
            $po_no,
            $quote_no,
            $supplier_id,
            $emp_id,
            $quote['subtotal_before_vat'],
            $quote['vat_total'],
            $quote['grand_total']
        );
        $stmt->execute();
        $stmt->close();

        // ✅ เพิ่มลง po_items
        $q3 = $conn->prepare("
      INSERT INTO po_items (po_no, product_id, quantity, unit_price, discount_pct, vat_rate, uom)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
        $items->data_seek(0);
        while ($it = $items->fetch_assoc()) {
            $q3->bind_param(
                "sidddds",
                $po_no,
                $it['product_id'],
                $it['quantity'],
                $it['unit_price'],
                $it['discount_pct'],
                $it['vat_rate'],
                $it['uom']
            );
            $q3->execute();
        }
        $q3->close();

        // ✅ อัปเดตสถานะใบเสนอราคา
        $conn->query("UPDATE purchase_quotes SET status='SELECTED' WHERE quote_no='{$quote_no}'");

        $conn->commit();

        echo "<script>
      Swal.fire({
        icon:'success',
        title:'สร้างใบสั่งซื้อเรียบร้อย!',
        text:'หมายเลขใบสั่งซื้อ: {$po_no}',
        confirmButtonText:'ตกลง'
      }).then(()=>window.location='dashboard.php');
    </script>";
    } catch (Throwable $e) {
        $conn->rollback();
        echo '<div class=\"alert alert-danger\">' . $e->getMessage() . '</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ออกใบสั่งซื้อ | ฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #1e293b;
            color: #e0e0e0ff;
            font-family: 'Prompt', sans-serif;
            padding-left: 260px;
        }

        .container-fluid {
            padding: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            color: #b0d6ffff;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .3);
            margin-bottom: 1.5rem;
        }

        /* ===== ปรับสีตารางใหม่ให้เข้ากับธีมมืด Emerald ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: rgba(15, 23, 42, 0.7);
            /* ✅ พื้นหลังโปร่งนิด ๆ */
            color: #e2e8f0;
            /* ตัวอักษรสีเทาอ่อน */
            border-radius: 10px;
            overflow: hidden;
        }

        /* ส่วนหัวตาราง */
        .table thead {
            background: linear-gradient(90deg, #0d9488, #14b8a6);
            /* ✅ เขียวอมฟ้า gradient */
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* ช่องของตาราง */
        .table th,
        .table td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* เส้นคั่นล่างสุด */
        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Hover แถว */
        .table tbody tr:hover {
            background-color: rgba(20, 184, 166, 0.12);
            /* ✅ เขียวโปร่งเมื่อชี้ */
            transform: scale(1.005);
            transition: all 0.15s ease-in-out;
        }

        /* จัดชิดขวาเฉพาะตัวเลข */
        .table td.text-end {
            text-align: right !important;
        }

        /* แถวสรุปท้ายตาราง (เช่น Total) */
        .table tfoot {
            background: rgba(20, 184, 166, 0.15);
            font-weight: 600;
            color: #ffffff;
        }




        .btn-confirm {
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            border: 0;
            color: #fff;
            border-radius: 8px;
            font-size: 1rem;
            padding: 10px 24px;
        }

        .btn-confirm:hover {
            opacity: 0.9;
        }

        .summary-box {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(20, 184, 166, 0.4);
            border-radius: 12px;
            padding: 1rem;
        }

        .summary-box .value {
            font-size: 1.4rem;
            font-weight: 600;
            color: #5eead4;
        }

        p {
            color: #e0e0e0ff;
        }

        .summary-box .value.total {
            color: #ffffff !important;
            /* ✅ เฉพาะบรรทัดรวมสุทธิ */
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-cart-check me-2"></i>ออกใบสั่งซื้อ (Purchase Order)</h2>

        <!-- ส่วนข้อมูลผู้ขาย -->
        <div class="card p-4">
            <div class="section-title"><i class="bi bi-building me-1"></i>ข้อมูลผู้จำหน่าย </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ชื่อผู้จำหน่าย:</strong> <?= htmlspecialchars($quote['supplier_name']) ?></p>
                    <p><strong>เบอร์โทรศัพท์:</strong> <?= htmlspecialchars($quote['phone']) ?></p>
                    <p><strong>อีเมล:</strong> <?= htmlspecialchars($quote['email']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($quote['address'] ?: '-') ?></p>
                    <p><strong>วันที่เสนอราคา:</strong> <?= date("d/m/Y", strtotime($quote['quote_date'])) ?></p>
                    <p><strong>PR ที่อ้างอิง:</strong> <?= htmlspecialchars($quote['pr_no']) ?></p>
                </div>
            </div>
        </div>

        <!-- ตารางสินค้า -->
        <div class="card p-4">
            <div class="section-title"><i class="bi bi-box-seam me-1"></i> รายการสินค้าในใบเสนอราคา</div>
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-center">ราคาต่อหน่วย</th>
                        <th class="text-center">ส่วนลด (%)</th>
                        <th class="text-center">ภาษี (%)</th>
                        <th class="text-center">รวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum = 0;
                    $items->data_seek(0);
                    while ($it = $items->fetch_assoc()):
                        $total = $it['quantity'] * $it['unit_price'] * (1 - $it['discount_pct'] / 100);
                        $sum += $total;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($it['product_name']) ?></td>
                            <td class="text-end"><?= number_format($it['quantity'], 2) ?></td>
                            <td class="text-center"><?= htmlspecialchars($it['uom']) ?></td>
                            <td class="text-end"><?= number_format($it['unit_price'], 2) ?></td>
                            <td class="text-end"><?= number_format($it['discount_pct'], 2) ?></td>
                            <td class="text-end"><?= number_format($it['vat_rate'], 2) ?></td>
                            <td class="text-end"><?= number_format($total, 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- กล่องสรุปยอดรวม -->
            <div class="summary-box mt-3">
                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <div class="d-flex justify-content-between">
                            <span>รวมก่อนภาษี:</span>
                            <span class="value"><?= number_format($quote['subtotal_before_vat'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>ภาษีมูลค่าเพิ่ม:</span>
                            <span class="value"><?= number_format($quote['vat_total'], 2) ?></span>
                        </div>
                        <hr class="border-info">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>รวมสุทธิ:</span>
                            <span class="value total"><?= number_format($quote['grand_total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่มยืนยัน -->
        <form method="post" class="text-end mt-4">
            <button type="submit" class="btn btn-confirm">
                <i class="bi bi-check2-circle me-1"></i> ยืนยันและสร้างใบสั่งซื้อ
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>