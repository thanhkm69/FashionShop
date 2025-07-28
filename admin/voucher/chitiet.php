<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
}else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}


$dir = "../../uploads/";
$id = $_GET["id"];

$voucher = $db->getOne("SELECT * FROM voucher WHERE id = ?", [$id]);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            min-height: 100vh;
        }

        .sidebar {
            background-color: #f1f4ff;
            border-right: 1px solid #e0e3f1;
            width: 260px;
        }

        .sidebar .nav-link {
            padding: 12px 18px;
            font-weight: 500;
            border-radius: 6px;
            color: #2c2e33;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #dc3545 !important;
            background-color: rgba(0, 0, 0, 0.03);
            transform: translateX(4px);
        }

        .product-image {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 0.5rem;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.08);
        }

        .list-group-item {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #f9fafb;
        }

        .sidebar .logo {
            width: 80%;
            margin: 0 auto 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }

            .main-content {
                padding: 1rem;
            }

            .product-image {
                max-height: 160px;
            }
        }
    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="action-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-box-seam fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Chi tiết voucher</span>
                    </div>
                </div>

                <!-- Trong file chi tiết voucher, sau phần header -->

                <div class="card p-3">
                    <div class="row g-4">
                        <!-- Cột hình ảnh -->
                        <div class="col-md-3">
                            <img src="<?= $dir . $voucher["hinh"] ?>" alt="Hình voucher" class="product-image w-100">
                        </div>

                        <!-- Cột thông tin -->
                        <div class="col-md-9">
                            <h5 class="card-title">
                                <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                                Mã voucher: <?= htmlspecialchars($voucher["ma"]) ?>
                            </h5>

                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item">
                                    <i class="bi bi-type me-2 text-success"></i>
                                    <strong>Loại giảm:</strong>
                                    <?= $voucher["kieu"] == "0" ? "Giảm theo số tiền" : "Giảm theo phần trăm"; ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-piggy-bank me-2 text-warning"></i>
                                    <strong>Giá trị giảm:</strong>
                                    <?= $voucher["kieu"] == "0"
                                        ? number_format($voucher["giam"]) . "₫"
                                        : $voucher["giam"] . "%"; ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-cart-check me-2 text-info"></i>
                                    <strong>Đơn tối thiểu:</strong> <?= number_format($voucher["donToiDa"]) ?> ₫
                                </li>

                                <?php if ($voucher["kieu"] == "1"): ?>
                                    <li class="list-group-item">
                                        <i class="bi bi-cash-coin me-2 text-danger"></i>
                                        <strong>Giảm tối đa:</strong>
                                        <?= $voucher["giamToiDa"] > 0
                                            ? number_format($voucher["giamToiDa"]) . " ₫"
                                            : "Không giới hạn"; ?>
                                    </li>
                                <?php endif; ?>

                                <li class="list-group-item">
                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <strong>Ngày bắt đầu:</strong> <?= date("d/m/Y", strtotime($voucher["ngayBatDau"])) ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-calendar-x me-2 text-danger"></i>
                                    <strong>Ngày kết thúc:</strong> <?= date("d/m/Y", strtotime($voucher["ngayKetThuc"])) ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-stack me-2 text-dark"></i>
                                    <strong>Số lượng:</strong> <?= $voucher["soLuong"] ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-check-circle me-2 text-success"></i>
                                    <strong>Trạng thái:</strong>
                                    <?= $voucher["trangThai"] == 1 ? "✅ Hiện" : "❌ Ẩn" ?>
                                </li>

                                <li class="list-group-item">
                                    <i class="bi bi-card-text me-2 text-muted"></i>
                                    <strong>Mô tả:</strong><br>
                                    <?= nl2br(htmlspecialchars($voucher["moTa"])) ?>
                                </li>
                            </ul>
                        </div>

                        <!-- Nút quay lại -->
                        <div class="col-12 mt-4">
                            <a href="voucher.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>

            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>