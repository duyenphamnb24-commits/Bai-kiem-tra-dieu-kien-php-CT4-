<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

$error = '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = empty_to_null(trim($_POST['email'] ?? NULL));

    if (empty($username) || empty($password)) {
        $error = "Tên đăng nhập và mật khẩu không được để trống.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email]);
            header('Location: login.php?success=' . urlencode('Đăng ký thành công! Vui lòng đăng nhập.'));
            exit();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Tên đăng nhập hoặc Email đã tồn tại.";
            } else {
                $error = "Lỗi CSDL: " . $e->getMessage();
            }
        }
    }
}
// Trích xuất phần HTML View
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo tài khoản mới</title>
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
            <div class="card-header bg-success text-white text-center h5">Tạo tài khoản mới</div>
            <div class="card-body p-4">
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="reg_username" class="form-label fw-bold"><i class="fas fa-user-circle me-1"></i> Tên đăng nhập (*)</label>
                        <input type="text" class="form-control" id="reg_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_password" class="form-label fw-bold"><i class="fas fa-key me-1"></i> Mật khẩu (*)</label>
                        <input type="password" class="form-control" id="reg_password" name="password" required>
                    </div>
                    <div class="mb-4">
                        <label for="reg_email" class="form-label fw-bold"><i class="fas fa-envelope me-1"></i> Email (Tùy chọn)</label>
                        <input type="email" class="form-control" id="reg_email" name="email">
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg"><i class="fas fa-user-plus me-2"></i>Đăng ký</button>
                </form>
                <p class="mt-4 text-center">
                    Bạn đã có tài khoản? <a href="login.php" class="text-primary fw-bold">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
