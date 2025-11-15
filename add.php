<?php
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $uid = $_SESSION["user_id"];

    $conn->query("INSERT INTO tasks (user_id, title, status) VALUES ($uid, '$title', 0)");
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"></head>
<body>

<h2>Thêm công việc</h2>

<form method="POST">
    <input type="text" name="title" required placeholder="Nội dung công việc">
    <button type="submit">Thêm</button>
</form>

</body>
</html>
