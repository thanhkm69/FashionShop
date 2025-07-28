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
$categoriesID = $_GET["id"];
$category = $db->getOne("SELECT * FROM danhmuc WHERE id = ?", [$categoriesID]);

// Xử lý khi nhấn nút Xóa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteCategories"])) {
    if (!empty($category)) {
        // Xoá ảnh nếu tồn tại
        $imgPath = $dir . $category["hinh"];
        if (!empty($category["hinh"]) && file_exists($imgPath)) {
            unlink($imgPath);
        }

        // Xoá bản ghi trong database
        $db->execute("DELETE FROM danhmuc WHERE id = ?", [$categoriesID]);
        $_SESSION["thongBao"] = "Xóa danh mục thành công";
        header("location: danhmuc.php");
        exit();
    } else {
        $err = "Không tìm thấy danh mục";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xóa danh mục</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }

        .container {
            margin-top: 40px;
            max-width: 900px;
        }

        .card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }

        .img-thumbnail {
            width: 100%;
            max-width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.03);
        }

        .list-group-item {
            border: none;
            padding-left: 0;
            font-size: 16px;
            background-color: transparent;
        }

        h2.text-danger i {
            margin-right: 8px;
        }

        .btn {
            min-width: 120px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>

</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class=" flex-grow-1">
                <div class="container">
                    <div class="card-header bg-danger text-white text-center py-2">
                        <h6 class="mb-0"><i class="bi bi-trash3-fill me-2"></i>Xóa danh mục</h6>
                    </div>

                    <div class="card p-4 shadow">
                        <div class="row user-info mb-3">
                            <div class="col-md-5 d-flex align-items-center justify-content-center">
                                <img src="<?php echo  $dir . $category["hinh"]; ?>" class="img-thumbnail" alt="User Image">
                            </div>
                            <div class="col-md-7">
                                <ul class="list-group">
                                    <li class="list-group-item"><i class="bi bi-key"></i> <strong>ID:</strong> <?php echo $category["id"]; ?></li>
                                    <li class="list-group-item"><i class="bi bi-person-vcard"></i> <strong>Tên:</strong> <?php echo $category["ten"]; ?></li>
                                    <li class="list-group-item"><i class="bi bi-calendar-plus"></i><strong>Ngày tạo:</strong> <?= $category["ngayTao"]; ?></li>
                                </ul>
                            </div>
                        </div>

                        <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                            <div class="d-flex justify-content-between mt-3">
                                <a href="danhmuc.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-danger" name="deleteCategories">
                                    <i class="bi bi-trash3"></i> Xóa
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