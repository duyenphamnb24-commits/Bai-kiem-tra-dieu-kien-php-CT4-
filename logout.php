<?php
session_start();
// Đảm bảo logout luôn xảy ra trước khi gửi output
session_unset();
session_destroy();
// Xóa cookie phiên (nếu cần)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Chuyển hướng người dùng về trang đăng nhập
header('Location: login.php?success=' . urlencode('Bạn đã đăng xuất thành công.'));
exit();
?>
