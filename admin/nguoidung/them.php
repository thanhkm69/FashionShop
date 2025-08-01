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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {

    $password = test_input($_POST["password"] ?? "");
    $name     = test_input($_POST["name"] ?? "");
    $email    = test_input($_POST["email"] ?? "");
    $phone    = test_input($_POST["phone"] ?? "");
    $dir      = "../../uploads/";

    // Validate
    validateEmpty($err, $name, "tên");
    validateEmail($err, $email);
    if (!empty($phone)) {
        validatePhone($err, $phone);
    }
    validatePassword($err, $password);
    // Check email trùng
    if (empty($err)) {
        $checkEmail = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
        if ($checkEmail) {
            $err["email"] = "Email đã tồn tại";
        }
    }

    // Xử lý ảnh nếu có
    $imgName = null;
    if (!empty($_FILES["img"]["name"]) && empty($err)) {
        $imgName = validateImg($err, $dir, $_FILES["img"]);
    }

    if (empty($err)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($imgName === null) {
            $db->execute(
                "INSERT INTO nguoidung (matKhau, ten, email, soDienThoai) 
                 VALUES (?, ?, ?, ?)",
                [$password_hash, $name, $email, $phone]
            );
        } else {
            $db->execute(
                "INSERT INTO nguoidung (matKhau, ten, email, soDienThoai, img) 
                 VALUES (?, ?, ?, ?, ?)",
                [$password_hash, $name, $email, $phone, $imgName]
            );
        }

        $_SESSION["thongBao"] = "Thêm người dùng thành công";
        header("Location: nguoidung.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm người dùng</title>
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
        }

        form label {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        form input.form-control {
            font-size: 0.9rem;
            padding: 0.4rem 0.5rem;
        }

        #togglePassword i {
            color: black;
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
                            <h6 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Thêm người dùng</h6>
                        </div>
                        <!-- Đoạn bên trong <form> -->
                        <form action="" method="post" enctype="multipart/form-data" class="card p-2 shadow-sm">

                            <!-- Hình -->
                            <div class="mb-2">
                                <label>Hình</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                                    <input type="file" name="img"
                                        class="form-control <?php if (isset($err["img"])) echo 'is-invalid'; ?>">
                                </div>
                                <div class="invalid-feedback d-block"><?php echo $err["img"] ?? "" ?></div>
                            </div>

                            <!-- Tên -->
                            <div class="mb-2">
                                <label>Tên</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input value="<?php echo htmlspecialchars($name ?? "") ?>" type="text" name="name"
                                        class="form-control <?php echo ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["tên"]) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                </div>
                                <div class="invalid-feedback d-block"><?php echo $err["tên"] ?? "" ?></div>
                            </div>

                            <!-- Email -->
                            <div class="mb-2">
                                <label>Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input value="<?php echo htmlspecialchars($email ?? "") ?>" type="email" name="email"
                                        class="form-control <?php echo ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["email"]) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                </div>
                                <div class="invalid-feedback d-block"><?php echo $err["email"] ?? "" ?></div>
                            </div>

                            <!-- Số điện thoại -->
                            <div class="mb-2">
                                <label>Điện thoại</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input value="<?php echo htmlspecialchars($phone ?? "") ?>" type="text" name="phone"
                                        class="form-control <?php echo ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["soDienThoai"]) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                </div>
                                <div class="invalid-feedback d-block"><?php echo $err["soDienThoai"] ?? "" ?></div>
                            </div>

                            <!-- Mật khẩu -->
                            <div class="mb-2">
                                <label>Mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="passwordInput" type="password" name="password"
                                        value="<?php echo htmlspecialchars($password ?? "") ?>"
                                        class="form-control <?php echo ($_SERVER["REQUEST_METHOD"] == "POST") ? (isset($err["matKhau"]) ? 'is-invalid' : 'is-valid') : ''; ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <?php if (isset($err["matKhau"])): ?>
                                    <div class="invalid-feedback d-block"><?php echo $err["matKhau"]; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nút -->
                            <div class="d-flex justify-content-between mt-3">
                                <a href="nguoidung.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                                </a>
                                <button type="submit" name="create" class="btn btn-success btn-sm">
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

    <!-- Toggle Password Script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>