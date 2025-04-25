<?php 
session_start();  // 啟用 session

// 資料庫連線設定，請依照你的環境修改參數
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'taiwan_travel';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("資料庫連線失敗：" . $mysqli->connect_error);
}

// 登出邏輯
if (isset($_GET['logout'])) {
    session_destroy();  // 銷毀會話
    header("Location: index.php");  // 重定向到 index.php
    exit;
}

// 依據請求進行新增、更新、刪除處理
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 新增影片
    if (isset($_POST['new_video_title']) && isset($_POST['new_video_id'])) {
        $new_video_title = $mysqli->real_escape_string($_POST['new_video_title']);
        $new_video_id = $mysqli->real_escape_string($_POST['new_video_id']);
        $status = 1;  // 預設上架

        $sql = "INSERT INTO videos (title, video_id) VALUES ('$new_video_title', '$new_video_id')";

        if ($mysqli->query($sql)) {
            // 新增成功的處理
        } else {
            // 新增失敗的處理
        }
    }

    // 更新影片狀態
    if (isset($_POST['video_id']) && isset($_POST['status'])) {
        $video_id = (int) $_POST['video_id'];
        $status = (int) $_POST['status'];  // 0 或 1
        $sql = "UPDATE videos SET status = $status WHERE id = $video_id";
        if ($mysqli->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        }
        exit;
    }


    
    

    // 刪除影片
    if (isset($_POST['delete_video_id'])) {
        $delete_video_id = (int) $_POST['delete_video_id'];
        $sql = "DELETE FROM videos WHERE id = $delete_video_id";
        if ($mysqli->query($sql)) {
            echo json_encode(['status' => 'success', 'id' => $delete_video_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        }
        exit;
    }

    // 修改影片資料
    if (isset($_POST['edit_video_id']) && isset($_POST['edit_video_title']) && isset($_POST['edit_video_id_value'])) {
        $edit_video_id = (int) $_POST['edit_video_id'];
        $edit_video_title = $mysqli->real_escape_string($_POST['edit_video_title']);
        $edit_video_id_value = $mysqli->real_escape_string($_POST['edit_video_id_value']);
        $sql = "UPDATE videos SET title = '$edit_video_title', video_id = '$edit_video_id_value' WHERE id = $edit_video_id";
        if ($mysqli->query($sql)) {
            echo json_encode(['status' => 'success', 'id' => $edit_video_id, 'title' => $edit_video_title, 'video_id' => $edit_video_id_value]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        }
        exit;
    }
}

// 取得所有影片資料（由資料庫撈取）
$videos = [];
$sql = "SELECT id, title, video_id, status FROM videos";
if ($result = $mysqli->query($sql)) {
    while($row = $result->fetch_assoc()){
        $videos[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>影片管理 - 台灣到處走</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            font-family: 'Arial Rounded', sans-serif;
        }
        .btn-logout {
            background-color: #FF5733;
            border-color: #FF5733;
        }
        .btn-logout:hover {
            background-color: #FF4500;
            border-color: #FF4500;
        }

        .btn {
            border-radius: 15px;  /* 按鈕圓角 */
            border: none;
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
                        <a class="nav-link btn btn-logout text-white" href="logout.php">登出</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


        <!-- 新增影片表單 -->
        <div class="mb-4">
            <h4>影片管理</h4>
            <form id="add-video-form">
                <div class="mb-3">
                    <label for="new_video_title" class="form-label">影片標題</label>
                    <input type="text" class="form-control" id="new_video_title" name="new_video_title" required>
                </div>
                <div class="mb-3">
                    <label for="new_video_id" class="form-label">影片ID (YouTube ID)</label>
                    <input type="text" class="form-control" id="new_video_id" name="new_video_id" required>
                </div>
                <button type="submit" class="btn btn-primary">新增影片</button>
            </form>
        </div>

        <!-- 影片列表 -->
        <table class="table">
            <thead>
                <tr>
                    <th>影片標題</th>
                    <th>影片ID</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="video-list">
            <?php foreach ($videos as $video): ?>
                <tr data-id="<?php echo $video['id']; ?>">
                    <td><?php echo $video['title']; ?></td>
                    <td><?php echo $video['video_id']; ?></td>
                    <td>
                        <select class="form-select status-select" data-id="<?php echo $video['id']; ?>">
                            <option value="1" <?php echo ($video['status'] == 1) ? 'selected' : ''; ?>>上架</option>
                            <option value="0" <?php echo ($video['status'] == 0) ? 'selected' : ''; ?>>下架</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-warning edit-video" data-id="<?php echo $video['id']; ?>" data-title="<?php echo $video['title']; ?>" data-video_id="<?php echo $video['video_id']; ?>">修改</button>
                        <button class="btn btn-danger delete-video" data-id="<?php echo $video['id']; ?>">刪除</button>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

        <!-- 訊息顯示區 -->
        <div id="message" class="alert alert-info" style="display:none;"></div>

        <!-- 修改影片 Modal -->
        <div class="modal fade" id="editVideoModal" tabindex="-1" aria-labelledby="editVideoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editVideoModalLabel">修改影片</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="edit-video-form">
                            <div class="mb-3">
                                <label for="edit_video_title" class="form-label">影片標題</label>
                                <input type="text" class="form-control" id="edit_video_title" name="edit_video_title" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_video_id_value" class="form-label">影片ID (YouTube ID)</label>
                                <input type="text" class="form-control" id="edit_video_id_value" name="edit_video_id_value" required>
                            </div>
                            <input type="hidden" id="edit_video_id" name="edit_video_id">
                            <button type="submit" class="btn btn-primary">保存</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 刪除確認 Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="confirmDeleteModalLabel">確認刪除</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            你確定要刪除這部影片嗎？此操作無法復原。
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button type="button" class="btn btn-danger" id="modalDeleteButton">確認刪除</button>
        </div>
        </div>
    </div>
    </div>


    <script>
        $(document).ready(function() {
            // 更新影片狀態
            $('.status-select').change(function() {
                var video_id = $(this).data('id');
                var status = $(this).val();

                $.ajax({
                    url: 'admin.php',
                    type: 'POST',
                    data: {
                        video_id: video_id,
                        status: status
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status == 'success') {
                            $('#message').text('影片狀態已更新').show().fadeOut(3000);
                        }
                    }
                });
            });

            // 新增影片
            $('#add-video-form').submit(function(e) {
                e.preventDefault();  // 防止表單默認提交

                var title = $('#new_video_title').val();
                var video_id = $('#new_video_id').val();

                $.ajax({
                    url: 'admin.php',
                    type: 'POST',
                    data: {
                        new_video_title: title,
                        new_video_id: video_id
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status == 'success') {
                            // 顯示新增影片到影片列表中
                            var newRow = '<tr data-id="' + data.id + '">';
                            newRow += '<td>' + data.title + '</td>';
                            newRow += '<td>' + data.video_id + '</td>';
                            newRow += '<td>上架</td>';
                            newRow += '<td><button class="btn btn-warning edit-video" data-id="' + data.id + '" data-title="' + data.title + '" data-video_id="' + data.video_id + '">修改</button><button class="btn btn-danger delete-video" data-id="' + data.id + '">刪除</button></td>';
                            newRow += '</tr>';
                            $('#video-list').append(newRow);
                            $('#message').text('影片已新增').show().fadeOut(3000);
                        }
                    }
                });
            });

            // 顯示修改影片的 Modal
            $(document).on('click', '.edit-video', function() {
                var video_id = $(this).data('id');
                var title = $(this).data('title');
                var video_id_value = $(this).data('video_id');

                $('#edit_video_id').val(video_id);
                $('#edit_video_title').val(title);
                $('#edit_video_id_value').val(video_id_value);

                $('#editVideoModal').modal('show');
            });

            // 保存修改
            $('#edit-video-form').submit(function(e) {
                e.preventDefault();

                var video_id = $('#edit_video_id').val();
                var title = $('#edit_video_title').val();
                var video_id_value = $('#edit_video_id_value').val();

                $.ajax({
                    url: 'admin.php',
                    type: 'POST',
                    data: {
                        edit_video_id: video_id,
                        edit_video_title: title,
                        edit_video_id_value: video_id_value
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status == 'success') {
                            // 更新影片資料
                            $('tr[data-id="' + data.id + '"] td:eq(0)').text(data.title);
                            $('tr[data-id="' + data.id + '"] td:eq(1)').text(data.video_id);
                            $('#editVideoModal').modal('hide');
                            $('#message').text('影片已修改').show().fadeOut(3000);
                        }
                    }
                });
            });

            // 儲存待刪除影片的ID及所在列
            var deleteVideoId, deleteRow;

            // 當使用者點選刪除按鈕時，顯示確認 Modal
            $(document).on('click', '.delete-video', function() {
                deleteVideoId = $(this).data('id');
                deleteRow = $(this).closest('tr');
                $('#confirmDeleteModal').modal('show');
            });

            // 當使用者在 Modal 中確認刪除後，執行 AJAX 刪除動作
            $('#modalDeleteButton').click(function() {
                $.ajax({
                    url: 'admin.php',
                    type: 'POST',
                    data: {
                        delete_video_id: deleteVideoId
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status == 'success') {
                            deleteRow.remove();
                            $('#message').text('影片已刪除').show().fadeOut(3000);
                        }
                        $('#confirmDeleteModal').modal('hide');
                    }
                });
            });


        });
    </script>
</body>
</html>