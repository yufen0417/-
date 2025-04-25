<?php
// 資料庫連線資訊
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'taiwan_travel';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 處理註冊表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 收集使用者輸入
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // 新增手機號碼
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // 檢查使用者名稱或信箱是否已存在
    $checkSql = "SELECT * FROM users WHERE username = ? OR email = ?";
    if ($stmt = $conn->prepare($checkSql)) {
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('註冊失敗，使用者名稱或電子郵件已存在！');</script>";
        } else {
            // 執行新增
            $sql = "INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssss", $username, $email, $phone, $password);
                if ($stmt->execute()) {
                    echo "<script>
                            alert('註冊成功！');
                            window.location.href = 'index.php';
                          </script>";
                } else {
                    echo "<script>alert('註冊失敗，請重試！');</script>";
                }
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .register-container h2 {
            text-align: center;
            color: #333;
        }
        .register-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .register-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .register-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>註冊</h2>
    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="使用者名稱" required>
        <input type="email" name="email" placeholder="電子郵件" required>
        <input type="text" name="phone" placeholder="手機號碼" required>
        <input type="password" name="password" placeholder="密碼" required>
        <button type="submit">註冊</button>
    </form>
</div>

</body>
</html>
