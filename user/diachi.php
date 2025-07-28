<?php
require_once "../core/db_utils.php";
if (!isset($_SESSION["nguoiDung"])) {
    header("location: ../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}
$dir = "../uploads/";
$dirMenu = "../";

$dstinh = $db->getAll("SELECT * FROM tinh");


$idTinh = $idHuyen = $idXa = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["tinh"])) {
        $idTinh = $_POST["tinh"];
        $dshuyen = $db->getAll("SELECT * FROM huyen WHERE idTinh = ?", [$idTinh]);
    }
    if (isset($_POST["huyen"])) {
        $idHuyen = $_POST["huyen"];
        $dsxa = $db->getAll("SELECT * FROM xa WHERE idHuyen = ?", [$idHuyen]);
    }
    if (isset($_POST["xa"])) {
        $idXa = $_POST["xa"];
    }

    if (isset($_POST["them"])) {
        $tinh = $_POST["tinh"] ?? "";
        $huyen = $_POST["huyen"] ?? "";
        $xa = $_POST["xa"] ?? "";
        $chiTiet = test_input($_POST["chiTiet"] ?? "");

        if (empty($chiTiet) || empty($tinh) || empty($huyen) || empty($xa)) {
            $_SESSION["thongBao"] = "Vui lòng nhập đầy đủ thông tin";
        } else {
            $db->execute("INSERT INTO diachi(idNguoiDung,idXa,chiTiet) VALUES (?,?,?)", [$id, $xa, $chiTiet]);
            $_SESSION["thongBao"] = "Thêm địa chỉ thành công!";
            header("Location: diachi.php");
            exit;
        }
    }


    if (isset($_POST["macDinh"])) {
        $db->execute("UPDATE diachi SET macDinh = 0 WHERE idNguoiDung = ?", [$_POST["idNguoiDung"]]);
        $db->execute("UPDATE diachi SET macDinh = 1 WHERE idNguoiDung = ? AND id = ?", [$_POST["idNguoiDung"], $_POST["idDiaChi"]]);
        $_SESSION["thongBao"] = "Cập nhật địa chỉ thành công!";
        header("Location: diachi.php?");
        exit;
    }
    if (isset($_POST["xoa"])) {
        $db->execute("DELETE FROM diachi WHERE id = ?", [$_POST["idDiaChi"]]);
        $_SESSION["thongBao"] = "Xóa địa chỉ thành công!";
        header("Location: diachi.php?");
        exit;
    }
}


$dsDiaChi = $db->getAll("
    SELECT dc.id, dc.chiTiet, dc.macDinh, 
           tinh.ten AS tenTinh, 
           huyen.ten AS tenHuyen, 
           xa.ten AS tenXa
    FROM diachi dc
    JOIN xa ON dc.idXa = xa.maGHN
    JOIN huyen ON xa.idHuyen = huyen.maGHN
    JOIN tinh ON huyen.idTinh = tinh.maGHN
    WHERE dc.idNguoiDung = ?
    ORDER BY macDinh DESC
", [$id]);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Địa chỉ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        form.card.p-10 {
            padding: 1.5rem !important;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            background-color: #fff;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-primary {
            border-radius: 8px;
            font-weight: 500;
        }

        .row.align-items-end>div {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .row.align-items-end>div {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php' ?>
            <main class="p-4 flex-grow-1">
                <div class="container my-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-geo-alt-fill fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Sổ địa chỉ</span>
                    </div>

                    <?php if (!empty($_SESSION["thongBao"])): ?>
                        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                            <?= $_SESSION["thongBao"] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php $_SESSION["thongBao"] = ""; ?>
                    <?php endif; ?>
                    <form action="" method="post" class="card p-3 shadow-sm">
                        <div class="d-flex flex-wrap align-items-end gap-3">

                            <!-- Tỉnh -->
                            <div style="flex: 1; min-width: 150px;">
                                <label for="tinh" class="form-label">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Tỉnh
                                </label>
                                <select name="tinh" id="tinh" class="form-select" onchange="this.form.submit()">
                                    <option value="">Chọn Tỉnh</option>
                                    <?php foreach ($dstinh as $tinh) { ?>
                                        <option <?= isset($_POST["tinh"]) && $idTinh == $tinh["maGHN"] ? "selected" : "" ?> value="<?= $tinh["maGHN"] ?>">
                                            <?= $tinh["ten"] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Huyện -->
                            <div style="flex: 1; min-width: 150px;">
                                <label for="huyen" class="form-label">
                                    <i class="bi bi-signpost-2-fill me-1"></i>TP/Quận/Huyện
                                </label>
                                <select name="huyen" id="huyen" class="form-select" onchange="this.form.submit()" <?= !isset($_POST["tinh"]) ? "disabled" : "" ?>>
                                    <option value="">Chọn Quận / Huyện</option>
                                    <?php if (isset($_POST["tinh"])) {
                                        foreach ($dshuyen as $huyen) { ?>
                                            <option <?= isset($_POST["huyen"]) && $idHuyen == $huyen["maGHN"] ? "selected" : "" ?> value="<?= $huyen["maGHN"] ?>">
                                                <?= $huyen["ten"] ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>

                            <!-- Xã -->
                            <div style="flex: 1; min-width: 150px;">
                                <label for="xa" class="form-label">
                                    <i class="bi bi-house-door-fill me-1"></i>Xã
                                </label>
                                <select name="xa" id="xa" class="form-select" onchange="this.form.submit()" <?= !isset($_POST["huyen"]) ? "disabled" : "" ?>>
                                    <option value="">Chọn Xã</option>
                                    <?php if (isset($_POST["huyen"])) {
                                        foreach ($dsxa as $xa) { ?>
                                            <option <?= isset($_POST["xa"]) && $idXa == $xa["maGHN"] ? "selected" : "" ?> value="<?= $xa["maGHN"] ?>">
                                                <?= $xa["ten"] ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>

                            <!-- Chi tiết -->
                            <div style="flex: 2; min-width: 200px;">
                                <label for="chiTiet" class="form-label">
                                    <i class="bi bi-pencil-square me-1"></i>Chi tiết
                                </label>
                                <input type="text" name="chiTiet" id="chiTiet"
                                    class="form-control" placeholder="Thôn, Tên đường, Số nhà"
                                    value="<?= isset($_POST["chiTiet"]) ? htmlspecialchars($_POST["chiTiet"]) : "" ?>"
                                    <?= !isset($_POST["xa"]) || empty($_POST["xa"]) ? "disabled" : "" ?> required>
                                <?php if (!empty($err["chiTiet"])): ?>
                                    <div class="text-danger mt-1 small"><?= $err["chiTiet"] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút Thêm -->
                            <div style="min-width: 120px;">
                                <label class="form-label d-block invisible">Nút</label>
                                <button type="submit" name="them" class="btn btn-success w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-plus-circle me-2"></i> Thêm
                                </button>
                            </div>

                        </div>
                    </form>

                    <div class="table-responsive">
                        <?php if (!empty($dsDiaChi)) { ?>
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tỉnh</th>
                                        <th>TP/Quận/Huyện</th>
                                        <th>Phường/Thị trấn/Xã</th>
                                        <th>Chi tiết</th>
                                        <th>Mặc định</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dsDiaChi as $dc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dc["tenTinh"]) ?></td>
                                            <td><?= htmlspecialchars($dc["tenHuyen"]) ?></td>
                                            <td><?= htmlspecialchars($dc["tenXa"]) ?></td>
                                            <td><?= htmlspecialchars($dc["chiTiet"]) ?></td>
                                            <td>
                                                <?php if ($dc["macDinh"]): ?>
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <form method="post" action="">
                                                        <input type="hidden" name="idDiaChi" value="<?= $dc["id"] ?>">
                                                        <input type="hidden" name="idNguoiDung" value="<?= $id ?>">

                                                        <?php if (!$dc["macDinh"]): ?>
                                                            <button name="macDinh" class="btn btn-outline-primary btn-sm" title="Đặt làm mặc định">
                                                                <i class="bi bi-star"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                        <button type="submit" name="xoa" class="btn btn-danger btn-sm" title="Xóa địa chỉ" onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="alert alert-warning text-center mt-4" role="alert">
                                <i class="bi bi-geo-alt fs-4 me-2"></i>Hiện tại chưa có địa chỉ nào.
                            </div>
                        <?php } ?>
                    </div>

                </div>


            </main>
            <?php include '../admin/include/footer.php' ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>