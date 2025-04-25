<?php
session_start();
session_destroy(); // 清除 session
header("Location: index.php");
exit;
?>
