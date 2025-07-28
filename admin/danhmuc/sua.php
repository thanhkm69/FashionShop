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

if (isset($_GET["id"])) {
    $categoriesID = $_GET["id"];
    $category = $db->getOne("SELECT * FROM danhmuc WHERE id = ?", [$categoriesID]);
    if (!$category) {
        die("Không tìm thấy danh mục");
    }
} else {
    header("location: danhmuc.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["updateCategories"])) {
    $nameCategories = test_input($_POST["nameCategories"]);

    // Validate name
    if (empty($nameCategories)) {
        $err["nameCategories"] = "Vui lòng nhập tên";
    }



    $checkName = $db->getOne("SELECT * FROM danhmuc WHERE ten = ? AND id != ?", [$nameCategories, $categoriesID]);
    if ($checkName) $err["nameCategories"] = "Tên danh mục đã tồn tại";

    // Validate image
    $imgName = $category["hinh"]; // Default image
    if (empty($err) && !empty($_FILES["img"]["name"])) {
        $newImg = validateImg($err, $dir, $_FILES["img"]);
    }

    // Check duplicate name
    if (empty($err)) {
        if ($newImg != null) {
            if (file_exists($dir . $category["hinh"])) {
                unlink($dir . $category["hinh"]);
            }
            $update = $db->execute(
                "UPDATE danhmuc SET ten = ?, hinh = ? WHERE id = ?",
                [$nameCategories, $newImg, $categoriesID]
            );
        } else {
            $update = $db->execute(
                "UPDATE danhmuc SET ten = ? WHERE id = ?",
                [$nameCategories, $categoriesID]
            );
        }
        $_SESSION["thongBao"] = "Sửa danh mục thành công";
        header("location: danhmuc.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa danh mục</title>
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

        .error {
            color: red;
            font-size: 0.8rem;
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
                            <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa danh mục</h6>
                        </div>
                        <form action="" method="post" enctype="multipart/form-data" class="p-3">
                            <!-- ID -->

                            <input type="hidden" readonly class="form-control" name="categoriesID" id="categoriesID"
                                value="<?= $category["id"] ?>">

                           
                            <!-- Hình ảnh -->
                            <div class="mb-3">
                                <label class="form-label d-block">
                                    <i class="bi bi-image me-1"></i> Hình ảnh
                                </label>
                                <img src="<?= $dir . $category["hinh"] ?>" width="120" class="rounded shadow-sm mb-2">
                                <input type="file" name="img" class="form-control mt-2">
                                <div class="form-text">Nếu không thay đổi ảnh thì không cần chọn</div>
                                <?php if (isset($err["img"])): ?>
                                    <p class="text-danger mb-0"><?= $err["img"] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Tên danh mục -->
                            <div class="mb-3">
                                <label for="nameCategories" class="form-label">
                                    <i class="bi bi-tag-fill me-1"></i> Tên danh mục
                                </label>
                                <input type="text" class="form-control <?= isset($err["nameCategories"]) ? 'is-invalid' : '' ?>"
                                    name="nameCategories" id="nameCategories"
                                    value="<?= isset($nameCategories) ? $nameCategories : $category["ten"] ?>">
                                <?php if (isset($err["nameCategories"])): ?>
                                    <div class="invalid-feedback"><?= $err["nameCategories"] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-between pt-2">
                                <a href="danhmuc.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i>Quay lại
                                </a>
                                <button type="submit" name="updateCategories" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i>Sửa
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