<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Vui lòng đăng nhập để thực hiện thao tác.'));
    exit();
}

if (isset($_GET['id'])) {
    $current_user_id = (int)$_SESSION['user_id'];
    $task_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $current_user_id]);
        if ($stmt->rowCount() > 0) {
            header('Location: dashboard.php?success=' . urlencode('Xóa công việc thành công.'));
        } else {
            header('Location: dashboard.php?error=' . urlencode('Không tìm thấy công việc hoặc không có quyền xóa.'));
        }
        exit();
    } catch (\PDOException $e) {
        header('Location: dashboard.php?error=' . urlencode("Lỗi khi xóa: " . $e->getMessage()));
        exit();
    }
} else {
     header('Location: dashboard.php');
     exit();
}
?>
