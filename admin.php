<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sales_order_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $new_password = 'admin123';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@gmail.com']);

    echo "Admin password successfully reset to: <b>admin123</b>. You can now <a href='index.php?action=login'>Login here</a>.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>