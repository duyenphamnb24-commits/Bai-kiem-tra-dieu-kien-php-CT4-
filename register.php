<?php
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE username = '$username'");

    if ($check->num_rows > 0) {
        $error = "Tên đăng nhập đã tồn tại!";
    } else {
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Đăng ký</h2>

<form method="POST">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Tạo tài khoản</button>
</form>

<p style="color:red"><?= $error ?></p>

</body>
</html>
