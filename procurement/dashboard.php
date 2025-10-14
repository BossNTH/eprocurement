<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

$emp_id = $_SESSION['employee_id'] ?? 0;

/* ---------- ดึงข้อมูลสรุป ---------- */
$counts = [
    'products' => 0,
    'approved_pr' => 0,
    'quotes' => 0,
    'orders' => 0
];

/* สินค้าทั้งหมด */
$q1 = $conn->query("SELECT COUNT(*) AS c FROM products");
$counts['products'] = (int)$q1->fetch_assoc()['c'];

/* ใบขอซื้อที่อนุมัติ */
$q2 = $conn->query("SELECT COUNT(*) AS c FROM purchase_requisitions WHERE status='approved'");
$counts['approved_pr'] = (int)$q2->fetch_assoc()['c'];

/* ใบเสนอราคาที่บันทึกแล้ว */
$q3 = $conn->query("SELECT COUNT(*) AS c FROM purchase_quotes");
$counts['quotes'] = (int)$q3->fetch_assoc()['c'];

/* ใบสั่งซื้อที่ออกแล้ว */
$q4 = $conn->query("SELECT COUNT(*) AS c FROM purchase_orders");
$counts['orders'] = (int)$q4->fetch_assoc()['c'];

/* ---------- ดึงใบขอซื้ออนุมัติล่าสุด ---------- */
$recentPR = $conn->query("
  SELECT pr_no, request_date, need_by_date, requested_by
  FROM purchase_requisitions
  WHERE status='approved'
  ORDER BY request_date DESC
  LIMIT 5
");

/* ---------- ดึงใบสั่งซื้อล่าสุด ---------- */
$recentPO = $conn->query("
  SELECT po_no, needed_date, supplier_id 
  FROM purchase_orders
  ORDER BY needed_date DESC
  LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดจัดซื้อ | ระบบจัดซื้อสมุนไพร</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
            font-family: 'Prompt', sans-serif;
            margin: 0;
            padding-left: 260px;
        }

        .main-content {
            padding: 2rem;
        }

        .page-title {
            color: #5eead4;
            font-size: 1.6rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .card {
            background: #1e293b;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(20, 184, 166, 0.3);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
            transition: .2s;
        }

        .card:hover {
            border-color: #14b8a6;
            transform: translateY(-2px);
        }

        .card h5 {
            color: #a5f3fc;
        }

        .table th {
            background: #334155;
            color: #a5f3fc;
        }

        .table tr:hover {
            background-color: rgba(20, 184, 166, 0.15);
        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="page-title">
            <i class="bi bi-speedometer2"></i> แดชบอร์ดฝ่ายจัดซื้อ
        </div>

        <!-- Summary Cards -->
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <h5>สินค้า</h5>
                    <div class="display-6 fw-bold text-info"><?= $counts['products'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <h5>ใบขอซื้อที่อนุมัติ</h5>
                    <div class="display-6 fw-bold text-success"><?= $counts['approved_pr'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <h5>ใบเสนอราคา</h5>
                    <div class="display-6 fw-bold text-warning"><?= $counts['quotes'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <h5>ใบสั่งซื้อ</h5>
                    <div class="display-6 fw-bold text-primary"><?= $counts['orders'] ?></div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card mt-4">
            <h5><i class="bi bi-graph-up me-2"></i>สรุปจำนวนรายการจัดซื้อ</h5>
            <canvas id="summaryChart" height="80"></canvas>
        </div>

        <!-- Recent Tables -->
        <div class="row mt-4 g-3">
            <div class="col-md-6">
                <div class="card">
                    <h5><i class="bi bi-file-earmark-check me-1"></i> ใบขอซื้อที่อนุมัติล่าสุด</h5>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>เลขที่ PR</th>
                                    <th>วันที่ขอซื้อ</th>
                                    <th>วันที่ต้องการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentPR->num_rows): while ($r = $recentPR->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['pr_no']) ?></td>
                                            <td><?= date("d/m/Y", strtotime($r['request_date'])) ?></td>
                                            <td><?= date("d/m/Y", strtotime($r['need_by_date'])) ?></td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <h5><i class="bi bi-cart-check me-1"></i> ใบสั่งซื้อล่าสุด</h5>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>เลขที่ PO</th>
                                    <th>วันที่สั่งซื้อ</th>
                                    <th>รหัสผู้จำหน่าย</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentPO->num_rows): while ($r = $recentPO->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['po_no']) ?></td>
                                            <td><?= date("d/m/Y", strtotime($r['order_date'])) ?></td>
                                            <td><?= htmlspecialchars($r['supplier_id']) ?></td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('summaryChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['สินค้า', 'PR อนุมัติ', 'ใบเสนอราคา', 'ใบสั่งซื้อ'],
                datasets: [{
                    label: 'จำนวนรายการ',
                    data: [<?= $counts['products'] ?>, <?= $counts['approved_pr'] ?>, <?= $counts['quotes'] ?>, <?= $counts['orders'] ?>],
                    backgroundColor: ['#38bdf8', '#22c55e', '#facc15', '#3b82f6'],
                    borderRadius: 8
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#a5f3fc'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#a5f3fc'
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>