<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/emp_header.php";

// ===== ดึงข้อมูล Dashboard ของพนักงาน =====
$emp_id = $_SESSION['employee_id'] ?? 0;

$total = $pending = $approved = $rejected = 0;

// นับจำนวน PR ตามสถานะ
$stmt = $conn->prepare("SELECT 
    COUNT(*) total,
    SUM(status='DRAFT') pending,
    SUM(status='APPROVE') approved,
    SUM(status='REJECTED') rejected
  FROM purchase_requisitions");
//$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($total, $pending, $approved, $rejected);
$stmt->fetch();
$stmt->close();

// รายการล่าสุด
$recent = [];
$stmt = $conn->prepare("
  SELECT pr_no, status, request_date
  FROM purchase_requisitions
  ORDER BY request_date DESC
  LIMIT 5
");
//$stmt->bind_param("i", $emp_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $recent[] = $row;
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แดชบอร์ดพนักงาน | ระบบจัดซื้อสมุนไพร</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #0f172a;
      color: #e2e8f0;
      font-family: 'Prompt', sans-serif;
      margin: 0;
      padding-left: 260px; /* เผื่อพื้นที่ sidebar */
    }

    .main-content {
      padding: 2rem;
    }

    .page-title {
      font-size: 1.6rem;
      font-weight: 600;
      color: #5eead4;
      margin-bottom: 1.5rem;
    }

    /* ==== Card style ==== */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 1rem;
    }

    .card {
      background: #1e293b;
      border: 1px solid rgba(20,184,166,0.2);
      border-radius: 10px;
      padding: 1.25rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      transition: 0.3s;
    }

    .card:hover {
      transform: translateY(-3px);
      border-color: #14b8a6;
    }

    .card h3 {
      color: #e0f2fe;
      font-size: 1.1rem;
      margin: 0;
    }

    .card .value {
      font-size: 2rem;
      font-weight: 600;
      color: #5eead4;
    }

    /* ==== Table style ==== */
    .recent-section {
      margin-top: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #1e293b;
      border-radius: 10px;
      overflow: hidden;
    }

    thead {
      background: #1e3a8a;
      color: #e0f2fe;
    }

    th, td {
      padding: 0.75rem 1rem;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    tbody tr:hover {
      background-color: rgba(20,184,166,0.1);
    }

    .badge {
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 0.8rem;
      color: white;
    }

    .badge.submitted { background: #facc15; color:#000; }
    .badge.manager_approved { background: #10b981; }
    .badge.rejected { background: #ef4444; }
    .badge.draft { background: #64748b; }
  </style>
</head>
<body>

<div class="main-content">
  <h1 class="page-title">📊 แดชบอร์ดพนักงาน</h1>

  <!-- KPI Cards -->
  <div class="card-grid">
    <div class="card">
      <h3>ใบขอซื้อทั้งหมด</h3>
      <div class="value"><?= $total ?></div>
    </div>
    <div class="card">
      <h3>รออนุมัติ</h3>
      <div class="value"><?= $pending ?></div>
    </div>
    <div class="card">
      <h3>อนุมัติแล้ว</h3>
      <div class="value"><?= $approved ?></div>
    </div>
    <div class="card">
      <h3>ไม่อนุมัติ</h3>
      <div class="value"><?= $rejected ?></div>
    </div>
  </div>

  <!-- Recent PR Table -->
  <div class="recent-section">
    <h2 style="color:#a5f3fc; margin-bottom:10px;">รายการขอซื้อล่าสุด</h2>
    <table>
      <thead>
        <tr>
          <th>เลขที่ PR</th>
          <th>วันที่สร้าง</th>
          <th>สถานะ</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($recent)): ?>
          <tr><td colspan="3" style="text-align:center;color:#94a3b8;">ไม่มีข้อมูล</td></tr>
        <?php else: ?>
          <?php foreach($recent as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['pr_no']) ?></td>
              <td><?= date("d/m/Y", strtotime($r['request_date'])) ?></td>
              <td>
                <span class="badge <?= strtolower($r['status']) ?>">
                  <?= htmlspecialchars($r['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
