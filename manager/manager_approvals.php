<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/manager_header.php";

$manager_id = $_SESSION['employee_id'] ?? 0;

// ✅ อนุมัติคำขอซื้อ
if (isset($_POST['approve'])) {
    $pr_no = $_POST['pr_no'];
    $stmt = $conn->prepare("
        UPDATE purchase_requisitions
        SET status='APPROVE',
            manager_approved_by=?,
            manager_approved_at=NOW()
        WHERE pr_no=?
    ");
    $stmt->bind_param("is", $manager_id, $pr_no);
    $stmt->execute();
    echo "<script>alert('อนุมัติใบขอซื้อเรียบร้อยแล้ว'); window.location='manager_approvals.php';</script>";
    exit;
}

// ❌ ปฏิเสธคำขอซื้อ
if (isset($_POST['reject'])) {
    $pr_no = $_POST['pr_no'];
    $reason = trim($_POST['reason']);
    $stmt = $conn->prepare("
        UPDATE purchase_requisitions
        SET status='REJECTED',
            rejected_reason=?,
            manager_approved_by=?,
            manager_approved_at=NOW()
        WHERE pr_no=?
    ");
    $stmt->bind_param("sis", $reason, $manager_id, $pr_no);
    $stmt->execute();
    echo "<script>alert('ปฏิเสธใบขอซื้อเรียบร้อยแล้ว'); window.location='manager_approvals.php';</script>";
    exit;
}

// 🔍 ดึงรายการรออนุมัติ
$prs = $conn->query("
    SELECT pr.pr_no, pr.request_date, pr.need_by_date, e.full_name AS requester_name
    FROM purchase_requisitions pr
    JOIN employees e ON pr.requested_by = e.employee_id
    WHERE pr.status='DRAFT'
    ORDER BY pr.request_date DESC
")->fetch_all(MYSQLI_ASSOC);

// ดึงสินค้าในแต่ละใบ (สำหรับ popup modal)
function getPrItems($conn, $pr_no) {
    $stmt = $conn->prepare("
        SELECT p.product_name, i.quantity, i.uom, i.need_by_date
        FROM pr_items i
        JOIN products p ON i.product_id = p.product_id
        WHERE i.pr_no = ?
    ");
    $stmt->bind_param("s", $pr_no);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>อนุมัติคำขอซื้อ | ระบบจัดซื้อสมุนไพร</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    body { background:#0f172a; color:#e2e8f0; font-family:'Prompt',sans-serif; margin:0; padding-left:260px; }
    .main-content { padding:2rem; }
    h1 { color:#5eead4; font-size:1.6rem; margin-bottom:1.5rem; }
    .card { background:#1e293b; border-radius:10px; padding:1.5rem; border:1px solid rgba(20,184,166,0.3); margin-bottom:1.5rem; }
    table { width:100%; border-collapse:collapse; background:#1e293b; border-radius:10px; overflow:hidden; }
    thead { background:#1e3a8a; color:#e0f2fe; }
    th,td { padding:.75rem 1rem; border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; }
    tr:hover { background-color:rgba(20,184,166,0.1); }
    .btn { background:#14b8a6; color:white; border:none; border-radius:6px; padding:6px 12px; cursor:pointer; }
    .btn:hover { background:#0d9488; }
    .btn-danger { background:#dc2626; }
    .btn-danger:hover { background:#b91c1c; }
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); justify-content:center; align-items:center; }
    .modal-content { background:#1e293b; padding:1.5rem; border-radius:10px; width:500px; border:1px solid rgba(20,184,166,0.3); }
    .close { float:right; cursor:pointer; color:#94a3b8; font-size:1.4rem; }
    textarea { width:100%; background:#334155; color:#e2e8f0; border:none; border-radius:6px; padding:8px; margin-top:8px; }
  </style>
</head>
<body>

<div class="main-content">
  <h1><i class="mdi mdi-check-decagram-outline"></i> อนุมัติคำขอซื้อจากพนักงาน</h1>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>เลขที่ PR</th>
          <th>ผู้ขอซื้อ</th>
          <th>วันที่ขอซื้อ</th>
          <th>วันที่ต้องการสินค้า</th>
          <th>รายละเอียด</th>
          <th>การดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($prs)): ?>
          <tr><td colspan="6" style="text-align:center;color:#94a3b8;">ไม่มีรายการรออนุมัติ</td></tr>
        <?php else: foreach($prs as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['pr_no']) ?></td>
            <td><?= htmlspecialchars($r['requester_name']) ?></td>
            <td><?= date("d/m/Y", strtotime($r['request_date'])) ?></td>
            <td><?= date("d/m/Y", strtotime($r['need_by_date'])) ?></td>
            <td>
              <button class="btn" onclick="showModal('<?= $r['pr_no'] ?>')">ดูรายการ</button>
            </td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="pr_no" value="<?= $r['pr_no'] ?>">
                <button type="submit" name="approve" class="btn">อนุมัติ</button>
              </form>
              <button class="btn-danger" onclick="openReject('<?= $r['pr_no'] ?>')">ไม่อนุมัติ</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ===== Modal แสดงรายละเอียดสินค้า ===== -->
<?php foreach($prs as $r): 
  $items = getPrItems($conn, $r['pr_no']);
?>
<div class="modal" id="modal-<?= $r['pr_no'] ?>">
  <div class="modal-content">
    <span class="close" onclick="closeModal('<?= $r['pr_no'] ?>')">&times;</span>
    <h3 style="color:#5eead4;">รายละเอียดใบขอซื้อ: <?= $r['pr_no'] ?></h3>
    <table>
      <thead><tr><th>ชื่อสินค้า</th><th>จำนวน</th><th>หน่วย</th><th>ต้องการภายใน</th></tr></thead>
      <tbody>
        <?php foreach($items as $i): ?>
        <tr>
          <td><?= htmlspecialchars($i['product_name']) ?></td>
          <td><?= $i['quantity'] ?></td>
          <td><?= htmlspecialchars($i['uom']) ?></td>
          <td><?= date("d/m/Y", strtotime($i['need_by_date'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<!-- ===== Modal ปฏิเสธ ===== -->
<div class="modal" id="reject-modal">
  <div class="modal-content">
    <span class="close" onclick="closeReject()">&times;</span>
    <h3 style="color:#fca5a5;">ปฏิเสธใบขอซื้อ</h3>
    <form method="POST">
      <input type="hidden" name="pr_no" id="reject_pr_no">
      <label>เหตุผลในการปฏิเสธ:</label>
      <textarea name="reason" rows="3" required></textarea>
      <br><br>
      <button type="submit" name="reject" class="btn-danger">ยืนยันการปฏิเสธ</button>
      <button type="button" class="btn" onclick="closeReject()">ยกเลิก</button>
    </form>
  </div>
</div>

<script>
function showModal(id){ document.getElementById('modal-'+id).style.display='flex'; }
function closeModal(id){ document.getElementById('modal-'+id).style.display='none'; }

function openReject(pr_no){
  document.getElementById('reject_pr_no').value = pr_no;
  document.getElementById('reject-modal').style.display='flex';
}
function closeReject(){ document.getElementById('reject-modal').style.display='none'; }
</script>
</body>
</html>
