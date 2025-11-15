<?php
require_once "db.php";

$id = $_GET["id"];
$conn->query("UPDATE tasks SET status = NOT status WHERE id = $id");

header("Location: dashboard.php");
exit();
?>
