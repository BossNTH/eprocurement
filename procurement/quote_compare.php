<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

/* ==================== โหลดรายการ PR ที่อนุมัติ ==================== */
$prs = $conn->query("
  SELECT pr_no 
  FROM purchase_requisitions 
  WHERE status = 'APPROVE' 
  ORDER BY request_date DESC
");

/* ==================== รับค่า PR ที่เลือก ==================== */
$selected_pr = trim($_GET['pr_no'] ?? '');

/* ==================== ดึงใบเสนอราคาของ PR นั้น ==================== */
$quotes = null;
if ($selected_pr !== '') {
    $stmt = $conn->prepare("
    SELECT q.quote_no, q.pr_no, q.quote_date, q.supplier_id,
           q.subtotal_before_vat, q.vat_total, q.grand_total,
           q.status, s.supplier_name, s.phone, s.email
    FROM purchase_quotes q
    LEFT JOIN suppliers s ON q.supplier_id = s.supplier_id
    WHERE q.pr_no = ?
    ORDER BY q.grand_total ASC
  ");
    $stmt->bind_param("s", $selected_pr);
    $stmt->execute();
    $quotes = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เปรียบเทียบใบเสนอราคา | ฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #e6e6e6ff;
            color: #353535ff;
            font-family: 'Prompt', sans-serif;
            padding-left: 260px;
        }

        .container-fluid {
            padding: 2rem;
        }

        .card {
            background: #ffffffff;
            border-radius: 16px;
            border: 1px solid rgba(20, 184, 166, 0.3);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        }

        .table thead {
            background: #334155;
            color: #a5f3fc;
        }

        .table tbody tr:hover {
            background-color: rgba(20, 184, 166, 0.1);
        }

        .select-pr {
            max-width: 340px;
        }

        .badge-status {
            font-size: .85rem;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-diagram-3 me-2"></i>เปรียบเทียบใบเสนอราคา</h2>

        <!-- ======= ตัวเลือก PR ======= -->
        <form class="d-flex align-items-center gap-2 mb-4" method="get">
            <label class="fw-semibold">เลือกใบขอซื้อ (PR):</label>
            <select name="pr_no" class="form-select select-pr" required>
                <option value="">-- เลือกใบขอซื้อที่อนุมัติแล้ว --</option>
                <?php while ($pr = $prs->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($pr['pr_no']) ?>" <?= $selected_pr === $pr['pr_no'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pr['pr_no']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> แสดงใบเสนอราคา</button>
        </form>

        <?php if ($selected_pr === ''): ?>
            <div class="alert alert-info">กรุณาเลือกใบขอซื้อ (PR) เพื่อดูใบเสนอราคา</div>
        <?php else: ?>

            <?php
            if (!$quotes || $quotes->num_rows === 0):
            ?>
                <div class="alert alert-warning">ยังไม่มีใบเสนอราคาสำหรับ PR นี้</div>
            <?php else: ?>
                <?php while ($q = $quotes->fetch_assoc()): ?>
                    <?php
                    $qi = $conn->prepare("
          SELECT qi.product_id, p.product_name, qi.quantity, qi.unit_price, qi.uom, qi.discount_pct, qi.vat_rate
          FROM quote_items qi
          LEFT JOIN products p ON qi.product_id = p.product_id
          WHERE qi.quote_no = ?
        ");
                    $qi->bind_param("s", $q['quote_no']);
                    $qi->execute();
                    $items = $qi->get_result();

                    $badge_color = match ($q['status']) {
                        'SELECTED' => 'bg-success',
                        'EVALUATING' => 'bg-warning text-dark',
                        'SUPPLIER_SUBMITTED' => 'bg-info text-dark',
                        'NOT_SELECTED' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center text-white" style="background:linear-gradient(135deg,#0d9488,#14b8a6);">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>ใบเสนอราคา: <?= htmlspecialchars($q['quote_no']) ?></h5>
                                <small>ผู้จำหน่าย: <?= htmlspecialchars($q['supplier_name']) ?> • โทร: <?= htmlspecialchars($q['phone']) ?> • <?= htmlspecialchars($q['email']) ?></small>
                            </div>
                            <span class="badge <?= $badge_color ?> badge-status"><?= htmlspecialchars($q['status']) ?></span>
                        </div>

                        <div class="card-body">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-center">
                                        <th>สินค้า</th>
                                        <th>จำนวน</th>
                                        <th>หน่วย</th>
                                        <th>ราคาต่อหน่วย</th>
                                        <th>ส่วนลด (%)</th>
                                        <th>ภาษี (%)</th>
                                        <th>รวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sum = 0;
                                    while ($row = $items->fetch_assoc()):
                                        $total = $row['quantity'] * $row['unit_price'] * (1 - $row['discount_pct'] / 100);
                                        $sum += $total;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td class="text-end"><?= number_format($row['quantity'], 2) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['uom']) ?></td>
                                            <td class="text-end"><?= number_format($row['unit_price'], 2) ?></td>
                                            <td class="text-end"><?= number_format($row['discount_pct'], 2) ?></td>
                                            <td class="text-end"><?= number_format($row['vat_rate'], 2) ?></td>
                                            <td class="text-end"><?= number_format($total, 2) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold text-info">
                                        <td colspan="6" class="text-end">รวมสุทธิ (ก่อน VAT)</td>
                                        <td class="text-end"><?= number_format($sum, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="text-end mt-3">
                                <a href="po_create.php?quote_no=<?= urlencode($q['quote_no']) ?>" class="btn btn-success">
                                    <i class="bi bi-cart-check"></i> ออกใบสั่งซื้อจากใบเสนอราคานี้
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>