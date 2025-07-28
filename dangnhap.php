<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

if (isset($_SESSION["nguoiDung"])) {
  header("location: index.php");
  exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["dangNhap"])) {
    $email = test_input($_POST["email"]);
    $matKhau = test_input($_POST["matKhau"]);

    validateEmpty($err, $email, "email");
    validateEmpty($err, $matKhau, "mật khẩu");

    if (empty($err)) {
      $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
      if ($nguoiDung && password_verify($matKhau, $nguoiDung["matKhau"])) {
        $_SESSION["nguoiDung"] = $nguoiDung;
        // addCartLogin($user["userID"]);
        header("location: index.php");
        exit();
      } else {
        $_SESSION["thongBao"]  = "Tài khoản không tồn tại";
        $email = "";
        $matKhau = "";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập</title>
</head>

<body>
  <?php require_once "./include/header.php" ?>
  <div class="container d-flex justify-content-center align-items-center min-vh-50">
    <div class="row bg-white shadow rounded p-4 register-container w-100">

      <!-- Cột trái - Form đăng nhập -->
      <div class="col-md-6 border-end">
        <h4 class="fw-bold mb-2">Đăng nhập vào tài khoản</h4>
        <p class="text-muted mb-4">Đăng nhập để thanh toán nhanh hơn.</p>

        <form id="loginForm" method="POST" action="">
          <?php if (!empty($_SESSION["thongBao"])): ?>
            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
              <?= $_SESSION["thongBao"] ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php $_SESSION["thongBao"] = ""; ?>
          <?php endif; ?>

          <!-- Email -->
          <label class="form-label">Địa chỉ Email</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="bi bi-envelope-fill"></i></span>
            <input value="<?= isset($email) ? $email : "" ?>" name="email" type="email" class="form-control bg-light <?= isset($err['email']) ? 'is-invalid' : '' ?>" id="loginEmail" placeholder="Nhập email" required>
            <?php if (isset($err['email'])): ?>
              <div class="invalid-feedback d-block"><?= $err['email'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Mật khẩu -->
          <label class="form-label">Mật khẩu</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
            <input value="<?= isset($matKhau) ? $matKhau : "" ?>" name="matKhau" type="password" class="form-control bg-light <?= isset($err['matKhau']) ? 'is-invalid' : '' ?>" id="passwordInput" placeholder="Nhập mật khẩu" required>
            <span class="input-group-text bg-light" onclick="toggleLoginPassword()" style="cursor: pointer;">
              <i class="bi bi-eye" id="eyeIconLogin"></i>
            </span>
            <?php if (isset($err['mật khẩu'])): ?>
              <div class="invalid-feedback d-block"><?= $err['mật khẩu'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Quên mật khẩu -->
          <div class="mb-3">
            <a href="forgot.php" class="text-decoration-none text-primary fw-semibold">Quên mật khẩu?</a>
          </div>

          <!-- Nút đăng nhập -->
          <button name="dangNhap" type="submit" class="btn btn-primary w-100 fw-bold">ĐĂNG NHẬP</button>


        </form>
      </div>

      <!-- Cột phải - Mạng xã hội -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center gap-3 mt-4 mt-md-0">
        <!-- Logo chèn vào đầu cột -->
        <img src="uploads/logo.jpg" alt="Logo" style="width: 200px; height: auto;">

        <h6 class="mb-3 fw-semibold mt-3">Đăng nhập bằng mạng xã hội</h6>

        <a href="facebook-login.php" class="btn btn-primary w-75" type="button">
          <i class="bi bi-facebook me-2"></i>Đăng nhập bằng Facebook
        </a>
        <a href="google-login.php" class="btn btn-light border w-75">
          <i class="bi bi-google me-2"></i>Đăng nhập bằng Google
        </a>


        <p class="mt-3">
          Chưa có tài khoản?
          <a href="dangki.php" class="fw-bold text-decoration-none text-primary">ĐĂNG KÝ</a>
        </p>
      </div>

    </div>
  </div>

  <?php require_once "./include/footer.php" ?>
  <script>
    function toggleLoginPassword() {
      const input = document.getElementById("passwordInput");
      const icon = document.getElementById("eyeIconLogin");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    }
  </script>
</body>

</html>