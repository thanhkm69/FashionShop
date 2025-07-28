<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}

$categoryName = "";
$imgName = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["createCategory"])) {
    $categoryName = test_input($_POST["categoryName"] ?? "");
    $dir      = "../../uploads/";
    validateEmpty($err, $categoryName, "tên");

    if (empty($err)) {
        $exists = $db->getOne("SELECT * FROM danhmuc WHERE ten = ?", [$categoryName]);
        if ($exists) {
            $err["tên"] = "Tên danh mục đã tồn tại";
        }
    }

    $imgName = null;

    if (empty($_FILES["img"]["name"])) {
        $err["img"] = "Vui lòng chọn ảnh";
    } else {
        $imgName = validateImg($err, $dir, $_FILES["img"]);
    }

    if (empty($err) && $imgName != null) {
        $db->execute("INSERT INTO danhmuc (ten, hinh) VALUES (?, ?)", [
            $categoryName,
            $imgName
        ]);
        $_SESSION["thongBao"] = "Thêm danh mục thành công";
        header("Location: danhmuc.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm danh mục</title>
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
                            <h6 class="mb-0"><i class="bi bi-tags-fill me-2"></i>Thêm danh mục</h6>
                        </div>

                        <!-- Đoạn bên trong <form> -->
                        <form action="" method="post" enctype="multipart/form-data">
                            <!-- Image Upload -->
                            <div class="mb-3">
                                <label for="img" class="form-label">Hình ảnh</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                                    <input
                                        type="file"
                                        name="img"
                                        id="img"
                                        accept=".jpg,.jpeg,.png,.gif,.webp"
                                        class="form-control <?php echo isset($err['img']) ? 'is-invalid' : ''; ?>">
                                </div>
                                <?php if (isset($err["img"])): ?>
                                    <div class="invalid-feedback d-block">
                                        <?php echo $err["img"]; ?>
                                    </div>
                                <?php endif; ?>
                            </div>


                            <!-- Category Name -->
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Tên danh mục</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input
                                        type="text"
                                        name="categoryName"
                                        id="categoryName"
                                        class="form-control <?php echo isset($err['tên']) ? 'is-invalid' : ''; ?>"
                                        value="<?php echo htmlspecialchars($categoryName); ?>">
                                    <?php if (isset($err["tên"])): ?>
                                        <div class="invalid-feedback d-block">
                                            <?php echo $err["tên"]; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="danhmuc.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                                </a>
                                <button type="submit" name="createCategory" class="btn btn-success btn-sm">
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