<?php
session_start();

// 清除 session
session_unset();
session_destroy();

// 清除 cookie
setcookie('remember_user', '', time() - 3600, "/");  // 設定過期時間為過去的時間

// 跳轉回登入頁
header("Location: member_login.php");
exit;
?>
