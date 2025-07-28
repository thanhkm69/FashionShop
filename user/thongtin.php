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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["luu"])) {
        $ten = trim($_POST["ten"]);
        $email = trim($_POST["email"]);
        $soDienThoai = trim($_POST["soDienThoai"]);
        $gioiTinh = $_POST["gioiTinh"];
        $ngaySinh = $_POST["ngaySinh"];
        $imgName = null;
        if (empty($ten)) {
            $err["ten"] = "Vui lòng nhập tên";
        }
        validateEmail($err, $email);
        $check = $db->getOne("SELECT * FROM nguoidung WHERE id != ? AND email = ?", [$id, $email]);
        if ($check) {
            $err["email"] = "Email đã tồn tại";
        }
        if (!empty($_FILES["img"]["name"] && empty($err))) {
            $imgName = validateImg($err, $dir, $_FILES["img"]);
        }
        if (empty($err)) {
            if ($imgName == null) {
                // Không thay ảnh
                $db->execute("UPDATE nguoidung SET ten = ?, email = ?, soDienThoai = ?, gioiTinh = ?, ngaySinh = ? WHERE id = ?", [
                    $ten,
                    $email,
                    $soDienThoai,
                    $gioiTinh,
                    $ngaySinh,
                    $id
                ]);
            } else {
                // Có thay ảnh
                if ($nguoiDung["hinh"] != "user.jpg") {
                    unlink($dir . $nguoiDung["hinh"]);
                }
                $db->execute("UPDATE nguoidung SET ten = ?, email = ?, soDienThoai = ?, gioiTinh = ?, ngaySinh = ?, hinh = ? WHERE id = ?", [
                    $ten,
                    $email,
                    $soDienThoai,
                    $gioiTinh,
                    $ngaySinh,
                    $imgName,
                    $id
                ]);
            }

            $_SESSION["thongBao"] = "Cập nhập thông tin thành công";
            header("location: thongtin.php");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php' ?>
            <div class="container">
                <div class="row">
                    <div class="container py-4">
                     <h4><i class="bi bi-person-circle text-danger me-2"></i> Hồ Sơ Của Tôi</h4>
                        <p class="text-muted">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                        <hr>

                        <?php if (!empty($_SESSION["thongBao"])): ?>
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                <?= $_SESSION["thongBao"] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php $_SESSION["thongBao"] = ""; ?>
                        <?php endif; ?>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Cột trái: Thông tin -->
                                <div class="col-md-8">
                                    <!-- Dòng 1: Tên và Email -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-person-fill me-2"></i> Tên
                                            </label>
                                            <input type="text" class="form-control" name="ten" value="<?= htmlspecialchars($nguoiDung["ten"]) ?>">
                                            <?php if (!empty($err["ten"])): ?><div class="text-danger"><?= $err["ten"] ?></div><?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-envelope-fill me-2"></i> Email
                                            </label>
                                            <input type="text" class="form-control" name="email" value="<?= htmlspecialchars($nguoiDung["email"]) ?>">
                                        </div>
                                    </div>

                                    <!-- Dòng 2: Giới tính và Số điện thoại -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-telephone-fill me-2"></i> Số điện thoại
                                            </label>
                                            <input type="text" name="soDienThoai" class="form-control" value="<?= ($nguoiDung["soDienThoai"]) ?>">
                                            <?php if (!empty($err["soDienThoai"])): ?><div class="text-danger"><?= $err["soDienThoai"] ?></div><?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">
                                                <i class="bi bi-gender-ambiguous me-2"></i> Giới tính
                                            </label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gioiTinh" id="nam" value="Nam" <?= ($nguoiDung["gioiTinh"] === "Nam") ? "checked" : "" ?>>
                                                <label class="form-check-label" for="nam">Nam</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gioiTinh" id="nu" value="Nữ" <?= ($nguoiDung["gioiTinh"] === "Nữ") ? "checked" : "" ?>>
                                                <label class="form-check-label" for="nu">Nữ</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gioiTinh" id="khac" value="Khác" <?= ($nguoiDung["gioiTinh"] === "Khác") ? "checked" : "" ?>>
                                                <label class="form-check-label" for="khac">Khác</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dòng 3: Ngày sinh -->
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-calendar-date me-2"></i> Ngày sinh
                                        </label>
                                        <input type="date" name="ngaySinh" class="form-control" value="<?= date('Y-m-d', strtotime($nguoiDung["ngaySinh"])) ?>">
                                    </div>

                                    <button type="submit" name="luu" class="btn btn-danger mt-3 px-4">
                                        <i class="bi bi-save2 me-2"></i> Lưu
                                    </button>
                                </div>

                                <!-- Cột phải: Ảnh đại diện -->
                                <div class="col-md-4 text-center">
                                    <img src="<?= $dir . $nguoiDung["hinh"] ?>" class="rounded-circle border" width="120" height="120" style="object-fit: cover;">
                                    <div class="mt-3">
                                        <label class="form-label d-flex justify-content-center align-items-center gap-2">
                                            <i class="bi bi-image-fill"></i> Ảnh đại diện
                                        </label>
                                        <input type="file" name="img" class="form-control mt-2">
                                        <small class="text-muted">Dung lượng tối đa 1MB<br>Định dạng: JPG, PNG</small>
                                        <?php if (!empty($err["hinh"])): ?><div class="text-danger"><?= $err["hinh"] ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <?php include '../admin/include/footer.php' ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>