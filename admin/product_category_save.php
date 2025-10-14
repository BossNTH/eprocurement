<?php
session_start();

require_once("../connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO product_categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
}
header("Location: productCategoryManagement.php");
exit();
?>