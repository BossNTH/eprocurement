<?php
require_once "../connect.php";

$name = trim($_POST['product_name']);
$desc = trim($_POST['description']);
$qty = (float)$_POST['qty_onhand'];
$reorder = (float)$_POST['reorder_point'];
$price = (float)$_POST['unit_price'];
$uom = trim($_POST['uom']);
$cat = (int)$_POST['category_id'];

$stmt = $conn->prepare("
  INSERT INTO products (product_name, description, qty_onhand, reorder_point, unit_price, uom, category_id)
  VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssddssi", $name, $desc, $qty, $reorder, $price, $uom, $cat);
$stmt->execute();
$stmt->close();

header("Location: manage_products.php");
exit;
?>
