<?php
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_GET["id"];
$result = $conn->query("SELECT * FROM tasks WHERE id=$id");
$task = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $conn->query("UPDATE tasks SET title='$title' WHERE id=$id");
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"></head>
<body>

<h2>Sửa công việc</h2>

<form method="POST">
    <input type="text" name="title" value="<?= $task['title'] ?>" required>
    <button type="submit">Lưu</button>
</form>

</body>
</html>
