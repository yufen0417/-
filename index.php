<?php
session_start();

// 檢查是否有 'remember_user' cookie
if (isset($_COOKIE['remember_user']) && !isset($_SESSION['username'])) {
    // 假設 cookie 保存的是用戶名
    $_SESSION['username'] = $_COOKIE['remember_user'];
}

// -------------------------
// 登出邏輯：當 URL 參數 ?logout=true 時銷毀 Session 並重導回首頁
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// -------------------------
// 登入邏輯：如果有表單送出帳號與密碼，則進行驗證
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    // 請根據實際情況調整下列資料庫連線設定
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'taiwan_travel';
    
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        die("資料庫連線失敗：" . $mysqli->connect_error);
    }
    
    // 假設使用 users 資料表，欄位為 username 與 password（密碼使用 password_hash() 加密）
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    $stmt = $mysqli->prepare("SELECT username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_username, $db_password);
        $stmt->fetch();
        if (password_verify($password, $db_password)) {
            // 驗證成功，設定 Session
            $_SESSION["username"] = $db_username;
            // 可選：設定其他會員資訊到 Session
            header("Location: index.php");
            exit();
        } else {
            $error = "密碼錯誤，請重新輸入！";
        }
    } else {
        $error = "帳號不存在，請確認輸入的帳號！";
    }
    $stmt->close();
    $mysqli->close();
}

// -------------------------
// 以下為原本撈取影片資料的程式碼
// 若登入時已有建立資料庫連線，可考慮重複利用，不過為簡單起見，此處另行建立連線

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'taiwan_travel';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("資料庫連線失敗：" . $mysqli->connect_error);
}

// 從資料庫撈取狀態為「上架」的影片資料
$videos = [];
$status = 1;
$stmt = $mysqli->prepare("SELECT id, title, video_id, status FROM videos WHERE status = ?");
$stmt->bind_param("i", $status);
$stmt->execute();
$result = $stmt->get_result();
$videos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>台灣到處走</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .banner {
            width: 100%;
            height: 200px;
            background: url('banner.jpg') center/cover no-repeat;
            margin-bottom: -10px;
            padding: 0;
            display: block;
        }
        #travelCarousel {
            margin: 0;
            padding: 0;
        }
        #travelCarousel img {
            display: block;
            margin: 0 auto;
        }
        .video-card {
            text-align: center;
            margin-bottom: 30px;
        }
        .video-card iframe {
            width: 100%;
            height: 200px;
        }
        .qrcode {
            margin-top: 10px;
        }
        .custom-btn {
            background-color: #87CEEB;
            color: white;
            font-weight: bold;
            border-radius: 15px;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .custom-btn:hover {
            background-color: #6cbce6;
        }
    </style>
</head>
<body>
    <!-- Navbar 固定區塊 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" id="home-link">臺灣到處走</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#video-section">臺灣旅遊景點攻略介紹</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="submit_testimonial.php">客戶使用口碑</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">聯絡我們</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="favorite_actions.php">我的收藏</a></li>
                    <?php if (isset($_SESSION['username'])): ?>
                        <!-- 登入後顯示會員名稱與登出 -->
                        <li class="nav-item d-flex justify-content-center">
                        <span class="navbar-text">歡迎 <strong class="text-primary"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                        </li>


                        <li class="nav-item">
                            <a class="nav-link" href="index.php?logout=true">登出</a>
                        </li>
                    <?php else: ?>
                        <!-- 尚未登入時顯示登入按鈕，觸發 Modal -->
                        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#memberLoginModal">會員登入</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">登入</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 形象廣告輪播 -->
    <div id="travelCarousel" class="carousel slide container-fluid" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://wallpapercave.com/wp/wp7508057.jpg" class="d-block w-100" alt="">
            </div>
            <div class="carousel-item">
                <img src="https://photo.settour.com.tw/900x600/https://www.settour.com.tw/ss_img/info/location/TXG/G0/TXG0000107/TXG0000107_89150.jpg" class="d-block w-100" alt="">
            </div>
            <div class="carousel-item">
                <img src="https://youimg1.c-ctrip.com/target/fd/tg/g3/M02/54/90/CggYGVaHf0WAJSFsAChVkk304zA574.jpg" class="d-block w-100" alt="">
            </div>
        </div>
        <!-- 左右切換按鈕 -->
        <button class="carousel-control-prev" type="button" data-bs-target="#travelCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">上一張</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#travelCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">下一張</span>
        </button>
    </div>

    <!-- 影片展示區 -->
    <div id="video-section" class="container mt-4">
        <div class="row">
            <?php if(count($videos) > 0): ?>
                <?php foreach($videos as $video): ?>
                    <div class="col-md-4 video-card" id="video-<?php echo $video['id']; ?>">
                        <iframe src="https://www.youtube.com/embed/<?php echo $video['video_id']; ?>" frameborder="0" allowfullscreen></iframe>
                        <h5><?php echo $video['title']; ?></h5>
                        <p>瀏覽次數: <span id="view-count-<?php echo $video['id']; ?>">載入中...</span></p>
                        <button class="custom-btn" onclick="showQRCode('https://www.youtube.com/watch?v=<?php echo $video['video_id']; ?>')">瀏覽 QR Code</button>
                        <form action="favorite_actions.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>"> <!-- 這裡的 123 是影片 ID -->
                        <button type="submit">加入收藏</button>
                        </form>


                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>目前沒有影片上架。</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- 按鈕只出現在影片區塊底部 -->
        <div class="row mt-3">
            <div class="col-12 text-center">
                <button class="btn btn-secondary" onclick="scrollToTop()">返回上方首頁</button>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4 shadow-sm">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrCodeImage" src="" alt="QR Code" width="200" height="200">
                </div>
            </div>
        </div>
    </div>
    
    <!-- 會員登入 Modal (原本的 memberLoginModal)，這裡將 action 改為 index.php 以整合登入邏輯 -->
    <div class="modal fade" id="memberLoginModal" tabindex="-1" aria-labelledby="memberLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4 shadow-sm">
                <div class="modal-header">
                    <h4 class="modal-title" id="memberLoginModalLabel">會員登入</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 若有登入錯誤，顯示錯誤訊息 -->
                    <?php if(isset($error)): ?>
                        echo "<script>
                        alert('帳密輸入錯誤！');
                        window.location.href = 'index.php';</script>";

                    <?php endif; ?>
                    <form method="POST" action="index.php">
                        <input type="text" name="username" class="form-control mt-2" placeholder="帳號" required>
                        <input type="password" name="password" class="form-control mt-2" placeholder="密碼" required>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary w-100">登入</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>還沒有帳號嗎？ <a href="register.php">立即註冊</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 登入 Modal (授權碼登入，此 Modal 保留不變) -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4 shadow-sm">
                <div class="modal-header">
                    <h4 class="modal-title" id="loginModalLabel">請輸入授權碼登入</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="login.php">
                        <input type="password" name="authCode" class="form-control mt-2" placeholder="輸入授權碼">
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary w-100">登入</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 錯誤訊息 Modal (保留原版) -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4 shadow-sm">
                <div class="modal-body text-center">
                    <i class="bi bi-emoji-dizzy text-danger" style="font-size: 50px;"></i>
                    <p class="text-danger mt-3"><strong>錯誤：</strong> 授權碼錯誤，請重新輸入。</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 在頁面載入後初始化滾動效果
        $(document).ready(function() {
            // 若 URL 包含 error 參數，顯示錯誤 Modal
            if (window.location.search.indexOf('error=1') !== -1) {
                $('#errorModal').modal('show');
            }

            // 讓「臺灣旅遊景點攻略介紹」按鈕點擊時滾動到影片展示區
            $('a[href="#video-section"]').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('#video-section').offset().top
                }, 500);
            });

            // 初始化時載入所有影片的瀏覽次數
            <?php foreach($videos as $video): ?>
                fetchViewCount('<?php echo $video['video_id']; ?>', 'view-count-<?php echo $video['id']; ?>');
            <?php endforeach; ?>
        });

        $(document).ready(function() {
            // 當點擊「臺灣到處走」時，滾動至頁面最上方
            $('#home-link').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 500);
            });

            if (window.location.search.indexOf('error=1') !== -1) {
                $('#errorModal').modal('show');
            }
        });

        function scrollToTop() {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }

        function showQRCode(url) {
            var qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url);
            document.getElementById('qrCodeImage').src = qrCodeUrl;
            $('#qrCodeModal').modal('show');
        }

        function fetchViewCount(videoId, elementId) {
            $.get('fetch_view_count.php', { videoId: videoId }, function(data) {
                document.getElementById(elementId).innerText = data;
            });
        }

        $(document).ready(function() {
            <?php foreach($videos as $video): ?>
                fetchViewCount('<?php echo $video['video_id']; ?>', 'view-count-<?php echo $video['id']; ?>');
            <?php endforeach; ?>
            if (window.location.search.indexOf('error=1') !== -1) {
                $('#errorModal').modal('show');
            }
        });
    </script>
</body>
</html>
