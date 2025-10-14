<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/emp_header.php";

$emp_id = $_SESSION['employee_id'] ?? 0;

// ดึงรายการสินค้า
$products = $conn->query("SELECT product_id, product_name, qty_onhand, uom FROM products ORDER BY product_name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $need_by = $_POST['need_by_date'];
  $items = $_POST['product_id'];
  $qtys = $_POST['quantity'];

  // สร้างรหัส PR
  $pr_no = "PR" . date("ymdHis");
  $today = date("Y-m-d");

  $stmt = $conn->prepare("INSERT INTO purchase_requisitions (pr_no, request_date, need_by_date, requested_by, status) VALUES (?, ?, ?, ?, 'DRAFT')");
  $stmt->bind_param("sssi", $pr_no, $today, $need_by, $emp_id);
  $stmt->execute();

  $itemStmt = $conn->prepare("INSERT INTO pr_items (pr_no, product_id, quantity, unit_price, uom, need_by_date) VALUES (?, ?, ?, 0, ?, ?)");
  foreach ($items as $i => $pid) {
    if (!empty($pid) && $qtys[$i] > 0) {
      $uom = $conn->query("SELECT uom FROM products WHERE product_id=$pid")->fetch_assoc()['uom'];
      $itemStmt->bind_param("sidss", $pr_no, $pid, $qtys[$i], $uom, $need_by);
      $itemStmt->execute();
    }
  }
  $itemStmt->close();
  echo "<script>alert('สร้างใบขอซื้อเรียบร้อยแล้ว'); window.location='pr_manage.php';</script>";  
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>สร้างใบขอซื้อใหม่ | ระบบจัดซื้อสมุนไพร</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    body{background:#0f172a;color:#e2e8f0;font-family:'Prompt',sans-serif;margin:0;padding-left:260px;}
    .main-content{padding:2rem;}
    .card{background:#1e293b;border:1px solid rgba(20,184,166,0.3);border-radius:10px;padding:1.5rem;margin-bottom:2rem;}
    input,select{background:#334155;color:#e2e8f0;border:none;border-radius:6px;padding:8px 10px;margin-bottom:.5rem;width:100%;}
    .btn{background-color:#14b8a6;color:white;border:none;border-radius:6px;padding:8px 14px;cursor:pointer;}
    .btn:hover{background-color:#0d9488;}
  </style>
</head>
<body>
<div class="main-content">
  <h1 style="color:#5eead4">📝 สร้างใบขอซื้อใหม่</h1>

  <form method="POST">
    <div class="card">
      <label>วันที่ต้องการสินค้า:</label>
      <input type="date" name="need_by_date" required>

      <h3 style="color:#a5f3fc;">รายการสินค้า</h3>
      <div id="items">
        <div class="item">
          <select name="product_id[]" required>
            <option value="">-- เลือกสินค้า --</option>
            <?php foreach($products as $p): ?>
              <option value="<?= $p['product_id'] ?>"><?= $p['product_name'] ?> (คงเหลือ: <?= $p['qty_onhand'] ?> <?= $p['uom'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="quantity[]" min="1" placeholder="จำนวน" required>
        </div>
      </div>

      <button type="button" class="btn" onclick="addRow()">+ เพิ่มสินค้า</button>
      <hr>
      <button type="submit" class="btn">บันทึกใบขอซื้อ</button>
      <a href="pr_manage.php" class="btn" style="background:#334155;">ยกเลิก</a>
    </div>
  </form>
</div>

<script>
function addRow(){
  const item = document.createElement('div');
  item.classList.add('item');
  item.innerHTML = document.querySelector('.item').innerHTML;
  document.getElementById('items').appendChild(item);
}
</script>
</body>
</html>
