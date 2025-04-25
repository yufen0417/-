<?php
session_start();  // 啟動 Session
require 'db_connect.php'; // 確保這個檔案正確連線到資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_username, $db_password);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                // 登入成功，設定 session 變數
                $_SESSION['username'] = $db_username;

                // 設定「記住我」功能的 cookie
                if (isset($_POST['remember_me'])) {
                    $cookie_name = 'remember_user';
                    $cookie_value = $db_username;  // 存儲用戶名，也可以加密後存儲
                    setcookie($cookie_name, $cookie_value, time() + (30 * 24 * 60 * 60), "/"); // 設置30天有效期
                }

                // 關閉資料庫連接
                $stmt->close();
                $conn->close();

                // 重定向到主頁
                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('密碼錯誤！'); window.location.href = 'member_login.php';</script>";
            }
        } else {
            echo "<script>alert('帳號不存在！'); window.location.href = 'member_login.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('請輸入帳號與密碼！'); window.location.href = 'member_login.php';</script>";
    }
    $conn->close();
}
?>
