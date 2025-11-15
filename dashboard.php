<?php
session_start();
include_once 'db.php'; // Kết nối CSDL

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$is_logged_in = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? null;
$current_username = $_SESSION['username'] ?? null;

// Kiểm tra đăng nhập
if (!$is_logged_in) {
    header('Location: login.php?error=' . urlencode('Vui lòng đăng nhập để quản lý công việc.'));
    exit();
}

// --- Lấy danh sách công việc để hiển thị ---
$tasks = [];
$filter_status = $_GET['status'] ?? '';
$sort_by = $_GET['sort'] ?? 'due_date';
$current_user_id = (int)$current_user_id;

$sql = "SELECT id, title, description, due_date, status, created_at FROM tasks WHERE user_id = ?";
$params = [$current_user_id];

if (!empty($filter_status) && in_array($filter_status, ['pending', 'in_progress', 'completed'])) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

if ($sort_by === 'created_at') {
    $sql .= " ORDER BY created_at DESC";
} else {
    // ưu tiên các công việc chưa hoàn thành lên trước, null due_date xuống dưới
    $sql .= " ORDER BY FIELD(status, 'pending', 'in_progress', 'completed'), (due_date IS NULL) ASC, due_date ASC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error = "Lỗi khi tải công việc: " . $e->getMessage();
    $tasks = [];
}

// Trích xuất phần HTML của Dashboard từ file gốc
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sleek To-Do List - PHP</title>
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

        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <h4 class="mb-0 text-dark"><i class="fas fa-smile-beam text-warning me-2"></i>Xin chào, <span class="text-primary fw-bold"><?= htmlspecialchars($current_username) ?></span>!</h4>
            <a href="logout.php" class="btn btn-outline-danger btn-sm action-btn" title="Đăng xuất">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

        <div class="card mb-4 border-0">
            <div class="card-body">
                <button class="btn btn-brand-primary mb-3 me-3" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="fas fa-calendar-plus me-1"></i> Thêm Công Việc Mới
                </button>

                <div class="row align-items-center">
                    <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                        <span class="fw-bold me-2 text-secondary"><i class="fas fa-filter me-1"></i> Lọc theo Trạng thái:</span>
                        <?php
                            $build_url = function($status = '', $sort = '') use ($sort_by, $filter_status) {
                                $query_params = [];
                                $query_params['sort'] = empty($sort) ? $sort_by : $sort;
                                if (!empty($status)) {
                                    $query_params['status'] = $status;
                                }
                                return '?' . http_build_query($query_params);
                            };
                        ?>

                        <a href="<?= $build_url() ?>" class="btn btn-sm <?= empty($filter_status) ? 'btn-dark' : 'btn-outline-secondary' ?>">Tất cả</a>
                        <a href="<?= $build_url('pending') ?>" class="btn btn-sm <?= $filter_status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Chờ</a>
                        <a href="<?= $build_url('in_progress') ?>" class="btn btn-sm <?= $filter_status === 'in_progress' ? 'btn-info' : 'btn-outline-info' ?>">Đang làm</a>
                        <a href="<?= $build_url('completed') ?>" class="btn btn-sm <?= $filter_status === 'completed' ? 'btn-success' : 'btn-outline-success' ?>">Xong</a>
                    </div>

                    <div class="col-lg-5 col-md-12 text-lg-end">
                        <span class="fw-bold me-2 text-secondary"><i class="fas fa-sort me-1"></i> Sắp xếp theo:</span>
                        <a href="<?= $build_url($filter_status, 'due_date') ?>" class="btn btn-sm <?= $sort_by === 'due_date' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Ngày Hạn</a>
                        <a href="<?= $build_url($filter_status, 'created_at') ?>" class="btn btn-sm <?= $sort_by === 'created_at' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Ngày Tạo</a>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3 text-dark"><i class="fas fa-list-check me-2 text-primary"></i>Danh sách Công việc (<?= count($tasks) ?>)</h3>

        <?php if (empty($tasks)): ?>
            <div class="alert alert-info text-center shadow-sm">
                <i class="fas fa-check-double me-2"></i>Tuyệt vời! Bạn không có công việc nào cần làm, hoặc không có công việc nào khớp với bộ lọc.
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task['status'] === 'completed' ? 'completed' : '' ?>">
                        <div class="row align-items-center">
                            <div class="col-md-7 col-sm-12">
                                <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                                <?php if (!empty($task['description'])): ?>
                                    <p class="mb-0 task-meta fst-italic mt-1"><i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($task['description']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-2 col-sm-6 text-md-center mt-2 mt-md-0">
                                <div class="task-meta">
                                    <i class="far fa-calendar-alt me-1 text-primary"></i> Hạn:
                                    <span class="fw-bold text-dark"><?= $task['due_date'] ?? '[Không có]' ?></span>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 text-md-end text-start mt-2 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between align-items-center gap-2">
                                    <?php
                                        $badge_class = 'bg-secondary';
                                        $status_text = 'Unknown';
                                        if ($task['status'] === 'completed') { $badge_class = 'bg-success'; $status_text = '✅ Hoàn thành'; }
                                        elseif ($task['status'] === 'in_progress') { $badge_class = 'bg-info'; $status_text = '🔨 Đang làm'; }
                                        else { $badge_class = 'bg-warning text-dark'; $status_text = '⏳ Đang chờ'; }
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $status_text ?></span>

                                    <a href="change.php?id=<?= (int)$task['id'] ?>" class="btn action-btn <?= $task['status'] === 'completed' ? 'btn-success' : 'btn-outline-success' ?>" title="Đổi trạng thái">
                                        <i class="fas <?= $task['status'] === 'completed' ? 'fa-check' : 'fa-hourglass-start' ?>"></i>
                                    </a>

                                    <button class="btn action-btn btn-info text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editTaskModal"
                                        data-id="<?= (int)$task['id'] ?>"
                                        data-title="<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>"
                                        data-desc="<?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES) ?>"
                                        data-due="<?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES) ?>"
                                        data-status="<?= htmlspecialchars($task['status'], ENT_QUOTES) ?>"
                                        title="Chỉnh sửa"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <a href="delete_task.php?id=<?= (int)$task['id'] ?>"
                                        onclick="return confirm('Bạn chắc chắn muốn xóa công việc này?')"
                                        class="btn action-btn btn-danger"
                                        title="Xóa"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-brand-primary text-white">
                        <h5 class="modal-title" id="createTaskModalLabel"><i class="fas fa-plus-square me-2"></i>Tạo Công Việc Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="add.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="task_title" class="form-label fw-bold">Tiêu đề (*)</label>
                                <input type="text" class="form-control" id="task_title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="task_desc" class="form-label fw-bold">Mô tả (Tùy chọn)</label>
                                <textarea class="form-control" id="task_desc" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="task_due" class="form-label fw-bold">Ngày hết hạn (Tùy chọn)</label>
                                <input type="date" class="form-control" id="task_due" name="due_date">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-brand-primary"><i class="fas fa-save me-1"></i>Lưu lại</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="editTaskModalLabel"><i class="fas fa-pen-to-square me-2"></i>Chỉnh sửa công việc</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="edit_task.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_task_title" class="form-label fw-bold">Tiêu đề (*)</label>
                                <input type="text" class="form-control" id="edit_task_title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_task_desc" class="form-label fw-bold">Mô tả (Tùy chọn)</label>
                                <textarea class="form-control" id="edit_task_desc" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_task_due" class="form-label fw-bold">Ngày hết hạn (Tùy chọn)</label>
                                <input type="date" class="form-control" id="edit_task_due" name="due_date">
                            </div>
                            <div class="mb-3">
                                <label for="edit_task_status" class="form-label fw-bold">Trạng thái (*)</label>
                                <select class="form-control" id="edit_task_status" name="status" required>
                                    <option value="pending">⏳ Đang chờ</option>
                                    <option value="in_progress">🔨 Đang làm</option>
                                    <option value="completed">✅ Hoàn thành</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-info text-white"><i class="fas fa-upload me-1"></i>Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editTaskModal = document.getElementById('editTaskModal');
            if (editTaskModal) {
                editTaskModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;
                    var taskId = button.getAttribute('data-id');
                    var taskTitle = button.getAttribute('data-title') || '';
                    var taskDesc = button.getAttribute('data-desc') || '';
                    var taskDue = button.getAttribute('data-due') || '';
                    var taskStatus = button.getAttribute('data-status') || 'pending';

                    var modalTitle = editTaskModal.querySelector('.modal-title');
                    var modalForm = editTaskModal.querySelector('form');
                    var modalInputTitle = editTaskModal.querySelector('#edit_task_title');
                    var modalInputDesc = editTaskModal.querySelector('#edit_task_desc');
                    var modalInputDue = editTaskModal.querySelector('#edit_task_due');
                    var modalSelectStatus = editTaskModal.querySelector('#edit_task_status');

                    modalTitle.textContent = 'Chỉnh sửa công việc ID: ' + taskId;
                    // Cập nhật action để trỏ đến edit_task.php với ID
                    modalForm.action = 'edit_task.php?id=' + encodeURIComponent(taskId);
                    modalInputTitle.value = taskTitle;
                    modalInputDesc.value = taskDesc;
                    modalInputDue.value = taskDue;
                    modalSelectStatus.value = taskStatus;
                });
            }
        });
    </script>
</body>
</html>
