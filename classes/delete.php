<?php
include('../includes/auth.php');
include('../config/db.php');

$id = $_GET['id'];
$conn->query("DELETE FROM classes WHERE id = $id");

header("Location: index.php");
exit();