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

// Dùng đúng tên cột trong DB
$voucher = $db->getOne("SELECT * FROM voucher WHERE id = ?", [$id]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["delete"])) {
        $db->execute("DELETE FROM voucher WHERE id = ?", [$id]);
        $_SESSION["thongBao"] = "Xóa voucher thành công";
        header("location: voucher.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xóa sản phẩm</title>
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

        .list-group-item i {
            width: 24px;
            display: inline-block;
            text-align: center;
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

<body style="background-color: #f1f3f5;">
    <div class="d-flex">
        <?php include '../include/sidebar.php'; ?>

        <div class="flex-grow-1 d-flex flex-column min-vh-100">
            <?php include '../include/header.php'; ?>

            <main class="flex-grow-1 py-4">
                <div class="container">
                    <div class="card-header bg-danger text-white text-center py-2 rounded-top">
                        <h6 class="mb-0"><i class="bi bi-trash3-fill me-2"></i>Xóa voucher</h6>
                    </div>

                    <div class="card p-4 shadow rounded-bottom">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-5 text-center">
                                <img src="<?php echo $dir . $voucher["hinh"]; ?>" alt="Product Image"
                                    class="img-thumbnail">
                            </div>

                            <div class="col-md-7">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <i class="bi bi-hash text-primary me-2"></i>
                                        <strong>ID:</strong> <?= $voucher["id"]; ?>
                                    </li>

                                    <li class="list-group-item">
                                        <i class="bi bi-ticket-perforated text-success me-2"></i>
                                        <strong>Mã voucher:</strong> <?= $voucher["ma"]; ?>
                                    </li>

                                    <li class="list-group-item">
                                        <i class="bi bi-percent text-warning me-2"></i>
                                        <strong>Loại giảm:</strong>
                                        <?= $voucher["kieu"] === "0" ? "Giảm theo số tiền" : "Giảm theo phần trăm"; ?>
                                    </li>

                                    <li class="list-group-item">
                                        <i class="bi bi-tag text-info me-2"></i>
                                        <strong>Giá trị giảm:</strong>
                                        <?= $voucher["kieu"] === "0"
                                            ? number_format($voucher["giam"]) . "₫"
                                            : $voucher["giam"] . "%"; ?>
                                    </li>

                                    <li class="list-group-item">
                                        <i class="bi bi-cash-coin text-success me-2"></i>
                                        <strong>Đơn tối thiểu:</strong> <?= number_format($voucher["donToiDa"]) ?> ₫
                                    </li>

                                    <?php if ($voucher["kieu"] == "1"): ?>
                                        <li class="list-group-item">
                                            <i class="bi bi-currency-dollar text-danger me-2"></i>
                                            <strong>Giảm tối đa:</strong>
                                            <?= $voucher["giamToiDa"] > 0
                                                ? number_format($voucher["giamToiDa"]) . " ₫"
                                                : "Không giới hạn"; ?>
                                        </li>
                                    <?php endif; ?>

                                    <li class="list-group-item">
                                        <i class="bi bi-calendar-date text-primary me-2"></i>
                                        <strong>Ngày bắt đầu:</strong> <?= $voucher["ngayBatDau"]; ?>
                                    </li>

                                    <li class="list-group-item">
                                        <i class="bi bi-calendar-date text-danger me-2"></i>
                                        <strong>Ngày kết thúc:</strong> <?= $voucher["ngayKetThuc"]; ?>
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
                                        <i class="bi bi-card-text text-muted me-2"></i>
                                        <strong>Mô tả:</strong> <?= $voucher["moTa"]; ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn xóa?')">
                            <div class="d-flex justify-content-between mt-4">
                                <a href="voucher.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-danger" name="delete">
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


</html>