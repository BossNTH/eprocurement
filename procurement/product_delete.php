<?php
require_once "../connect.php";
$id = (int)$_GET['id'];
if($id > 0){
  $conn->query("DELETE FROM products WHERE product_id=$id");
}
header("Location: manage_products.php");
exit;
?>
