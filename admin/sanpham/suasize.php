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
$idSize = test_input($_GET["idSize"]);
$size = $db->getOne("SELECT * FROM bienthesize WHERE id = ?", [$idSize]);
$reslust = $db->getAll("SELECT * FROM mau");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update"])) {
        $idM = test_input($_POST["idM"]);
        $idSize = intval(test_input($_POST["idSize"]));
        $tenSize = test_input($_POST["tenSize"]);
        $soLuong = test_input($_POST["soLuong"]);
        $moTa = test_input($_POST["moTa"]);

        if ($idM == "") {
            $err["idM"] = "Vui lòng chọn màu size";
        }

        if ($tenSize == "") {
            $err["tenSize"] = "Vui lòng nhập tên size";
        }

        if ($soLuong == "") {
            $err["soLuong"] = "Vui lòng nhập số lượng";
        }

        if ($moTa == "") {
            $err["moTa"] = "Vui lòng nhập mô tả size";
        }

        if (empty($err)) {
            $update = $db->execute(
                "UPDATE bienthesize SET size=?, idMau=?, moTaSize=?, soLuong=? WHERE id = ?",
                [$tenSize, $idM, $moTa, $soLuong, $idSize]
            );
            $_SESSION["thongBao"] = "Cập nhật size thành công";
            header("location: size.php?id=" . $idSanPham . "&idMau=" . $idMau);
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
                            <input type="hidden" name="idSize" value="<?= $size["id"]; ?>">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-list-ul"></i>Màu</label>
                                <select name="idM" class="form-select <?= isset($err["idM"]) ? 'is-invalid' : '' ?>">
                                    <option value="">Chọn màu</option>
                                    <?php foreach ($reslust as $cat): ?>
                                        <option value="<?= $cat["id"]; ?>"
                                            <option value="<?= $cat["id"] ?>"
                                            <?php if ((isset($idM) && $idM == $cat["id"])) {
                                                echo "selected";
                                            } elseif ($cat["id"] == $size["idMau"]) {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>
                                            <?= htmlspecialchars($cat["mau"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($err["idM"])): ?>
                                    <div class="invalid-feedback"><?= $err["idM"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-box-seam"></i>Size</label>
                                <input value="<?= isset($tenSize) ? $tenSize : $size["size"]; ?>" type="text" name="tenSize"
                                    class="form-control <?= isset($err["tenSize"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["tenSize"])): ?>
                                    <div class="invalid-feedback"><?= $err["tenSize"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-box-seam"></i>Số lượng</label>
                                <input value="<?= isset($soLuong) ? $soLuong : $size["soLuong"]; ?>" type="text" name="soLuong"
                                    class="form-control <?= isset($err["soLuong"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["soLuong"])): ?>
                                    <div class="invalid-feedback"><?= $err["soLuong"]; ?></div>
                                <?php endif; ?>
                            </div>


                            <!-- Mô tả -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-card-text"></i> Mô tả</label>
                                <textarea name="moTa" rows="3"
                                    class="form-control <?= isset($err["moTa"]) ? 'is-invalid' : '' ?>"><?= isset($moTa) ? $moTa : $size["moTaSize"]; ?></textarea>
                                <?php if (isset($err["moTa"])): ?>
                                    <div class="invalid-feedback"><?= $err["moTa"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút -->
                            <div class="d-flex justify-content-between pt-3">
                                <a href="size.php?id=<?= $idSanPham ?>&idMau=<?= $idMau ?>" class="btn btn-secondary">
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