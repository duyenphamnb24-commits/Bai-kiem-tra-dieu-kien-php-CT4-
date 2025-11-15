<?php
// Cấu hình CSDL
$db_host = 'localhost';
$db_name = 'duynphm_todo_app';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES      => false,
];

try {
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
     die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Trợ giúp: chuyển empty -> NULL
function empty_to_null($value) {
	$trimmed_value = trim((string)$value);
   return ($trimmed_value === '' || $trimmed_value === null) ? NULL : $value;
}
?>
