<?php
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Danh sách công việc</h2>

<a href="add.php">+ Thêm Task</a> | 
<a href="logout.php">Đăng xuất</a>

<table border="1" width="100%">
    <tr>
        <th>Nội dung</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
    </tr>

    <?php while ($row = $tasks->fetch_assoc()): ?>
    <tr>
        <td><?= $row["title"] ?></td>
        <td><?= $row["status"] ? "✔ Hoàn thành" : "✘ Chưa làm" ?></td>
        <td>
            <a href="toggle_status.php?id=<?= $row['id'] ?>">Đổi trạng thái</a> |
            <a href="edit_task.php?id=<?= $row['id'] ?>">Sửa</a> |
            <a href="delete_task.php?id=<?= $row['id'] ?>" onclick="return confirm('Xóa?')">Xóa</a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
