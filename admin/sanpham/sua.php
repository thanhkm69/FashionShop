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

$productID = test_input($_GET["id"]);

$db = new DB_UTILS();
$product = $db->getOne("SELECT * FROM sanpham WHERE id = ?", [$productID]);
$reslust = $db->getAll("SELECT * FROM danhmuc");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update"])) {
        $productID = intval(test_input($_POST["productID"]));
        $nameProduct = test_input($_POST["nameProduct"]);
        $categoriesID = test_input($_POST["categoriesID"]);
        $priceProduct = intval($_POST["priceProduct"]);
        $priceDiscount = $_POST["priceDiscount"];
        $commentProduct = test_input($_POST["commentProduct"]);
        $statusProduct = test_input($_POST["status"]);

        validateEmpty($err, $nameProduct, "nameProduct");
        validateEmpty($err, $categoriesID, "categoriesID");
        validateEmpty($err, $priceDiscount, "priceDiscount");
        validateEmpty($err, $priceProduct, "priceProduct");
        validateEmpty($err, $commentProduct, "commentProduct");

        $priceDiscount >= $priceProduct ? $err["priceDiscount"] = "Giá khuyến mãi lớn hơn giá gốc" : "";

        $imgProduct = $product["hinh"];
        $imgNew = null;

        if (!empty($_FILES["img"]["name"]) && empty($err)) {
            $imgNew = validateImg($err, $dir, $_FILES["img"]);
        }

        if (empty($err)) {
            if ($imgNew != null) {
                // Xóa ảnh cũ nếu có
                if (file_exists($dir . $product["hinh"])) {
                    unlink($dir . $product["hinh"]);
                }
                $imgProduct = $imgNew;
            }

            $update = $db->execute(
                "UPDATE sanpham SET ten=?, idDanhMuc=?, giaGoc=?, moTa=?, hinh=?, trangThai=?, giaKhuyenMai=? WHERE id=?",
                [$nameProduct, $categoriesID, $priceProduct, $commentProduct, $imgProduct, $statusProduct, $priceDiscount, $productID]
            );

            $_SESSION["thongBao"] = "Cập nhật sản phẩm thành công";
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
    <title>Sửa sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }

        .container {
            max-width: 600px;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .card-header h4 {
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }

        .form-control,
        .form-select {
            font-size: 0.88rem;
        }

        .btn {
            padding: 0.45rem 1.2rem;
            font-size: 0.88rem;
            border-radius: 0.45rem;
            min-width: 100px;
        }

        .preview-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 0.3rem;
        }

        .mb-3 {
            margin-bottom: 0.8rem !important;
        }

        .form-label i {
            margin-right: 6px;
            color: #0d6efd;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="flex-grow-1">
                <div class="container">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-center py-2">
                            <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa sản phẩm</h6>
                        </div>

                        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="productID" value="<?= $product["id"]; ?>">

                            <!-- Hình ảnh -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-image"></i> Hình ảnh
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $dir . $product["hinh"]; ?>" class="preview-img" alt="">
                                    <div class="flex-grow-1">
                                        <input type="file" name="img"
                                            class="form-control <?= isset($err["img"]) ? 'is-invalid' : '' ?>">
                                        <?php if (isset($err["img"])): ?>
                                            <div class="invalid-feedback"><?= $err["img"]; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tên sản phẩm -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-box-seam"></i> Tên sản phẩm</label>
                                <input value="<?= isset($nameProduct) ? $nameProduct : $product["ten"]; ?>" type="text" name="nameProduct"
                                    class="form-control <?= isset($err["nameProduct"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["nameProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["nameProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Danh mục -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-list-ul"></i> Danh mục</label>
                                <select name="categoriesID" class="form-select <?= isset($err["categoriesID"]) ? 'is-invalid' : '' ?>">
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($reslust as $cat): ?>
                                        <option value="<?= $cat["id"]; ?>"
                                            <?= (isset($categoriesID) && $categoriesID == $cat["id"]) || $cat["id"] == $product["idDanhMuc"] ? "selected" : "" ?>>
                                            <?= $cat["ten"]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($err["categoriesID"])): ?>
                                    <div class="invalid-feedback"><?= $err["categoriesID"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Giá gốc -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Giá gốc</label>
                                <input type="number" name="priceProduct" min="0"
                                    value="<?= isset($priceProduct) ? $priceProduct : $product["giaGoc"]; ?>"
                                    class="form-control <?= isset($err["priceProduct"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["priceProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["priceProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Giá khuyến mãi -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-tags"></i> Giá khuyến mãi</label>
                                <input type="number" name="priceDiscount" min="0"
                                    value="<?= isset($priceDiscount) ? $priceDiscount : $product["giaKhuyenMai"]; ?>"
                                    class="form-control <?= isset($err["priceDiscount"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["priceDiscount"])): ?>
                                    <div class="invalid-feedback"><?= $err["priceDiscount"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Trạng thái -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-eye"></i> Trạng thái</label>
                                <select name="status" class="form-select <?= isset($err["status"]) ? 'is-invalid' : '' ?>">
                                    <option value="1" <?= (isset($_POST["status"]) && $_POST["status"] == "1") || (!isset($_POST["status"]) && $product["trangThai"] == 1) ? "selected" : "" ?>>✅ Hiện</option>
                                    <option value="0" <?= (isset($_POST["status"]) && $_POST["status"] == "0") || (!isset($_POST["status"]) && $product["trangThai"] == 0) ? "selected" : "" ?>>❌ Ẩn</option>
                                </select>
                                <?php if (isset($err["status"])): ?>
                                    <div class="invalid-feedback"><?= $err["status"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-card-text"></i> Mô tả</label>
                                <textarea name="commentProduct" rows="3"
                                    class="form-control <?= isset($err["commentProduct"]) ? 'is-invalid' : '' ?>"><?= isset($commentProduct) ? $commentProduct : $product["moTa"]; ?></textarea>
                                <?php if (isset($err["commentProduct"])): ?>
                                    <div class="invalid-feedback"><?= $err["commentProduct"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút -->
                            <div class="d-flex justify-content-between pt-3">
                                <a href="sanpham.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                                </a>
                                <button type="submit" name="update" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Sửa
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>