<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
}else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}


$id = test_input($_GET["id"]);
$voucher = $db->getOne("SELECT * FROM voucher WHERE id = ?", [$id]);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $ma = test_input($_POST["ma"]);
    $kieu = test_input($_POST["kieu"]);
    $moTa = test_input($_POST["moTa"]);
    $giam = intval($_POST["giam"]);
    $donToiDa = intval($_POST["donToiDa"]);
    $giamToiDa = isset($_POST["giamToiDa"]) ? intval($_POST["giamToiDa"]) : 0;
    $ngayBatDau = $_POST["ngayBatDau"];
    $ngayKetThuc = $_POST["ngayKetThuc"];
    $soLuong = intval($_POST["soLuong"]);
    $trangThai = isset($_POST["trangThai"]) ? intval($_POST["trangThai"]) : 1;

    // Validate
    if ($ma == "") $err["ma"] = "Vui lòng nhập mã voucher";
    if ($kieu !== "0" && $kieu !== "1") $err["kieu"] = "Vui lòng chọn kiểu";
    if ($kieu == "1" && ($giam <= 0 || $giam > 100)) $err["giam"] = "Phần trăm giảm từ 1 - 100";
    if ($kieu == "0" && $giam <= 0) $err["giam"] = "Giá trị giảm phải > 0";
    if ($donToiDa < 0) $err["donToiDa"] = "Đơn tối đa không hợp lệ";
    if (!$ngayBatDau || !$ngayKetThuc) {
        $err["ngayBatDau"] = "Chọn ngày bắt đầu";
        $err["ngayKetThuc"] = "Chọn ngày kết thúc";
    } elseif ($ngayBatDau > $ngayKetThuc) {
        $err["ngayKetThuc"] = "Ngày kết thúc phải sau ngày bắt đầu";
    }
    if ($soLuong < 1) $err["soLuong"] = "Số lượng phải lớn hơn 0";

    if (empty($err)) {
        $check = $db->getOne("SELECT * FROM voucher WHERE ma = ? AND id != ?", [$ma, $id]);
        if (!$check) {
            $db->execute(
                "UPDATE voucher SET ma=?, kieu=?, moTa=?, giam=?, donToiDa=?, giamToiDa=?, ngayBatDau=?, ngayKetThuc=?, soLuong=?, trangThai=? WHERE id = ?",
                [$ma, $kieu, $moTa, $giam, $donToiDa, $giamToiDa, $ngayBatDau, $ngayKetThuc, $soLuong, $trangThai, $id]
            );
            $_SESSION["thongBao"] = "Cập nhật voucher thành công";
            header("location: voucher.php");
            exit;
        } else {
            $err["ma"] = "Mã voucher đã tồn tại";
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
                            <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa voucher</h6>
                        </div>

                        <form method="post" class="row g-3">
                            <input type="hidden" name="id" value="<?= $voucher["id"]; ?>">

                            <!-- Mã -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-ticket-perforated"></i> Mã</label>
                                <input name="ma" type="text" value="<?= $ma ?? $voucher["ma"] ?>" class="form-control <?= isset($err["ma"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["ma"])): ?><div class="invalid-feedback"><?= $err["ma"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Kiểu -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-percent"></i> Kiểu</label>
                                <select id="kieu" name="kieu" class="form-select <?= isset($err["kieu"]) ? 'is-invalid' : '' ?>" onchange="toggleGiamToiDa()">
                                    <option value="0" <?= ($kieu ?? $voucher["kieu"]) == 0 ? "selected" : "" ?>>Giảm theo tiền</option>
                                    <option value="1" <?= ($kieu ?? $voucher["kieu"]) == 1 ? "selected" : "" ?>>Giảm theo %</option>
                                </select>
                                <?php if (isset($err["kieu"])): ?><div class="invalid-feedback"><?= $err["kieu"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Giảm -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-cash-stack"></i> Giảm</label>
                                <input name="giam" type="number" value="<?= $giam ?? $voucher["giam"] ?>" class="form-control <?= isset($err["giam"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["giam"])): ?><div class="invalid-feedback"><?= $err["giam"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Đơn tối đa -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-cart-check"></i> Đơn tối đa</label>
                                <input name="donToiDa" type="number" value="<?= $donToiDa ?? $voucher["donToiDa"] ?>" class="form-control <?= isset($err["donToiDa"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["donToiDa"])): ?><div class="invalid-feedback"><?= $err["donToiDa"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Giảm tối đa -->
                            <div class="col-md-6" id="giamToiDaContainer" style="display: <?= ($kieu ?? $voucher["kieu"]) == 1 ? 'block' : 'none' ?>">
                                <label class="form-label"><i class="bi bi-cash-coin"></i> Giảm tối đa</label>
                                <input name="giamToiDa" type="number" value="<?= $giamToiDa ?? $voucher["giamToiDa"] ?>" class="form-control <?= isset($err["giamToiDa"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["giamToiDa"])): ?><div class="invalid-feedback"><?= $err["giamToiDa"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Ngày bắt đầu -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-calendar-plus"></i> Ngày bắt đầu</label>
                                <input name="ngayBatDau" type="date" value="<?= $ngayBatDau ?? $voucher["ngayBatDau"] ?>" class="form-control <?= isset($err["ngayBatDau"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["ngayBatDau"])): ?><div class="invalid-feedback"><?= $err["ngayBatDau"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Ngày kết thúc -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-calendar-minus"></i> Ngày kết thúc</label>
                                <input name="ngayKetThuc" type="date" value="<?= $ngayKetThuc ?? $voucher["ngayKetThuc"] ?>" class="form-control <?= isset($err["ngayKetThuc"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["ngayKetThuc"])): ?><div class="invalid-feedback"><?= $err["ngayKetThuc"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Số lượng -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-123"></i> Số lượng</label>
                                <input name="soLuong" type="number" value="<?= $soLuong ?? $voucher["soLuong"] ?>" class="form-control <?= isset($err["soLuong"]) ? 'is-invalid' : '' ?>">
                                <?php if (isset($err["soLuong"])): ?><div class="invalid-feedback"><?= $err["soLuong"]; ?></div><?php endif; ?>
                            </div>

                            <!-- Trạng thái -->
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-eye"></i> Trạng thái</label>
                                <select name="trangThai" class="form-select">
                                    <option value="1" <?= ($trangThai ?? $voucher["trangThai"]) == 1 ? 'selected' : '' ?>>✅ Hiện</option>
                                    <option value="0" <?= ($trangThai ?? $voucher["trangThai"]) == 0 ? 'selected' : '' ?>>❌ Ẩn</option>
                                </select>
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-card-text"></i> Mô tả</label>
                                <textarea name="moTa" rows="3" class="form-control"><?= $moTa ?? $voucher["moTa"] ?></textarea>
                            </div>

                            <!-- Nút -->
                            <div class="d-flex justify-content-between mt-3">
                                <a href="voucher.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Quay lại</a>
                                <button name="update" type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Cập nhật</button>
                            </div>
                        </form>


                    </div>
                </div>
            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleGiamToiDa() {
            const kieu = document.getElementById("kieu").value;
            const giamToiDaContainer = document.getElementById("giamToiDaContainer");
            giamToiDaContainer.style.display = kieu === "1" ? "block" : "none";
        }
    </script>

</body>

</html>