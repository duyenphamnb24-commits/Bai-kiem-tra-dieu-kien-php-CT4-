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
        $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $current_user_id]);
        $task = $stmt->fetch();

        if ($task) {
            $new_status = ($task['status'] === 'completed') ? 'pending' : 'completed';
            // Logic từ file gốc: ưu tiên chuyển sang completed nếu đang in_progress
            if ($task['status'] === 'in_progress') {
                $new_status = 'completed';
            }

            $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$new_status, $task_id, $current_user_id]);
            header('Location: dashboard.php?success=' . urlencode('Cập nhật trạng thái thành công.'));
            exit();
        } else {
            header('Location: dashboard.php?error=' . urlencode('Không tìm thấy công việc hoặc không có quyền truy cập.'));
            exit();
        }
    } catch (\PDOException $e) {
        header('Location: dashboard.php?error=' . urlencode("Lỗi khi cập nhật trạng thái: " . $e->getMessage()));
        exit();
    }
} else {
     header('Location: dashboard.php');
     exit();
}
?>
