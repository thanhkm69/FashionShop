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
$userID = $_GET["id"];
$result = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$userID]);

if (!$result) {
    die("Không tìm thấy người dùng.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $userID = test_input($_POST["userID"]);
    $name = test_input($_POST["name"]);
    $email = test_input($_POST["email"]);
    $phone = test_input($_POST["phone"]);
    $role = test_input($_POST["role"]);
    $status = test_input($_POST["status"]);
    $password = $_POST["password"] ?? "";
    $img = $result["hinh"];

    validateEmpty($err, $name, "tên");
    validateEmail($err, $email);
    if (!empty($phone)) {
        validatePhone($err, $phone);
    }
    if (!empty($password)) {
        validatePassword($err, $password);
        if (empty($err)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }
    } else {
        $passwordHash = $result["matKhau"];
    }

    $checkEmail = $db->getOne("SELECT * FROM nguoidung WHERE email = ? AND id != ?", [$email, $userID]);
    if ($checkEmail) $err["email"] = "Email đã tồn tại";

    if (!empty($_FILES["img"]["name"]) && empty($err)) {
        $newImg = validateImg($err, $dir, $_FILES["img"]);
        if ($newImg && $newImg != null) {
            if ($img != "user.jpg" && file_exists($dir . $img)) {
                unlink($dir . $img);
            }
            $img = $newImg;
        }
    }

    if (empty($err)) {
        $db->execute(
            "UPDATE nguoidung SET ten=?, email=?, soDienThoai=?, hinh=?, phanQuyen=?, trangThai=?, matKhau=? WHERE id = ?",
            [$name, $email, $phone, $img, $role, $status, $passwordHash, $userID]
        );
        $_SESSION["thongBao"] = "Sửa người dùng thành công";
        header("Location: nguoidung.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa người dùng</title>
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
                            <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Sửa người dùng</h6>
                        </div>
                        <form action="" method="post" enctype="multipart/form-data" class="p-3">
                            <input type="hidden" name="userID" value="<?= $result["id"] ?>">

                            <div class="mb-3">
                                <label class="form-label d-block"><i class="bi bi-image"></i>Hình đại diện</label>
                                <div class="d-flex align-items-center">
                                    <img src="<?= $dir . htmlspecialchars($result["hinh"]) ?>" class="preview-img me-3">
                                    <div class="flex-grow-1">
                                        <input type="file" name="img" class="form-control <?= ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["img"]) ? 'is-invalid' : 'is-valid') : '' ?>">
                                        <div class="invalid-feedback"><?= $err["img"] ?? "" ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person"></i>Họ và tên</label>
                                <input type="text" name="name"
                                    class="form-control <?= ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["tên"]) ? 'is-invalid' : 'is-valid') : '' ?>"
                                    value="<?= htmlspecialchars($_POST["name"] ?? $result["ten"]) ?>">
                                <div class="invalid-feedback"><?= $err["tên"] ?? "" ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-envelope-at"></i>Email</label>
                                <input type="email" name="email"
                                    class="form-control <?= ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["email"]) ? 'is-invalid' : 'is-valid') : '' ?>"
                                    value="<?= htmlspecialchars($_POST["email"] ?? $result["email"]) ?>">
                                <div class="invalid-feedback"><?= $err["email"] ?? "" ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-telephone"></i>Số điện thoại</label>
                                <input type="text" name="phone"
                                    class="form-control <?= ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["phone"]) ? 'is-invalid' : 'is-valid') : '' ?>"
                                    value="<?= $_POST["phone"] ?? $result["soDienThoai"] ?>">
                                <div class="invalid-feedback"><?= $err["phone"] ?? "" ?></div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label class="form-label">
                                    <i class="bi bi-lock-fill"></i> Mật khẩu mới (tuỳ chọn)
                                </label>
                                <input type="password" name="password" id="passwordField"
                                    class="form-control <?= ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["matKhau"]) ? 'is-invalid' : 'is-valid') : '' ?>"
                                    value="<?= htmlspecialchars($_POST["password"] ?? "") ?>">
                                <i class="bi bi-eye-slash toggle-password position-absolute"
                                    style="top: 38px; right: 15px; cursor: pointer;" onclick="togglePassword()"></i>
                                <div class="invalid-feedback"><?= $err["matKhau"] ?? "" ?></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-person-gear"></i>Phân quyền</label>
                                    <select name="role" class="form-select" required>
                                        <option value="Admin" <?= ($result["phanQuyen"] == "Admin") ? "selected" : "" ?>>👑 Admin</option>
                                        <option value="User" <?= ($result["phanQuyen"] == "User") ? "selected" : "" ?>>👤 User</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-toggle-on"></i>Trạng thái</label>
                                    <select name="status" class="form-select" required>
                                        <option value="1" <?= ($result["trangThai"] == "1") ? "selected" : "" ?>>✅ Hoạt động</option>
                                        <option value="0" <?= ($result["trangThai"] == "0") ? "selected" : "" ?>>❌ Vô hiệu hóa</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between pt-2">
                                <a href="nguoidung.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i>Quay lại
                                </a>
                                <button type="submit" name="update" class="btn btn-warning btn-sm">
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
    <script>
        function togglePassword() {
            const input = document.getElementById("passwordField");
            const icon = document.querySelector(".toggle-password");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>

</body>

</html>