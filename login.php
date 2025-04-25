<?php
session_start();

// 確認授權碼
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $authCode = $_POST['authCode'] ?? ''; // 防止未定義的變數警告

    if ($authCode === 'nfu') {
        // 登入成功，記錄 session
        $_SESSION['authenticated'] = true;
        header("Location: admin.php"); // 跳轉至後台
        exit;
    } else {
        // 授權碼錯誤，回傳錯誤訊息
        header("Location: index.php?error=1");
        exit;
    }
}

?>
