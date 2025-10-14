<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/emp_header.php";


$emp_id = $_SESSION['employee_id'] ?? 0;

$stmt = $conn->prepare("
  SELECT pr_no, request_date, need_by_date, status
  FROM purchase_requisitions
  WHERE requested_by = ?
  ORDER BY created_at DESC
");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$requisitions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการขอซื้อสินค้า | ระบบจัดซื้อสมุนไพร</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    body { background-color: #0f172a; color: #e2e8f0; font-family: 'Prompt', sans-serif; margin:0; padding-left:260px; }
    .main-content { padding: 2rem; }
    .btn { background-color:#14b8a6; color:white; border:none; border-radius:6px; padding:8px 14px; text-decoration:none; }
    .btn:hover { background-color:#0d9488; }
    .btn-secondary { background-color:#334155; color:white; padding:6px 10px; border-radius:6px; text-decoration:none; }
    .btn-secondary:hover { background-color:#475569; }
    .card { background:#1e293b; border-radius:10px; padding:1.5rem; border:1px solid rgba(20,184,166,0.3); margin-bottom:2rem; }
    table { width:100%; border-collapse:collapse; background:#1e293b; border-radius:10px; overflow:hidden; }
    thead { background:#1e3a8a; color:#e0f2fe; }
    th,td { padding:.75rem 1rem; border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; }
    .badge { padding:4px 10px; border-radius:8px; font-size:.8rem; color:white; }
    .badge.draft{background:#64748b;} .badge.submitted{background:#facc15;color:#000;}
    .badge.manager_approved{background:#10b981;} .badge.rejected{background:#ef4444;}
  </style>
</head>
<body>
<div class="main-content">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h1 style="color:#5eead4"><i class="mdi mdi-file-document-box"></i> จัดการขอซื้อสินค้า</h1>
    <a href="pr_new.php" class="btn"><i class="mdi mdi-plus"></i> สร้างใบขอซื้อใหม่</a>
  </div>

  <div class="card">
    <h2 style="color:#a5f3fc;font-size:1.1rem;">📋 ใบขอซื้อสินค้าของฉัน</h2>
    <table>
      <thead>
        <tr><th>เลขที่ PR</th><th>วันที่ขอซื้อ</th><th>วันที่ต้องการ</th><th>สถานะ</th><th>การจัดการ</th></tr>
      </thead>
      <tbody>
        <?php if(empty($requisitions)): ?>
          <tr><td colspan="5" style="text-align:center;color:#94a3b8;">ยังไม่มีรายการขอซื้อ</td></tr>
        <?php else: foreach($requisitions as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['pr_no']) ?></td>
            <td><?= date("d/m/Y", strtotime($r['request_date'])) ?></td>
            <td><?= date("d/m/Y", strtotime($r['need_by_date'])) ?></td>
            <td><span class="badge <?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
            <td><a href="pr_view.php?pr_no=<?= urlencode($r['pr_no']) ?>" class="btn-secondary">ดู</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
