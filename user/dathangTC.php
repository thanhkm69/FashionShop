<?php
require_once "../core/db_utils.php";

$idNguoiDung = $_SESSION["nguoiDung"]["id"] ?? null;
$ma = $_GET["ma"];
$dir = "../uploads/";
$dirMenu = "../";

if (!$idNguoiDung) {
    header("Location: ../dangnhap.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công | Cảm ơn bạn</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet" />
    <style>
        body {
            background: #f7fafc;
            font-family: 'Roboto', Arial, sans-serif;
        }

        .success-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-top: 64px;
            padding-bottom: 64px;
            background: #f7fafc;
        }

        .success-box {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px #0002;
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .success-check {
            font-size: 3.5rem;
            color: #2dc071;
            margin-bottom: 1.2rem;
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
            color: #22223b;
            margin-bottom: 0.8rem;
        }

        .success-message {
            font-size: 1.05rem;
            color: #495057;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .success-actions .btn {
            min-width: 130px;
            margin: 0 .5rem;
        }

        .success-container {
            padding: 0;
            min-height: 0;
        }

        @media (max-width: 500px) {
            .success-box {
                padding: 1.5rem;
            }

            .success-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php require_once "../include/header.php"; ?>
    <div class="container my-5">
        <div class="success-container">
            <div class="success-box">
                <div class="success-check">
                    <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="11" stroke="#2dc071" stroke-opacity="0.13" fill="#2dc07122"></circle>
                        <path d="M7 13.2l3 3.2 6-6.6" stroke="#2dc071" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="success-title">Cảm ơn bạn!</div>
                <div class="success-message">
                    Đơn hàng của bạn đã được đặt thành công.<br>
                    Chúng tôi đã nhận được thông tin và đang xử lý đơn hàng.<br>
                    <span class="text-success d-block mt-3 fw-medium">Mã đơn hàng: <span id="order-code"><?= htmlspecialchars($ma) ?></span></span>
                </div>
                <div class="success-actions d-flex justify-content-center flex-wrap">
                    <a href="<?= $dirMenu ?>sanpham.php" class="btn btn-outline-primary">Tiếp tục mua hàng</a>
                    <a href="<?= $dirMenu ?>index.php" class="btn btn-success">Về trang chủ</a>
                </div>
                <div class="text-muted small mt-4">
                    Cần hỗ trợ? <a href="<?= $dirMenu ?>lienhe.php" class="text-decoration-underline">Liên hệ bộ phận CSKH</a>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "../include/footer.php"; ?>
</body>

</html>