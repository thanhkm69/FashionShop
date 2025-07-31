<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}

$dir = "../../uploads/";
// Lấy dữ liệu liên hệ
$id = $_GET['id'];
$nd = $db->getOne("SELECT * FROM lienhe WHERE id = ?", [$id]);

// Xử lý khi gửi form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $traloi = $_POST["traloi"];
    $thoiGianTL = date("Y-m-d H:i:s");

    $db->execute("UPDATE lienhe SET trangThai = 1, traLoi = ?, thoiGianTL = ? WHERE id = ?", [
        $traloi,
        $thoiGianTL,
        $id
    ]);

    // Gửi email
    $subject = "Phản hồi liên hệ từ FashionShop";
    $body = '
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Phản hồi liên hệ</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
  <h2>FashionShop - Phản hồi liên hệ</h2>
  <p>Xin chào <strong>' . htmlspecialchars($nd["ten"]) . '</strong>,</p>
  <p>Chúng tôi đã nhận được liên hệ từ bạn với nội dung sau:</p>
  <blockquote style="background-color: #f2f2f2; padding: 10px; border-left: 4px solid #007bff;">
    ' . nl2br(htmlspecialchars($nd["noiDung"])) . '
  </blockquote>

  <p>Dưới đây là phản hồi từ đội ngũ hỗ trợ của chúng tôi:</p>
  <blockquote style="background-color: #e6ffe6; padding: 10px; border-left: 4px solid #28a745;">
    ' . nl2br(htmlspecialchars($_POST["traloi"])) . '
  </blockquote>

  <p>Nếu bạn còn bất kỳ câu hỏi nào khác, đừng ngần ngại liên hệ lại với chúng tôi.</p>
  <p>Trân trọng,<br>Đội ngũ FashionShop</p>
</body>
</html>';
    MailService::send($nd["email"], USERNAME_EMAIL, $subject, $body);
    header("Location: lienhe.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trả lời liên hệ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="container">
                    <h4 class="mb-4"><i class="bi bi-reply-fill text-primary me-2"></i> Trả lời liên hệ</h4>

                    <!-- Thông tin người liên hệ -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <!-- Cột trái -->
                                <div class="col-md-6">
                                    <p><i class="bi bi-person-fill me-2 text-primary"></i> <?= htmlspecialchars($nd["ten"]) ?></p>
                                    <p><i class="bi bi-envelope-fill text-warning me-2"></i> <?= htmlspecialchars($nd["email"]) ?></p>
                                </div>

                                <!-- Cột phải -->
                                <div class="col-md-6">
                                    <p><i class="bi bi-telephone-fill text-success me-2"></i> <?= htmlspecialchars($nd["sdt"]) ?></p>
                                    <p><i class="bi bi-clock-fill text-secondary me-2"></i> <?= date("d/m/Y H:i", strtotime($nd["thoiGian"])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Nội dung câu hỏi -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-chat-left-dots-fill text-primary me-2"></i> Nội dung
                            </h5>
                            <p class="card-text fs-6"><?= nl2br(htmlspecialchars($nd["noiDung"])) ?></p>
                        </div>
                    </div>

                    <!-- Form trả lời -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="traloi" class="form-label fw-semibold">
                                <i class="bi bi-reply-fill text-success me-2"></i> Nội dung phản hồi
                            </label>
                            <textarea name="traloi" id="traloi" rows="5" class="form-control" required><?= isset($nd["traLoi"]) ? htmlspecialchars($nd["traLoi"]) : '' ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send-check-fill me-1"></i> Gửi phản hồi
                            </button>
                            <a href="lienhe.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                            </a>
                        </div>
                    </form>

                </div>
            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>