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
$sql = "SELECT * FROM danhmuc";
$reslust = $db->getAll($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["create"])) {
        $nameProduct = test_input($_POST["nameProduct"]);
        $categoriesID = intval(test_input($_POST["categoriesID"]));
        $priceProduct = intval(test_input($_POST["priceProduct"]));
        $priceDiscount = !empty($_POST["priceDiscount"]) ? test_input($_POST["priceDiscount"]) : "";
        $commentProduct = test_input($_POST["commentProduct"]);

        validateEmpty($err, $nameProduct, "nameProduct");
        validateEmpty($err, $categoriesID, "categoriesID");
        validateEmpty($err, $priceProduct, "priceProduct");
        validateEmpty($err, $commentProduct, "commentProduct");

        $priceDiscount != "" && $priceDiscount >= $priceProduct ? $err["priceDiscount"] = "Giá khuyến mãi lớn hơn giá gốc" : "";

        $imgName = null;

        if (empty($_FILES["img"]["name"])) {
            $err["img"] = "Vui lòng chọn ảnh";
        } else {
            $imgName = validateImg($err, $dir, $_FILES["img"]);
        }
        if (empty($err) && $imgName != null) {
            if ($priceDiscount != "") {
                $insert = $db->execute(
                    "INSERT INTO sanpham(ten, idDanhMuc, giaGoc, hinh, moTa,giaKhuyenMai) VALUES (?, ?, ?, ?, ?, ?)",
                    [$nameProduct, $categoriesID, $priceProduct, $imgName, $commentProduct, $priceDiscount]
                );
            } else {
                $insert = $db->execute(
                    "INSERT INTO sanpham(ten, idDanhMuc, giaGoc, hinh, moTa) VALUES (?, ?, ?, ?, ?)",
                    [$nameProduct, $categoriesID, $priceProduct, $imgName, $commentProduct]
                );
            }
            $_SESSION["thongBao"] = "Thêm sản phẩm thành công";
            header("location: sanpham.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Giao diện gọn hơn -->
    <style>
        form.card.p-3 {
            max-width: 500px;
            margin: auto;
            padding: 1rem !important;
            border-radius: 0.75rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        form label {
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        form input.form-control {
            font-size: 0.9rem;
            padding: 0.4rem 0.5rem;
        }

        .card-header h6 i {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>

</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="container my-4">
                    <div class="card shadow-sm" style="max-width: 500px; margin: auto;">
                        <!-- Trong phần HTML -->
                        <div class="card-header bg-success text-white text-center py-2">
                            <h6 class="mb-0"> <i class="bi bi-box-seam me-2"></i>Thêm sản phẩm</h6>
                        </div>

                        <!-- Đoạn bên trong <form> -->
                        <form action="" method="post" enctype="multipart/form-data" class="p-3 border rounded bg-light shadow-sm">
                            <!-- Image -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-image me-1"></i>Ảnh sản phẩm</label>
                                <input type="file" class="form-control form-control-sm <?= isset($err["img"]) ? 'is-invalid' : '' ?>" name="img">
                                <?php if (isset($err["img"])): ?>
                                    <div class="invalid-feedback"><?= $err["img"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Product Name -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-box-seam me-1"></i>Tên sản phẩm</label>
                                <input type="text" class="form-control form-control-sm <?= isset($err["nameProduct"]) ? 'is-invalid' : '' ?>" name="nameProduct" value="<?= $nameProduct ?? '' ?>">
                                <?php if (isset($err["nameProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["nameProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Category -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-tags me-1"></i>Danh mục</label>
                                <select class="form-select form-select-sm <?= isset($err["categoriesID"]) ? 'is-invalid' : '' ?>" name="categoriesID">
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($reslust as $cat): ?>
                                        <option value="<?= $cat["id"]; ?>" <?= (isset($categoriesID) && $categoriesID == $cat["id"]) ? 'selected' : '' ?>>
                                            <?= $cat["ten"]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($err["categoriesID"])): ?>
                                    <div class="invalid-feedback"><?= $err["categoriesID"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Price -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-cash-stack me-1"></i>Giá gốc</label>
                                <input type="number" min="0" class="form-control form-control-sm <?= isset($err["priceProduct"]) ? 'is-invalid' : '' ?>" name="priceProduct" value="<?= $priceProduct ?? '' ?>">
                                <?php if (isset($err["priceProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["priceProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Discount Price -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-tag me-1"></i>Giá khuyến mãi</label>
                                <input type="number" min="0" class="form-control form-control-sm <?= isset($err["priceDiscount"]) ? 'is-invalid' : '' ?>" name="priceDiscount" value="<?= $priceDiscount ?? '' ?>">
                                <?php if (isset($err["priceDiscount"])): ?>
                                    <div class="invalid-feedback"><?= $err["priceDiscount"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Description -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-chat-text me-1"></i>Mô tả</label>
                                <textarea name="commentProduct" class="form-control form-control-sm <?= isset($err["commentProduct"]) ? 'is-invalid' : '' ?>" rows="2"><?= $commentProduct ?? '' ?></textarea>
                                <?php if (isset($err["commentProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["commentProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="sanpham.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                                </a>
                                <button type="submit" name="create" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Thêm
                                </button>
                            </div>
                        </form>
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