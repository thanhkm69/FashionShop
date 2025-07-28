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
$idSanPham = $_GET["id"];

$sp = $db->getOne("SELECT a.*, b.ten AS tenDM FROM sanpham a 
                        JOIN danhmuc b ON a.idDanhMuc = b.id 
                        WHERE a.id = ?", [$idSanPham]);

$bt = $db->getAll("SELECT a.*, b.size, b.soLuong, b.moTaSize 
                   FROM mau a 
                   JOIN bienthesize b ON a.id = b.idMau 
                   WHERE idSanPham = ?
                   ORDER BY a.mau
                   ", [$idSanPham]);
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
                        <span class="fs-5 fw-semibold align-text-bottom">Chi tiết sản phẩm</span>
                    </div>
                </div>

                <div class="card p-3">
                    <div class="row g-4">
                        <!-- Cột hình ảnh -->
                        <div class="col-md-3">
                            <img src="<?= $dir . $sp["hinh"] ?>" alt="<?= htmlspecialchars($sp["ten"]) ?>" class="product-image w-100">
                        </div>

                        <!-- Cột thông tin sản phẩm -->
                        <div class="col-md-9">
                            <h5 class="card-title"><?= htmlspecialchars($sp["ten"]) ?></h5>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item">
                                    <strong>Danh mục:</strong> <?= htmlspecialchars($sp["tenDM"]); ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Giá gốc:</strong> <?= number_format($sp["giaGoc"]); ?>₫
                                </li>
                                <li class="list-group-item">
                                    <strong>Giá khuyến mãi:</strong> <?= number_format($sp["giaKhuyenMai"]); ?>₫
                                </li>
                                <li class="list-group-item">
                                    <strong>Mô tả:</strong><br><?= nl2br(htmlspecialchars($sp["moTa"])) ?>
                                </li>
                            </ul>
                        </div>

                        <!-- Hàng mới: Biến thể sản phẩm -->
                        <div class="col-12">
                            <?php if (!empty($bt)): ?>
                                <div class="mt-3">
                                    <h6 class="text-primary fw-bold">Biến thể (Màu - Size):</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mt-2">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Hình</th>
                                                    <th>Màu</th>
                                                    <th>Mô tả màu</th>
                                                    <th>Size</th>
                                                    <th>Số lượng</th>
                                                    <th>Mô tả size</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bt as $item): ?>
                                                    <tr>
                                                        <td style="width: 60px;">
                                                            <img src="<?= $dir . htmlspecialchars($item["hinh"]) ?>" class="img-fluid" style="height: 50px;">
                                                        </td>
                                                        <td><?= htmlspecialchars($item["mau"]) ?></td>
                                                        <td><?= htmlspecialchars($item["moTaMau"]) ?></td>
                                                        <td><?= htmlspecialchars($item["size"]) ?></td>
                                                        <td><span class="badge bg-success"><?= $item["soLuong"] ?></span></td>
                                                        <td><?= htmlspecialchars($item["moTaSize"]) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-muted mt-3">
                                    <i class="bi bi-info-circle me-1"></i>Không có biến thể nào.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Nút quay lại -->
                        <div class="col-12 mt-4">
                            <a href="sanpham.php" class="btn btn-outline-secondary">
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