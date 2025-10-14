<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/manager_header.php";

/* ===== เมื่อผู้จัดการคลิกอนุมัติหรือปฏิเสธ ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_no'], $_POST['action'])) {
    $po_no = $_POST['po_no'];
    $action = $_POST['action'];
    $employee_id = $_SESSION['employee_id'];

    if ($action === 'approve') {
        $sql = "UPDATE purchase_orders 
                SET status='APPROVED', approved_by=?, approved_at=NOW() 
                WHERE po_no=? AND status='DRAFT'";
    } elseif ($action === 'reject') {
        $sql = "UPDATE purchase_orders 
                SET status='REJECTED', approved_by=?, approved_at=NOW() 
                WHERE po_no=? AND status='DRAFT'";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $employee_id, $po_no);
    $stmt->execute();


    // ดึงข้อมูลเก่าก่อนอัปเดต
    $old_res = $conn->query("SELECT status FROM purchase_orders WHERE po_no='$po_no'");
    $old_data = $old_res->fetch_assoc();
    $old_json = json_encode($old_data, JSON_UNESCAPED_UNICODE);


    // ดึงข้อมูลใหม่หลังอัปเดต
    $new_res = $conn->query("SELECT status, approved_by, approved_at FROM purchase_orders WHERE po_no='$po_no'");
    $new_data = $new_res->fetch_assoc();
    $new_json = json_encode($new_data, JSON_UNESCAPED_UNICODE);



    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'ดำเนินการเรียบร้อยแล้ว!',
            text: 'สถานะใบสั่งซื้อถูกอัปเดตสำเร็จ',
            confirmButtonColor: '#10b981'
        }).then(() => window.location='purchase_approval.php');
    </script>";
    exit();
}

/* ===== ดึงรายการใบสั่งซื้อที่รออนุมัติ ===== */
$sql = "
    SELECT po.po_no, po.po_date, po.grand_total, po.status, s.supplier_name, e.full_name AS buyer_name
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.supplier_id
    JOIN employees e ON po.buyer_employee_id = e.employee_id
    WHERE po.status = 'DRAFT'
    ORDER BY po.po_date DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>อนุมัติใบสั่งซื้อ | ผู้จัดการฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            font-family: 'Prompt', sans-serif;
            padding-left: 260px;
        }

        .container-fluid {
            padding: 2.5rem;
        }

        h3 {
            color: #22d3ee;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ====== กล่องตารางหลัก ====== */
        .card-table {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
        }

        /* ====== ตาราง ====== */
        .table {
            width: 100%;
            border-collapse: collapse;
            color: #e2e8f0 !important;
            background: rgba(15, 23, 42, 0.85) !important;
            /* ✅ บังคับใช้พื้นหลังมืด */
            border-radius: 12px;
            overflow: hidden;
        }


        /* หัวตาราง */
        .table thead {
            background: linear-gradient(90deg, #0284c7, #22d3ee) !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        /* ช่องในตาราง */
        .table th,
        .table td {
            text-align: center;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }



        /* Hover */
        .table tbody tr:hover {
            background-color: rgba(34, 211, 238, 0.1) !important;
            transition: all 0.15s ease-in-out;
        }


        /* สถานะ badge */
        .badge {
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 6px;
        }

        /* ปุ่มต่าง ๆ */
        .btn {
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #34d399;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #f87171;
            transform: translateY(-2px);
        }

        .btn-info {
            background: #0ea5e9;
            color: #fff;
        }

        .btn-info:hover {
            background: #38bdf8;
            transform: translateY(-2px);
        }

        /* กล่องข้อความแจ้งเตือน */
        .alert {
            background-color: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #22d3ee;
            border-radius: 8px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <h3><i class="bi bi-check2-square"></i> อนุมัติใบสั่งซื้อ (Purchase Orders)</h3>

        <div class="card-table">
            <?php if ($result->num_rows > 0): ?>
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>เลขที่ใบสั่งซื้อ</th>
                            <th>วันที่</th>
                            <th>ซัพพลายเออร์</th>
                            <th>ผู้จัดซื้อ</th>
                            <th>ราคารวม (บาท)</th>
                            <th>สถานะ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['po_no']) ?></strong></td>
                                <td><?= htmlspecialchars($row['po_date']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($row['buyer_name']) ?></td>
                                <td class="text-end"><?= number_format($row['grand_total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="po_no" value="<?= $row['po_no'] ?>">
                                        <button type="submit" name="action" value="approve"
                                            class="btn btn-success btn-sm"
                                            onclick="return confirm('ยืนยันการอนุมัติใบสั่งซื้อนี้หรือไม่?');">
                                            <i class="bi bi-check-circle"></i> อนุมัติ
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('ยืนยันการปฏิเสธใบสั่งซื้อนี้หรือไม่?');">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                                        </button>
                                    </form>
                                    <a href="purchase_view.php?po_no=<?= urlencode($row['po_no']); ?>"
                                        class="btn btn-info btn-sm mt-1">
                                        <i class="bi bi-eye"></i> ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert text-center p-4">
                    <i class="bi bi-inbox"></i> ไม่มีใบสั่งซื้อที่รอการอนุมัติ
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>