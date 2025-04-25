<?php
// submit_testimonial.php

// 連線資料庫設定（請依據實際情況修改參數）
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'taiwan_travel';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("資料庫連線失敗：" . $mysqli->connect_error);
}

// 當為 POST 請求時處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 檢查必填欄位：評分、留言內容與留言類型
    if (isset($_POST['rating']) && isset($_POST['message']) && isset($_POST['message_option'])) {
        $rating = (int) $_POST['rating'];
        $message = trim($_POST['message']);
        $message_option = $_POST['message_option']; // "anonymous" 或 "named"
        $name = "匿名"; // 預設為匿名留言

        // 若為記名留言，必須提供姓名
        if ($message_option === "named") {
            if (empty($_POST['name'])) {
                $feedback = "請輸入您的姓名以便記名留言。";
            } else {
                $name = trim($_POST['name']);
            }
        }
        // 驗證評分是否在 1 到 5 之間
        if ($rating < 1 || $rating > 5) {
            $feedback = "錯誤：評分必須在 1 到 5 之間。";
        }

        // 若無錯誤訊息，則進行資料庫寫入
        if (!isset($feedback)) {
            $stmt = $mysqli->prepare("INSERT INTO testimonials (name, message, rating) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $name, $message, $rating);
            if ($stmt->execute()) {
                $feedback = "提交成功，感謝您的評分，我們會持續優化此平台";
            } else {
                $feedback = "錯誤：無法提交留言和評分。";
            }
            $stmt->close();
        }        
    } else {
        $feedback = "請填寫評分、留言及選擇留言類型";
    }
}

// 處理留言列表的篩選條件（採用 GET 傳入參數）
$where = [];
$params = [];
$types = "";

// 篩選「姓名」：依下拉選單選擇
if (isset($_GET['filter_name']) && $_GET['filter_name'] !== "") {
    if ($_GET['filter_name'] == "anonymous") {
        $where[] = "name = '匿名'";
    } elseif ($_GET['filter_name'] == "named") {
        $where[] = "name <> '匿名'";
    }
}
// 篩選「留言內容」：模糊比對
if (!empty($_GET['filter_message'])) {
    $where[] = "message LIKE ?";
    $params[] = "%" . $_GET['filter_message'] . "%";
    $types .= "s";
}
// 篩選「留言時間」：僅顯示所選日期以前的留言
if (!empty($_GET['filter_created_at'])) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $_GET['filter_created_at'];
    $types .= "s";
}
// 篩選「評分」：僅提供 1 至 5 星
if (isset($_GET['filter_rating']) && $_GET['filter_rating'] !== "" && is_numeric($_GET['filter_rating'])) {
    $where[] = "rating = ?";
    $params[] = (int)$_GET['filter_rating'];
    $types .= "i";
}

$sql = "SELECT name, message, created_at, rating FROM testimonials";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $bind_names = [];
    $bind_names[] = $types;
    foreach ($params as $key => $value) {
        $bind_names[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}
$stmt->execute();
$result = $stmt->get_result();
$testimonials = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>客戶使用口碑</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- 引入 Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

  <style>
      /* 全站標題樣式設定：圓潤、粗體、天藍色 */
      h1, h2, h3, h4, h5, h6 {
          font-family: "Arial Rounded MT Bold", "Helvetica Rounded", Arial, sans-serif;
          font-weight: bold;
          color: #87CEEB;
      }
      .star-rating i {
          font-size: 2rem;
          cursor: pointer;
          margin-right: 4px;
      }
      /* 統一按鈕、輸入框等元素的風格 */
      .btn-primary {
          background-color: #87CEEB;
          border-color: #87CEEB;
      }

      .btn {
            border-radius: 20px;  /* 按鈕圓角 */
            padding: 10px 30px;
            background-color: #63a9e1;  /* 天藍色背景 */
            color: white;
            border: none;
      }

      .btn-primary:hover {
          background-color: #6cbce6;
          border-color: #6cbce6;
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



  <div class="container mt-4">
    <h2>留下您的口碑</h2>
    <?php if(isset($feedback)): ?>
      <div class="alert alert-info"><?php echo $feedback; ?></div>
    <?php endif; ?>
    <form action="submit_testimonial.php" method="POST">
      <!-- 星級評分：以一組五顆星圖示呈現 -->
      <div class="mb-3">
        <label class="form-label">請選擇評分：</label>
        <div id="starRating" class="star-rating">
          <i class="bi bi-star" data-value="1"></i>
          <i class="bi bi-star" data-value="2"></i>
          <i class="bi bi-star" data-value="3"></i>
          <i class="bi bi-star" data-value="4"></i>
          <i class="bi bi-star" data-value="5"></i>
        </div>
        <!-- 使用隱藏欄位儲存評分值，預設為 5 -->
        <input type="hidden" name="rating" id="rating" value="5">
      </div>
      <!-- 留言內容 -->
      <div class="mb-3">
        <label for="message" class="form-label">留言內容：</label>
        <textarea class="form-control" name="message" id="message" rows="5" required></textarea>
      </div>
      <!-- 留言類型選擇 -->
      <div class="mb-3">
        <label class="form-label">留言類型：</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="message_option" id="anonymous" value="anonymous" checked>
          <label class="form-check-label" for="anonymous">匿名留言</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="message_option" id="named" value="named">
          <label class="form-check-label" for="named">記名留言</label>
        </div>
      </div>
      <!-- 記名留言時，輸入姓名 -->
      <div class="mb-3" id="nameInputDiv" style="display: none;">
        <label for="name" class="form-label">請輸入您的姓名：</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="您的姓名">
      </div>
      <button type="submit" class="btn btn-primary">提交留言</button>
    </form>

    <!-- 留言篩選區塊 -->
    <div class="mt-5">
      <h3>留言列表</h3>
      <form id="filterForm" class="row g-3 mb-3" method="GET" action="submit_testimonial.php">
        <!-- 改為下拉選單：依姓名篩選（全部/匿名/記名） -->
        <div class="col-md-3">
          <select name="filter_name" class="form-select">
            <option value="">全部</option>
            <option value="anonymous" <?php if(isset($_GET['filter_name']) && $_GET['filter_name'] == 'anonymous') echo 'selected'; ?>>匿名</option>
            <option value="named" <?php if(isset($_GET['filter_name']) && $_GET['filter_name'] == 'named') echo 'selected'; ?>>記名</option>
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" id="filter_message" name="filter_message" class="form-control" placeholder="依內容篩選" value="<?php echo isset($_GET['filter_message']) ? htmlspecialchars($_GET['filter_message']) : ''; ?>">
        </div>
        <div class="col-md-2">
          <input type="date" name="filter_created_at" class="form-control" placeholder="篩選該日期以前" value="<?php echo isset($_GET['filter_created_at']) ? htmlspecialchars($_GET['filter_created_at']) : ''; ?>">
        </div>

        <!-- 篩選「評分」：僅提供 1 至 5 星 -->
        <div class="col-md-2">
            <select name="filter_rating" class="form-select">
                <option value="">全部評分</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo (isset($_GET['filter_rating']) && $_GET['filter_rating'] == $i) ? 'selected' : ''; ?>>
                        <?php echo $i; ?> 星
                    </option>
                <?php endfor; ?>
            </select>

        </div>


        <div class="col-md-1">
          <button type="submit" class="btn btn-secondary w-100">篩選</button>
        </div>
        <div class="col-md-1">
          <!-- 重設按鈕直接跳回無篩選參數的頁面 -->
          <button type="button" class="btn btn-warning w-100" onclick="window.location.href='submit_testimonial.php'">重設</button>
        </div>
      </form>

      <!-- 留言列表 -->
      <table class="table table-striped">
        <thead>
          <tr>
            <th>姓名</th>
            <th>留言內容</th>
            <th>留言時間</th>
            <th>評分</th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($testimonials) > 0): ?>
            <?php foreach($testimonials as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['message']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                <?php
                // 根據 rating 數值呈現實心星星圖示
                for ($s = 1; $s <= $row['rating']; $s++) {
                    echo '<i class="fas fa-star" style="color:gold;"></i>';
                }                
                ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">目前沒有符合條件的留言</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div><!-- 留言篩選區塊結束 -->
  </div><!-- container 結束 -->

  <script>
    // 評分星星互動
    document.addEventListener("DOMContentLoaded", function() {
        const stars = document.querySelectorAll("#starRating i");
        const ratingInput = document.getElementById("rating");

        updateStars(ratingInput.value);
        stars.forEach(star => {
            star.addEventListener("click", function() {
            const ratingValue = this.getAttribute("data-value");
            ratingInput.value = ratingValue;
            updateStars(ratingValue);
            });
        });

        function updateStars(rating) {
            stars.forEach(star => {
            if (parseInt(star.getAttribute("data-value")) <= rating) {
                star.classList.remove("bi-star");
                star.classList.add("bi-star-fill");
                star.style.color = "gold";
            } else {
                star.classList.remove("bi-star-fill");
                star.classList.add("bi-star");
                star.style.color = "gray";
            }
            });
        }

        // 切換留言類型：記名留言時顯示姓名輸入欄位
        const anonymousRadio = document.getElementById("anonymous");
        const namedRadio = document.getElementById("named");
        const nameInputDiv = document.getElementById("nameInputDiv");

        function toggleNameInput() {
            if (namedRadio.checked) {
            nameInputDiv.style.display = "block";
            } else {
            nameInputDiv.style.display = "none";
            }
        }
        toggleNameInput();
        anonymousRadio.addEventListener("change", toggleNameInput);
        namedRadio.addEventListener("change", toggleNameInput);

        // 留言內容篩選：隨使用者輸入後，延遲 500ms 自動提交表單
        let filterTimeout;
        const filterMessageInput = document.getElementById("filter_message");
        filterMessageInput.addEventListener("input", function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
            document.getElementById("filterForm").submit();
            }, 500);
        });

        // 當按下 Enter 鍵時自動提交表單
        const filterForm = document.getElementById("filterForm");
        const filterSelects = filterForm.querySelectorAll("select, input[type='text'], input[type='date']");
        
        filterSelects.forEach(input => {
            input.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    filterForm.submit();
                }
            });
        });
    });

  </script>
</body>
</html>
