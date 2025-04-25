<?php
session_start();
require 'db_connect.php';

// 檢查使用者是否已登入
if (!isset($_SESSION['username'])) {
    
    echo "<script>
        alert('請登入會員！');
        window.location.href = 'index.php';
      </script>";

    exit();
}

// 從 Session 取得使用者名稱
$username = $_SESSION['username'];

// 查詢 user_id（改為使用 username）
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('找不到使用者資訊'); window.location.href = 'member_login.php';</script>";
    exit();
}
$user = $result->fetch_assoc();
$username = $user['username'];  // 這是你要的 username，而不是 user_id
$stmt->close();

// 1. 新增收藏
if (isset($_POST['action']) && $_POST['action'] == "add" && isset($_POST['video_id'])) {
    $video_id = $_POST['video_id'];  // 確保是整數

    // 檢查是否已收藏過該影片
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE username = ? AND video_id = ?");
    $stmt->bind_param("ss", $username, $video_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('此影片已在收藏列表！'); window.location.href = 'index.php';</script>";
    } else {
        // 執行插入收藏資料
        $stmt = $conn->prepare("INSERT INTO favorites (username, video_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $username, $video_id);
        if ($stmt->execute()) {
            echo "<script>alert('已加入收藏！'); window.location.href = 'index.php';</script>";
        } else {
            echo "<script>alert('加入收藏失敗！'); window.location.href = 'index.php';</script>";
        }
    }
    $stmt->close();
}

//刪除收藏影片
if (isset($_GET['action']) && $_GET['action'] == "remove" && isset($_GET['favorite_id'])) {
    $favorite_id = intval($_GET['favorite_id']);

    if ($favorite_id > 0) {
        $stmt = $conn->prepare("DELETE FROM favorites WHERE id = ?");
        $stmt->bind_param("i", $favorite_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<script>alert('成功刪除收藏！'); window.location.href = 'favorite_actions.php';</script>";
        } else {
            echo "<script>alert('刪除收藏失敗或該收藏不存在！'); window.location.href = 'favorite_actions.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('無效的收藏 ID'); window.location.href = 'favorite_actions.php';</script>";
    }
}



// 顯示使用者的收藏列表
$stmt = $conn->prepare("
    SELECT f.id AS favorite_id, v.title, f.video_id, f.created_at 
    FROM favorites f
    JOIN videos v ON f.video_id = v.video_id
    WHERE f.username = ?

");

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// ✅ 檢查查詢是否成功
if (!$result) {
    die("SQL 錯誤：" . $conn->error);
}


// 開始頁面輸出
echo '<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的收藏</title>
    <link rel="stylesheet" href="styles.css">  <!-- 引入外部CSS樣式 -->
</head>
<body>
    <div class="container">
        <h2>我的收藏</h2>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="favorite-item">
                <h3>' . htmlspecialchars($row['title']) . '</h3>
                <p><a href="https://www.youtube.com/watch?v=' . htmlspecialchars($row['video_id']) . '" target="_blank">觀看影片</a></p>
                <p class="date">收藏日期: ' . htmlspecialchars($row['created_at']) . '</p>
               <a href="favorite_actions.php?action=remove&favorite_id=' . $row['favorite_id'] . '" class="remove-btn" onclick="return confirm(\'確定刪除這個收藏嗎？\');">刪除</a>
              </div>';
    }
} else {
    echo "<p>目前還沒有任何收藏影片。</p>";
}
echo '<div style="text-align: center; margin-top: 30px;">
        <a href="index.php" style="
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        ">回首頁</a>
      </div>';

echo '</div>
</body>
</html>';

$stmt->close();
$conn->close();
?>
