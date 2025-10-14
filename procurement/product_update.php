<?php
require_once "../connect.php";

$id = (int)$_POST['product_id'];
$name = trim($_POST['product_name']);
$desc = trim($_POST['description']);
$qty = (float)$_POST['qty_onhand'];
$reorder = (float)$_POST['reorder_point'];
$price = (float)$_POST['unit_price'];
$uom = trim($_POST['uom']);
$cat = (int)$_POST['category_id'];

$stmt = $conn->prepare("
  UPDATE products 
  SET product_name=?, description=?, qty_onhand=?, reorder_point=?, 
      unit_price=?, uom=?, category_id=?, updated_at=NOW()
  WHERE product_id=?
");
$stmt->bind_param("ssddssii", $name, $desc, $qty, $reorder, $price, $uom, $cat, $id);
$stmt->execute();
$stmt->close();

header("Location: manage_products.php");
exit;
?>
