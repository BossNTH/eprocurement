<?php
    $conn = new mysqli('localhost','root','','eprocurement');

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>