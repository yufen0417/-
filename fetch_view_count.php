<?php
function getYouTubeViewCount($videoId) {
    $url = "https://www.youtube.com/watch?v=" . $videoId;
    $html = file_get_contents($url);

    if ($html === false) {
        return "載入中..."; // 如果抓取失敗，顯示 "載入中..."
    }

    // 使用正規表達式從 HTML 中提取視訊瀏覽次數
    preg_match('/"viewCount":"([0-9]+)"/', $html, $matches);

    if (isset($matches[1])) {
        return number_format($matches[1]); // 返回格式化的瀏覽次數
    } else {
        return "載入中..."; // 如果無法提取瀏覽次數，顯示 "載入中..."
    }
}

// 根據影片ID返回瀏覽次數
if (isset($_GET['videoId'])) {
    echo getYouTubeViewCount($_GET['videoId']);
} else {
    echo "無法獲取瀏覽次數";
}
?>
