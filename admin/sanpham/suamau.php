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

$idSanPham = test_input($_GET["id"]);
$idMau = test_input($_GET["idMau"]);

$mau = $db->getOne("SELECT * FROM mau WHERE id = ?", [$idMau]);
$reslust = $db->getAll("SELECT * FROM sanpham");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update"])) {
        $idMau = intval(test_input($_POST["idMau"]));
        $namemau = test_input($_POST["namemau"]);
        $commentmau = test_input($_POST["commentmau"]);
        $idSP = test_input($_POST["idSanPham"]);
        validateEmpty($err, $namemau, "namemau");
        validateEmpty($err, $commentmau, "commentmau");


        $imgmau = $mau["hinh"]; // lấy ảnh cũ

        if (!empty($_FILES["imgmau"]["name"]) && empty($err)) {
            $imgmau = validateImg($err, $dir, $_FILES["imgmau"]);
        }

        if (empty($err)) {
            $update = $db->execute(
                "UPDATE mau SET mau=?, idSanPham=?, moTaMau=?, hinh=? WHERE id = ?",
                [$namemau, $idSanPham, $commentmau, $imgmau, $idMau]
            );
            unlink($dir . $mau["hinh"]);
            $_SESSION["thongBao"] = "Cập nhật màu thành công";
            header("location: mausac.php?id=$idSanPham");
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
                            <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa màu sắc</h6>
                        </div>

                        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="idMau" value="<?= $mau["id"]; ?>">

                            <!-- Hình ảnh -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-image"></i> Hình ảnh
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $dir . $mau["hinh"]; ?>" class="preview-img" alt="">
                                    <div class="flex-grow-1">
                                        <input type="file" name="imgmau"
                                            class="form-control <?= isset($err["img"]) ? 'is-invalid' : '' ?>">
                                        <?php if (isset($err["img"])): ?>
                                            <div class="invalid-feedback"><?= $err["img"]; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tên sản phẩm -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-box-seam"></i>Màu</label>
                                <input value="<?= isset($namemau) ? $namemau : $mau["mau"]; ?>" type="text" name="namemau"
                                    class="form-control <?= isset($err["namemau"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["namemau"])): ?>
                                    <div class="invalid-feedback"><?= $err["namemau"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Sản phẩm -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-list-ul"></i>Sản phẩm</label>
                                <select name="idSanPham" class="form-select <?= isset($err["idSanPham"]) ? 'is-invalid' : '' ?>">
                                    <option value="">Chọn sản phẩm</option>
                                    <?php foreach ($reslust as $cat): ?>
                                        <option value="<?= $cat["id"]; ?>"
                                            <option value="<?= $cat["id"] ?>"
                                            <?php if((isset($idSP) && $idSP == $cat["id"])){ echo "selected";}elseif($cat["id"] == $mau["idSanPham"]){ echo "selected";}else{ echo "";}?>>
                                            <?= htmlspecialchars($cat["ten"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($err["idSanPham"])): ?>
                                    <div class="invalid-feedback"><?= $err["idSanPham"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-card-text"></i> Mô tả</label>
                                <textarea name="commentmau" rows="3"
                                    class="form-control <?= isset($err["commentmau"]) ? 'is-invalid' : '' ?>"><?= isset($commentmau) ? $commentmau : $mau["moTaMau"]; ?></textarea>
                                <?php if (isset($err["commentmau"])): ?>
                                    <div class="invalid-feedback"><?= $err["commentmau"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút -->
                            <div class="d-flex justify-content-between pt-3">
                                <a href="mausac.php?id=<?= $idSanPham ?>" class="btn btn-secondary">
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