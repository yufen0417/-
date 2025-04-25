<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 連線資料庫
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'taiwan_travel';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("資料庫連線失敗：" . $mysqli->connect_error);
}

// 確保是 POST 請求
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // 插入資料
    $stmt = $mysqli->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    if ($stmt->execute()) {
        echo "<script>alert('留言已送出！'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('發生錯誤，請稍後再試！'); window.location.href='index.php';</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>聯絡我們</title>
    <!-- 加入 Bootstrap 5 的樣式 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 自訂 CSS，讓表單置中，並縮小輸入框 */
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 20px;  /* 輸入框圓角 */
            border: 1px solid #ccc;
        }

        .btn {
            border-radius: 20px;  /* 按鈕圓角 */
            padding: 10px 30px;
            background-color: #63a9e1;  /* 天藍色背景 */
            color: white;
            border: none;
        }

        .btn:hover {
            background-color: #4f8cbf;  /* 按鈕滑鼠懸停時顏色變化 */
        }

        .form-label {
            font-size: 18px;
            font-weight: bold;
            color: #5c5c5c;
            font-family: 'Arial', sans-serif;  /* 圓潤字體 */
        }

        .header-text {
            font-family: 'Arial', sans-serif;  /* 圓潤字體 */
            color: #63a9e1;
            font-weight: bold;  /* 標題加粗 */
        }

        /* 可愛風格的背景 */
        body {
            background-color: #f1f1f1;
        }

        .container {
            margin-top: 50px;
        }

        .contact-info {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 15px;  /* 圓潤外框 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;  /* 恢復原始範圍 */
            margin: 0 auto;  /* 置中 */
        }

        .contact-info h4 {
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            color: #63a9e1;
            text-align: center;  /* 置中標題 */
        }

        .contact-info p {
            font-family: 'Arial', sans-serif;
            font-size: 16px;
            color: #5c5c5c;
            text-align: center;  /* 置中文字 */
            margin-bottom: 5px;  /* 縮小每項資料的間距 */
        }

        .navbar-brand {
          font-family: "Arial Rounded MT Bold", "Helvetica Rounded", Arial, sans-serif;
          font-weight: bold;
        }
    </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">臺灣到處走</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link btn btn-primary text-white" href="index.php">返回首頁</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container">
    <h1 class="text-center mb-4 header-text">聯絡我們</h1>

    <div class="form-container">
        <form action="contact.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">姓名</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">電子郵件</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">留言內容</label>
                <textarea class="form-control" name="message" id="message" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn w-100">送出</button>
        </form>
    </div>

    <!-- 台灣到處走的聯絡資訊 -->
    <div class="contact-info mt-5">
        <h4>台灣到處走聯絡資訊</h4>
        <p><strong>公司名稱：</strong>虎科資訊股份有限公司</p>
        <p><strong>公司地址：</strong>台北市中山區建國北路二段120號</p>
        <p><strong>電子郵件：</strong>contact@taiwantravel.com</p>
        <p><strong>連絡電話：</strong>02-1234-5678</p>
    </div>
</div>

<!-- 引入 Bootstrap 5 的腳本 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
