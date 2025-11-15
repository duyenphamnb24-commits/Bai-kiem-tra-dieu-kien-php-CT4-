<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

$error = '';
$success = $_GET['success'] ?? '';

// Nếu đã đăng nhập, chuyển hướng về dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Mật khẩu không đúng.";
            }
        } else {
            $error = "Tên đăng nhập không tồn tại.";
        }
    } catch (\PDOException $e) {
        $error = "Lỗi CSDL: " . $e->getMessage();
    }
}
// Trích xuất phần HTML View
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> /* ... CSS từ file gốc ... */ </style>
</head>
<body>
    <div class="container py-5">
        <header class="mb-5">
            <h1>📋 Ứng dụng Quản lý Công việc Cá nhân</h1>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card border-0 mx-auto" style="max-width: 420px;">
            <div class="card-header bg-primary text-white text-center h5">Đăng nhập tài khoản</div>
            <div class="card-body p-4">
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="login_username" class="form-label fw-bold"><i class="fas fa-user me-1"></i> Tên đăng nhập</label>
                        <input type="text" class="form-control" id="login_username" name="username" required>
                    </div>
                    <div class="mb-4">
                        <label for="login_password" class="form-label fw-bold"><i class="fas fa-lock me-1"></i> Mật khẩu</label>
                        <input type="password" class="form-control" id="login_password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</button>
                </form>
                <p class="mt-4 text-center">
                    Bạn chưa có tài khoản? <a href="register.php" class="text-success fw-bold">Đăng ký ngay!</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
