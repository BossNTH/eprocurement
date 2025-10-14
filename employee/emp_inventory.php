<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/emp_header.php";

// ดึงข้อมูลหมวดหมู่สินค้า
$categories = $conn->query("SELECT category_id, name FROM product_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// การกรองข้อมูล
$keyword = $_GET['search'] ?? '';
$cat = $_GET['cat'] ?? '';

$query = "
  SELECT p.product_id, p.product_name, p.qty_onhand, p.reorder_point,
         p.unit_price, p.uom, c.name AS category_name
  FROM products p
  LEFT JOIN product_categories c ON p.category_id = c.category_id
  WHERE 1=1
";
$params = [];
$types = "";

if (!empty($keyword)) {
  $query .= " AND p.product_name LIKE ?";
  $params[] = "%$keyword%";
  $types .= "s";
}

if (!empty($cat)) {
  $query .= " AND p.category_id = ?";
  $params[] = $cat;
  $types .= "i";
}

$query .= " ORDER BY c.name, p.product_name";

$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ดูสินค้าคงคลัง | ระบบจัดซื้อสมุนไพร</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
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

    h1 {
      color: #5eead4;
      font-size: 1.6rem;
      margin-bottom: 1.5rem;
    }

    .card {
      background: #1e293b;
      border-radius: 10px;
      padding: 1.5rem;
      border: 1px solid rgba(20,184,166,0.3);
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
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

    .low-stock {
      background-color: rgba(239, 68, 68, 0.2) !important;
    }

    input, select {
      background: #334155;
      border: none;
      border-radius: 6px;
      padding: 8px 10px;
      color: #e2e8f0;
    }

    .btn {
      background-color: #14b8a6;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn:hover {
      background-color: #0d9488;
    }

    .filter-box {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .price {
      color: #a5f3fc;
      font-weight: 500;
    }

    .category {
      font-size: 0.9rem;
      color: #94a3b8;
    }

  </style>
</head>
<body>

<div class="main-content">
  <h1><i class="mdi mdi-package-variant"></i> สินค้าคงคลัง</h1>

  <div class="card">
    <form method="GET" class="filter-box">
      <input type="text" name="search" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($keyword) ?>">
      <select name="cat">
        <option value="">-- ทุกหมวดหมู่ --</option>
        <?php foreach($categories as $c): ?>
          <option value="<?= $c['category_id'] ?>" <?= ($cat == $c['category_id'])?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn"><i class="mdi mdi-magnify"></i> ค้นหา</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ชื่อสินค้า</th>
          <th>หมวดหมู่</th>
          <th>จำนวนคงเหลือ</th>
          <th>จุดสั่งซื้อ</th>
          <th>หน่วย</th>
          <th>ราคาต่อหน่วย</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr><td colspan="6" style="text-align:center;color:#94a3b8;">ไม่พบข้อมูลสินค้า</td></tr>
        <?php else: ?>
          <?php foreach($products as $p): 
            $low = ($p['qty_onhand'] <= $p['reorder_point']);
          ?>
            <tr class="<?= $low ? 'low-stock' : '' ?>">
              <td><?= htmlspecialchars($p['product_name']) ?></td>
              <td class="category"><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
              <td><?= number_format($p['qty_onhand'], 2) ?></td>
              <td><?= number_format($p['reorder_point'], 2) ?></td>
              <td><?= htmlspecialchars($p['uom']) ?></td>
              <td class="price"><?= number_format($p['unit_price'], 2) ?> ฿</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
