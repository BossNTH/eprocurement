<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

// เปิด error ชั่วคราวเพื่อ debug (เอาออกได้ในโปรดักชัน)
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ==================== รับค่า ==================== */
$quote_no = trim($_GET['quote_no'] ?? '');

/* ========== เตรียมตัวแปรสำหรับ view ========== */
$recent_quotes = null;
$quote = null;
$items = null;
$success_po_no = null;
$error_msg = null;

/* ========== กรณียังไม่ได้เลือก quote: โชว์รายการให้เลือกก่อน ========== */
if ($quote_no === '') {
    $q = trim($_GET['q'] ?? '');

    if ($q !== '') {
        $like = '%' . $q . '%';
        $stmt = $conn->prepare(
            "SELECT q.quote_no, q.pr_no, q.quote_date, q.grand_total, s.supplier_name
             FROM purchase_quotes q
             LEFT JOIN suppliers s ON q.supplier_id = s.supplier_id
             WHERE q.status = 'SELECTED'
               AND (q.quote_no LIKE ? OR q.pr_no LIKE ? OR s.supplier_name LIKE ?)
             ORDER BY q.quote_date DESC
             LIMIT 100"
        );
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        $recent_quotes = $stmt->get_result();
        $stmt->close();
    } else {
        $recent_quotes = $conn->query(
            "SELECT q.quote_no, q.pr_no, q.quote_date, q.grand_total, s.supplier_name
             FROM purchase_quotes q
             LEFT JOIN suppliers s ON q.supplier_id = s.supplier_id
             WHERE q.status = 'SELECTED'
             ORDER BY q.quote_date DESC
             LIMIT 100"
        );
    }

} else {
    // load a single selected quote
    $stmt = $conn->prepare(
        "SELECT q.quote_no, q.pr_no, q.supplier_id, q.quote_date, q.valid_until,
                q.subtotal_before_vat, q.vat_total, q.grand_total, q.status,
                s.supplier_name, s.address, s.phone, s.email
         FROM purchase_quotes q
         LEFT JOIN suppliers s ON q.supplier_id = s.supplier_id
         WHERE q.quote_no = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $quote_no);
    $stmt->execute();
    $quote = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($quote) {
        // รายการสินค้าในใบเสนอราคา
        $q2 = $conn->prepare(
            "SELECT qi.product_id, p.product_name, qi.quantity, qi.unit_price,
                    qi.discount_pct, qi.vat_rate, qi.uom
             FROM quote_items qi
             LEFT JOIN products p ON qi.product_id = p.product_id
             WHERE qi.quote_no = ?"
        );
        $q2->bind_param("s", $quote_no);
        $q2->execute();
        $items = $q2->get_result();
    }

}

/* ========== บันทึกใบสั่งซื้อ เมื่อกดปุ่ม ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $quote) {
    $emp_id = (int)($_SESSION['employee_id'] ?? 0);
    $supplier_id = (int)$quote['supplier_id'];

    // สร้างเลขที่ PO: POYYYY-XXXX
    $prefix = "PO" . date("Y");
    $res = $conn->query("SELECT COUNT(*) AS c FROM purchase_orders WHERE YEAR(po_date) = YEAR(CURDATE())");
    $count = (int)$res->fetch_assoc()['c'] + 1;
    $po_no = sprintf("%s-%04d", $prefix, $count);

    $conn->begin_transaction();
    try {
        // สร้างหัวใบสั่งซื้อ (ใช้ค่า default ของฟิลด์อื่น ๆ ตาม schema)
        $stmt = $conn->prepare("
            INSERT INTO purchase_orders
                (po_no, quote_no, supplier_id, buyer_employee_id, po_date,
                 subtotal_before_vat, vat_total, grand_total, status)
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

        // รายการสินค้า
        if ($items && $items->num_rows > 0) {
            $ins = $conn->prepare("
                INSERT INTO po_items (po_no, product_id, quantity, unit_price, uom, discount_pct, vat_rate)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $items->data_seek(0);
            while ($it = $items->fetch_assoc()) {
                $ins->bind_param(
                    "sidddsd",
                    $po_no,
                    $it['product_id'],
                    $it['quantity'],
                    $it['unit_price'],
                    $it['uom'],
                    $it['discount_pct'],
                    $it['vat_rate']
                );
                $ins->execute();
            }
            $ins->close();
        }

        $conn->commit();
        $success_po_no = $po_no;
    } catch (Throwable $e) {
        $conn->rollback();
        $error_msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>ออกใบสั่งซื้อ | ฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        /* ===== ตารางพื้นขาว มีเส้นทุกช่อง (แบบในภาพ) ===== */
        body {
            background-color: #ebebebff;
            color: #2f2f2fff;
            font-family: 'Prompt', sans-serif;
            padding-left: 260px;
        }
.container-fluid { padding:2rem; }

.card {
            background: #ffffffff;
            border-radius: 16px;
            border: 1px solid rgba(20, 184, 166, 0.3);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        }

.section-title {
  font-size:1.1rem;
  color:#2f2f2fff;
  margin-bottom:.75rem;
}

/* ===== ตารางพื้นขาวแบบมีเส้นเฉพาะแนวนอก + ระหว่างแถว ===== */
.table-plain {
  width:100%;
  border-collapse:separate !important; /* เพื่อให้ใช้ border-spacing */
  border-spacing:0;
  background:#ffffff;
  color:#2f2f2fff;
  font-size:0.95rem;
  border:1px solid #d1d5db; /* ✅ เส้นกรอบนอก */
  border-radius:8px;
  overflow:hidden;
}

/* หัวตาราง */
.table-plain thead th {
  background:#f9fafb;
  font-weight:600;
  text-align:center;
  padding:10px 12px;
  border-bottom:1px solid #d1d5db; /* ✅ เส้นระหว่างหัวตารางกับเนื้อหา */
}

/* ตัวตาราง */
.table-plain td {
  padding:10px 12px;
  text-align:center;
  vertical-align:middle;
  border-bottom:1px solid #e5e7eb; /* ✅ เส้นคั่นเฉพาะแนวนอน */
  border-right:none !important;     /* ❌ ไม่มีเส้นแนวตั้ง */
}

/* แถวสุดท้ายไม่ต้องมีเส้นล่าง */
.table-plain tbody tr:last-child td {
  border-bottom:none;
}

/* Hover แถว */
.table-plain tbody tr:hover {
  background:#f3f4f6;
  transition:background 0.15s;
}

/* ช่องตัวเลขขวา */
.table-plain .text-end {
  text-align:right !important;
}

/* ปุ่มสไตล์ในภาพ (แก้ตามของเดิม) */
.btn-outline-sky {
  background:#fff;
  color:#0284c7;
  border:1px solid #7dd3fc;
  border-radius:6px;
  padding:5px 10px;
  font-size:0.9rem;
  display:inline-flex;
  align-items:center;
  gap:4px;
}
.btn-outline-sky:hover {
  background:#e0f2fe;
  color:#0369a1;
}

/* ปุ่มยืนยัน */
.btn-confirm {
  background:linear-gradient(135deg,#14b8a6,#0d9488);
  border:0;
  color:#fff;
  border-radius:8px;
  padding:10px 20px;
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
            color: #2f2f2fff;
        }

        .summary-box .value.total {
            color: #ffffff !important;
            /* ✅ เฉพาะบรรทัดรวมสุทธิ */
        }

        .teaxt-info {
            color: #2f2f2fff;
        }

    </style>
</head>
<body>
<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-cart-check me-2"></i>ออกใบสั่งซื้อ (Purchase Order)</h2>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if ($quote_no === ''): ?>
        <!-- ยังไม่ได้เลือก quote: แสดงให้เลือก -->
        <div class="card p-3">
            <h5 class="text-dark">เลือกใบเสนอราคาที่ต้องการสร้าง PO</h5>
            <form method="get" class="row g-2 align-items-center mb-3">
  <div class="col-sm-8 col-md-6">
    <input id="quoteSearch" name="q" type="text" class="form-control"
           placeholder="ค้นหา: Quote No., PR No., ผู้จำหน่าย"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-primary" type="submit">
      <i class="bi bi-search"></i> ค้นหา
    </button>
  </div>
</form>
            <?php if ($recent_quotes && $recent_quotes->num_rows > 0): ?>
                <div class="table-responsive mt-2">
                    <table class="table-plain align-middle">
                        <thead>
                            <tr>
                                <th>Quote No.</th>
                                <th>PR No.</th>
                                <th>Supplier</th>
                                <th>วันที่</th>
                                <th class="text-end">รวมสุทธิ</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($rq = $recent_quotes->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($rq['quote_no']) ?></td>
                                <td><?= htmlspecialchars($rq['pr_no'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($rq['supplier_name'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($rq['quote_date'])) ?></td>
                                <td class="text-end"><?= number_format($rq['grand_total'], 2) ?></td>
                                <td>
                                    <a class="btn btn-outline-sky"
                                       href="po_create.php?quote_no=<?= urlencode($rq['quote_no']) ?>">
                                        เปลี่ยนเป็น PO
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">ยังไม่มีใบเสนอราคาที่สถานะ “SELECTED”</div>
            <?php endif; ?>
        </div>

    <?php elseif (!$quote): ?>
        <!-- เลือก quote แล้ว แต่ไม่พบข้อมูล -->
        <div class="alert alert-danger">❌ ไม่พบข้อมูลใบเสนอราคาเลขที่ <?= htmlspecialchars($quote_no) ?></div>

    <?php else: ?>
        <!-- แสดงรายละเอียดใบเสนอราคา + ฟอร์มสร้าง PO -->
        <div class="card p-4 mb-3">
            <div class="section-title"><i class="bi bi-building me-1"></i>ข้อมูลผู้จำหน่าย</div>
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

        <div class="card p-4">
            <div class="section-title"><i class="bi bi-box-seam me-1"></i>รายการสินค้าในใบเสนอราคา</div>
            <table class="table-plain align-middle">
                <thead>
                <tr>
                    <th>สินค้า</th>
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
                $sum = 0;
                if ($items) {
                    $items->data_seek(0);
                    while ($it = $items->fetch_assoc()):
                        $total = $it['quantity'] * $it['unit_price'] * (1 - $it['discount_pct'] / 100);
                        $sum += $total;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($it['product_name']) ?></td>
                        <td class="text-end"><?= number_format($it['quantity'], 2) ?></td>
                        <td><?= htmlspecialchars($it['uom']) ?></td>
                        <td class="text-end"><?= number_format($it['unit_price'], 2) ?></td>
                        <td class="text-end"><?= number_format($it['discount_pct'], 2) ?></td>
                        <td class="text-end"><?= number_format($it['vat_rate'], 2) ?></td>
                        <td class="text-end"><?= number_format($total, 2) ?></td>
                    </tr>
                <?php
                    endwhile;
                }
                ?>
                </tbody>
            </table>

            <!-- สรุปยอด -->
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

        <form method="post" class="text-end mt-3">
            <button type="submit" class="btn btn-confirm">
                <i class="bi bi-check2-circle me-1"></i> ยืนยันและสร้างใบสั่งซื้อ
            </button>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($success_po_no): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'สร้างใบสั่งซื้อเรียบร้อย!',
    text: 'หมายเลขใบสั่งซื้อ: <?= htmlspecialchars($success_po_no) ?>',
    confirmButtonText: 'ตกลง'
}).then(()=> {
    window.location = 'dashboard.php';
});
</script>
<?php endif; ?>

<script>
(function () {
    const input = document.getElementById('quoteSearch');
    if (!input) return;

    // หา tbody ของตารางรายการใบเสนอราคา (ปรับ selector ได้ถ้ามีหลายตาราง)
    const tbody = document.querySelector('.table-plain tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));

    // สร้างแถว "ไม่พบรายการ" อัตโนมัติ
    let noRow = document.getElementById('noRows');
    if (!noRow) {
        noRow = document.createElement('tr');
        noRow.id = 'noRows';
        const colCount = document.querySelector('.table-plain thead tr').children.length || 1;
        noRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted py-3">ไม่พบรายการ</td>`;
        noRow.style.display = 'none';
        tbody.appendChild(noRow);
    }

    function filter() {
        const term = input.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(tr => {
            const text = tr.innerText.toLowerCase();
            const match = text.includes(term);
            tr.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        noRow.style.display = visible === 0 ? '' : 'none';
    }

    // กรองทันทีเมื่อพิมพ์ + โหลดหน้ามีค่าเดิมจาก query
    input.addEventListener('input', filter);
    filter();
})();
</script>
</body>
</html>