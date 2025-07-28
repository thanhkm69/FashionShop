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
$sql = "SELECT * FROM voucher";
$reslust = $db->getAll($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["create"])) {
        $ma = test_input($_POST["ma"]);
        $kieu = test_input($_POST["kieu"]);
        $moTa = test_input($_POST["moTa"]);
        $giam = intval($_POST["giam"]);
        $donToiDa = intval($_POST["donToiDa"]);
        $giamToiDa = intval($_POST["giamToiDa"]);
        $ngayBatDau = date($_POST["ngayBatDau"]);
        $ngayKetThuc = date($_POST["ngayKetThuc"]);
        $soLuong = intval($_POST["soLuong"]);

        // Validate cơ bản
        if ($ma == "") {
            $err["ma"] = "Vui lòng nhập mã voucher";
        }
        if ($kieu !== "0" && $kieu !== "1") {
            $err["kieu"] = "Vui lòng chọn kiểu giảm giá";
        }
        if (($giam <= 0) && $kieu != "1") {
            $err["giam"] = "Giá trị giảm phải lớn hơn 0";
        }

        if ($kieu == "1" && ($giam < 0 || $giam > 100)) {
            $err["giam"] = "Giá trị giảm phần trăm từ 1 đến 100";
        }

        if ($donToiDa < 0) {
            $err["donToiDa"] = "Đơn tối thiểu không hợp lệ";
        }
        if ($kieu == "1") {
            if (empty($giamToiDa)) {
                $giamToiDa = 0;
            } else {
                $giamToiDa = trim($giamToiDa);
            }
        } else {
            $giamToiDa = 0; // Nếu không phải phần trăm thì để = 0
        }
        if (!$ngayBatDau || !$ngayKetThuc) {
            $err["ngayBatDau"] = "Vui lòng chọn ngày";
            $err["ngayKetThuc"] = "Vui lòng chọn ngày";
        } elseif ($ngayBatDau > $ngayKetThuc) {
            $err["ngayKetThuc"] = "Ngày kết thúc phải sau ngày bắt đầu";
        }
        if ($soLuong < 1) {
            $err["soLuong"] = "Số lượng phải lớn hơn 0";
        }

        if (empty($err)) {
            $check = $db->getOne("SELECT * FROM voucher WHERE ma = ?", [$ma]);
            if ($check == false) {
                $db->execute("INSERT INTO voucher (ma, kieu, moTa, giam, donToiDa, giamToiDa, ngayBatDau, ngayKetThuc, soLuong) VALUES (?,?,?,?,?,?,?,?,?)", [$ma, $kieu, $moTa, $giam, $donToiDa, $giamToiDa, $ngayBatDau, $ngayKetThuc, $soLuong]);
                $_SESSION["thongBao"] = "Thêm voucher thành công";
                header("Location: voucher.php");
                exit;
            } else {
                $err["ma"] = "Mã voucher đã tồn tại";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm voucher</title>
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
                        <div class="card-header bg-success text-white text-center py-2">
                            <h6 class="mb-0">
                                <i class="bi bi-ticket-perforated me-2"></i>Thêm voucher
                            </h6>
                        </div>

                        <!-- Form -->
                        <form action="" method="post" enctype="multipart/form-data" class="p-3 border rounded bg-light shadow-sm">

                            <div class="row">
                                <!-- Mã voucher -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-upc-scan me-1"></i>Mã voucher</label>
                                    <input type="text" class="form-control form-control-sm <?= isset($err["ma"]) ? 'is-invalid' : '' ?>" name="ma" value="<?= $ma ?? '' ?>">
                                    <?php if (isset($err["ma"])): ?>
                                        <div class="invalid-feedback"><?= $err["ma"]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Kiểu -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-tags me-1"></i>Chọn kiểu</label>
                                    <select id="kieu" class="form-select form-select-sm <?= isset($err["kieu"]) ? 'is-invalid' : '' ?>" name="kieu" onchange="toggleGiamToiDa()">
                                        <option <?= isset($kieu) && $kieu == 0 ? "selected" : "" ?> value="0">Giảm theo tiền</option>
                                        <option <?= isset($kieu) && $kieu == 1 ? "selected" : "" ?> value="1">Giảm theo phần trăm</option>
                                    </select>
                                    <?php if (isset($err["kieu"])): ?>
                                        <div class="invalid-feedback"><?= $err["kieu"]; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Giảm -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-cash-stack me-1"></i>Giảm</label>
                                    <input type="number" class="form-control form-control-sm <?= isset($err["giam"]) ? 'is-invalid' : '' ?>" name="giam" value="<?= $giam ?? '' ?>">
                                    <?php if (isset($err["giam"])): ?>
                                        <div class="invalid-feedback"><?= $err["giam"]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Đơn tối đa -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-bag-check me-1"></i>Đơn tối đa</label>
                                    <input type="number" class="form-control form-control-sm <?= isset($err["donToiDa"]) ? 'is-invalid' : '' ?>" name="donToiDa" value="<?= $donToiDa ?? '' ?>">
                                    <?php if (isset($err["donToiDa"])): ?>
                                        <div class="invalid-feedback"><?= $err["donToiDa"]; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Giảm tối đa -->
                                <div id="giamToiDaBox" class="col-md-6 mb-2" style="display: <?= isset($kieu) && $kieu == 1 ? 'block' : 'none' ?>;">
                                    <label class="form-label"><i class="bi bi-arrow-down-up me-1"></i>Giảm tối đa</label>
                                    <input type="number" class="form-control form-control-sm <?= isset($err["giamToiDa"]) ? 'is-invalid' : '' ?>" name="giamToiDa" value="<?= $giamToiDa ?? '' ?>">
                                    <?php if (isset($err["giamToiDa"])): ?>
                                        <div class="invalid-feedback"><?= $err["giamToiDa"]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Số lượng -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-stack me-1"></i>Số lượng</label>
                                    <input type="number" class="form-control form-control-sm <?= isset($err["soLuong"]) ? 'is-invalid' : '' ?>" name="soLuong" value="<?= $soLuong ?? '' ?>">
                                    <?php if (isset($err["soLuong"])): ?>
                                        <div class="invalid-feedback"><?= $err["soLuong"]; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Ngày bắt đầu -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-calendar-plus me-1"></i>Ngày bắt đầu</label>
                                    <input type="date" class="form-control form-control-sm <?= isset($err["ngayBatDau"]) ? 'is-invalid' : '' ?>" name="ngayBatDau" value="<?= $ngayBatDau ?? '' ?>">
                                    <?php if (isset($err["ngayBatDau"])): ?>
                                        <div class="invalid-feedback"><?= $err["ngayBatDau"]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Ngày kết thúc -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Ngày kết thúc</label>
                                    <input type="date" class="form-control form-control-sm <?= isset($err["ngayKetThuc"]) ? 'is-invalid' : '' ?>" name="ngayKetThuc" value="<?= $ngayKetThuc ?? '' ?>">
                                    <?php if (isset($err["ngayKetThuc"])): ?>
                                        <div class="invalid-feedback"><?= $err["ngayKetThuc"]; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Mô tả -->
                            <div class="mb-2">
                                <label class="form-label"><i class="bi bi-chat-text me-1"></i>Mô tả</label>
                                <textarea name="moTa" class="form-control form-control-sm <?= isset($err["moTa"]) ? 'is-invalid' : '' ?>" rows="2"><?= $moTa ?? '' ?></textarea>
                                <?php if (isset($err["moTa"])): ?>
                                    <div class="invalid-feedback"><?= $err["moTa"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-between mt-3">
                                <a href="voucher.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                                </a>
                                <button type="submit" name="create" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Thêm
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
    <!-- JS: Toggle Giảm tối đa -->
    <script>
        function toggleGiamToiDa() {
            const kieu = document.getElementById("kieu").value;
            const box = document.getElementById("giamToiDaBox");
            box.style.display = kieu == "1" ? "block" : "none";
        }
    </script>

</body>

</html>