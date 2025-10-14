<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

/* =================== ดึงข้อมูลใบขอซื้อที่อนุมัติแล้ว =================== */
$search = trim($_GET['q'] ?? '');
$where = "WHERE pr.status='APPROVE'";
if ($search !== '') {
    $searchSQL = $conn->real_escape_string($search);
    $where .= " AND (pr.pr_no LIKE '%$searchSQL%' OR e.full_name LIKE '%$searchSQL%' OR d.name LIKE '%$searchSQL%')";
}

$sql = "
  SELECT pr.pr_no, pr.request_date, pr.need_by_date, pr.status,
         e.full_name AS requester_name, d.name
  FROM purchase_requisitions pr
  LEFT JOIN employees e ON pr.requested_by = e.employee_id
  LEFT JOIN departments d ON e.department_id = d.department_id
  $where
  ORDER BY pr.request_date DESC
";
$prs = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบขอซื้อที่อนุมัติ | ฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #ebebebff;
            color: #2f2f2fff;
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, .3);
        }

        .table thead {
            background: #334155;
            color: #a5f3fc;
        }

        .table tbody tr:hover {
            background-color: rgba(20, 184, 166, 0.1);
            cursor: pointer;
        }

        .btn-outline-info {
            color: #22d3ee;
            border-color: #22d3ee;
        }

        .btn-outline-info:hover {
            background: #22d3ee;
            color: #0f172a;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-file-earmark-check me-2"></i>ใบขอซื้อที่อนุมัติแล้ว</h2>

        <form class="d-flex mb-3" method="get">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="ค้นหา PR No / ผู้ขอซื้อ / แผนก...">
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if ($search): ?>
                <a href="approved_pr.php" class="btn btn-outline-secondary ms-2">ล้าง</a>
            <?php endif; ?>
        </form>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-center">
                            <th width="120">PR No.</th>
                            <th>ผู้ขอซื้อ</th>
                            <th>แผนก</th>
                            <th>วันที่ขอซื้อ</th>
                            <th>วันที่ต้องการ</th>
                            <th>สถานะ</th>
                            <th width="160">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($prs->num_rows > 0): while ($r = $prs->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($r['pr_no']) ?></td>
                                    <td><?= htmlspecialchars($r['requester_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['name'] ?? '-') ?></td>
                                    <td class="text-center"><?= date("d/m/Y", strtotime($r['request_date'])) ?></td>
                                    <td class="text-center"><?= date("d/m/Y", strtotime($r['need_by_date'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= htmlspecialchars(ucfirst($r['status'])) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="quote_compare.php?pr_no=<?= urlencode($r['pr_no']) ?>" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-diagram-3"></i> เปรียบเทียบราคา
                                        </a>

                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">ยังไม่มีใบขอซื้อที่อนุมัติ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>