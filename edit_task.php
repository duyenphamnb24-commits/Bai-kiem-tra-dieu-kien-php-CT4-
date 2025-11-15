<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Vui lòng đăng nhập để thực hiện thao tác.'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $current_user_id = (int)$_SESSION['user_id'];
    $task_id = (int)$_GET['id'];

    // Lấy dữ liệu từ form
    $title = trim($_POST['title'] ?? '');
    $description = empty_to_null(trim($_POST['description'] ?? NULL));
    $due_date = empty_to_null($_POST['due_date'] ?? NULL);
    $status = $_POST['status'] ?? 'pending';

    // Validate
    if (empty($title)) {
        header('Location: dashboard.php?error=' . urlencode('Tiêu đề không được để trống.'));
        exit();
    }

    try {
        $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $due_date, $status, $task_id, $current_user_id]);

        if ($stmt->rowCount() === 0) {
             header('Location: dashboard.php?error=' . urlencode('Cập nhật không thành công. Có thể công việc không tồn tại hoặc bạn không có quyền.'));
             exit();
        }

        header('Location: dashboard.php?success=' . urlencode('Chỉnh sửa công việc thành công!'));
        exit();
    } catch (\PDOException $e) {
        header('Location: dashboard.php?error=' . urlencode("Lỗi khi cập nhật: " . $e->getMessage()));
        exit();
    }
} else {
     header('Location: dashboard.php');
     exit();
}
?>
