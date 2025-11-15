<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Vui lòng đăng nhập để thực hiện thao tác.'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = (int)$_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = empty_to_null(trim($_POST['description'] ?? NULL));
    $due_date = empty_to_null($_POST['due_date'] ?? NULL);

    if (empty($title)) {
        header('Location: dashboard.php?error=' . urlencode('Tiêu đề không được để trống.'));
        exit();
    }

    try {
        $sql = "INSERT INTO tasks (user_id, title, description, due_date, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$current_user_id, $title, $description, $due_date]);
        header('Location: dashboard.php?success=' . urlencode('Thêm công việc thành công!'));
        exit();
    } catch (\PDOException $e) {
        header('Location: dashboard.php?error=' . urlencode("Lỗi khi thêm: " . $e->getMessage()));
        exit();
    }
} else {
     header('Location: dashboard.php');
     exit();
}
?>
