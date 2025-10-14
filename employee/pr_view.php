<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/emp_header.php";


$pr_no = $_GET['pr_no'] ?? '';
$stmt = $conn->prepare("
  SELECT pr_no, request_date, need_by_date, status
  FROM purchase_requisitions
  WHERE pr_no = ?
");
$stmt->bind_param("s", $pr_no);
$stmt->execute();
$pr = $stmt->get_result()->fetch_assoc();
$stmt->close();

$items = $conn->query("
  SELECT p.product_name, i.quantity, i.uom, i.need_by_date
  FROM pr_items i
  JOIN products p ON i.product_id = p.product_id
  WHERE i.pr_no = '$pr_no'
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดใบขอซื้อ | ระบบจัดซื้อสมุนไพร</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #e2e8f0;
            font-family: 'Prompt', sans-serif;
            margin: 0;
            padding-left: 260px;
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            background: #1e293b;
            border: 1px solid rgba(20, 184, 166, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        thead {
            background: #1e3a8a;
            color: #e0f2fe;
        }

        th,
        td {
            padding: .75rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .btn {
            color: #e2e8f0;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <h1 style="color:#5eead4">📄 รายละเอียดใบขอซื้อสินค้า</h1>

        <div class="card">
            <p><strong>เลขที่ใบขอซื้อ:</strong> <?= htmlspecialchars($pr['pr_no']) ?></p>
            <p><strong>วันที่ขอซื้อ:</strong> <?= date("d/m/Y", strtotime($pr['request_date'])) ?></p>
            <p><strong>วันที่ต้องการสินค้า:</strong> <?= date("d/m/Y", strtotime($pr['need_by_date'])) ?></p>
            <p><strong>สถานะ:</strong> <?= htmlspecialchars($pr['status']) ?></p>

            <h3 style="color:#a5f3fc;">รายการสินค้า</h3>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>หน่วย</th>
                        <th>ต้องการภายใน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['product_name']) ?></td>
                            <td><?= $i['quantity'] ?></td>
                            <td><?= htmlspecialchars($i['uom']) ?></td>
                            <td><?= date("d/m/Y", strtotime($i['need_by_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <a href="pr_manage.php" class="btn" style="background:#334155;">ย้อนกลับ</a>
        </div>
    </div>
</body>

</html>