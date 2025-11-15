CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- PRIMARY KEY, AUTO_INCREMENT
    username VARCHAR(50) NOT NULL UNIQUE, -- UNIQUE
    password VARCHAR(255) NOT NULL, -- Lưu trữ mật khẩu đã băm (HASHED)
    email VARCHAR(100) NULL UNIQUE, -- UNIQUE, có thể NULL
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- time, now
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- PRIMARY KEY, AUTO_INCREMENT
    user_id INT(11) UNSIGNED NOT NULL, -- FOREIGN KEY liên kết với users.id
    title VARCHAR(255) NOT NULL, -- Tên công việc
    description TEXT NULL, -- Mô tả chi tiết, có thể NULL
    due_date DATE NULL, -- Ngày hết hạn, có thể NULL
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending', -- Trạng thái
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- time, now

    -- Thiết lập khóa ngoại (Foreign Key)
    CONSTRAINT fk_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE -- Xóa tất cả tasks khi user bị xóa
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
