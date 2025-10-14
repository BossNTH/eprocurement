<?php
include("../connect.php");
header('Content-Type: application/json; charset=utf-8');

$email = $_GET['email'] ?? '';
$exists = false;

if ($email !== '') {
  $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  $exists = $stmt->num_rows > 0;
  $stmt->close();
}

echo json_encode(['exists' => $exists]);
