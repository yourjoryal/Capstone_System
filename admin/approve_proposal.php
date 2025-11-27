<?php
$conn = new mysqli('localhost','root','','capstone_system');

$id = $_GET['id'];
$action = $_GET['action'] == "approve" ? "Approved" : "Rejected";

$conn->query("UPDATE proposals SET status='$action' WHERE id=$id");

header("Location: dashboard.php");
?>
