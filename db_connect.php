<?php
$servername = "localhost"; // 通常 XAMPP 預設是 localhost
$username = "root";        // XAMPP 預設的 MySQL 帳號是 root
$password = "";            // 預設情況下 root 帳號密碼是空的
$database = "taiwan_travel"; // 你的資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $database);

// 檢查連線
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}
?>
