<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";
if (isset($_SESSION["nguoiDung"])) {
  header("location: index.php");
  exit();
}
$isSubmitted = $_SERVER["REQUEST_METHOD"] === "POST";
if ($isSubmitted) {
  if (isset($_POST["dangKi"])) {
    $ten = test_input($_POST["ten"]);
    $matKhau = test_input($_POST["matKhau"]);
    $email = test_input($_POST["email"]);
    $checkbox = isset($_POST["checkbox"]) ? 1 : 0;
    validateEmpty($err, $ten, "ten");
    validatePassword($err, $matKhau);
    validateEmail($err, $email);
    if ($checkbox) {

      if (empty($err)) {
        $checkEmail = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
        if (!$checkEmail) {
          $matKhau_hash = password_hash($matKhau, PASSWORD_DEFAULT);
          $insert = $db->execute("INSERT INTO nguoidung(ten,email,matKhau) VALUES (?,?,?)", [$ten, $email, $matKhau_hash,]);
          $subject = "Đăng kí tài khoản thành công";
          $body = '
  <!DOCTYPE html>
  <html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <title>Đăng ký thành công</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f6f8;
        margin: 0; padding: 0;
      }
      .container {
        max-width: 600px;
        background-color: #ffffff;
        margin: 40px auto;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        color: #333333;
      }
      h1 {
        color: #007bff;
        font-size: 24px;
        margin-bottom: 10px;
      }
      p {
        font-size: 16px;
        line-height: 1.5;
      }
      .footer {
        margin-top: 30px;
        font-size: 12px;
        color: #999999;
        text-align: center;
      }
      .btn {
        display: inline-block;
        padding: 10px 20px;
        margin-top: 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Chào mừng bạn đến với Fashion Shop!</h1>
      <p>Cảm ơn bạn đã đăng kí tài khoản và sử dụng dịch vụ của chúng tôi.</p>
      <p>Chúng tôi rất vui được phục vụ bạn. Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi.</p>
      <div class="footer">
        &copy; 2025 Fashion Shop. All rights reserved.
      </div>
    </div>
  </body>
  </html>
  ';
          MailService::send($email, USERNAME_EMAIL, $subject, $body);
          header("location: dangnhap.php");
          exit();
        } else {
          $err["email"] = "Email đã tồn tại";
        }
      }
    }
  } else {
    $err["checkbox"] = "Vui lòng đồng ý với điều khoản";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng kí</title>
</head>

<body>
  <?php require_once "./include/header.php"; ?>
  <div class="container d-flex justify-content-center align-items-center min-vh-50">
    <div class="row bg-white shadow rounded p-4 register-container w-100">

      <!-- Cột trái - Form đăng ký -->
      <div class="col-md-6 border-end">
        <h4 class="fw-bold mb-2">Tạo tài khoản mới</h4>
        <p class="text-muted mb-4">Đăng ký để sử dụng các dịch vụ của chúng tôi.</p>
        <form id="registerForm" action="" method="POST">
          <!-- Tên -->
          <label class="form-label">Tên</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
            <input value="<?= isset($ten) ? $ten : "" ?>" type="text" name="ten" class="form-control bg-light <?= $isSubmitted ? (isset($err['ten']) ? 'is-invalid' : 'is-valid') : "" ?>" id="name" placeholder="Nhập tên" required>
            <?php if (isset($err['ten'])): ?>
              <div class="invalid-feedback d-block"><?= $err['ten'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Email -->
          <label class="form-label">Địa chỉ Email</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="bi bi-envelope-fill"></i></span>
            <input value="<?= isset($email) ? $email : "" ?>" type="email" name="email" class="form-control bg-light <?= $isSubmitted ? (isset($err['email']) ? 'is-invalid' : 'is-valid') : "" ?>" id="email" placeholder="Nhập email" required>
            <?php if (isset($err['email'])): ?>
              <div class="invalid-feedback d-block"><?= $err['email'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Mật khẩu -->
          <label class="form-label">Mật khẩu</label>
          <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
            <input value="<?= isset($matKhau) ? $matKhau : "" ?>" type="password" name="matKhau" class="form-control bg-light <?= $isSubmitted ? (isset($err['matKhau']) ? 'is-invalid' : 'is-valid') : "" ?>" id="password" placeholder="Nhập mật khẩu" required>
            <span class="input-group-text bg-light" onclick="togglePassword()" style="cursor:pointer;"><i class="bi bi-eye" id="toggleIcon"></i></span>
            <?php if (isset($err['matKhau'])): ?>
              <div class="invalid-feedback d-block"><?= $err['matKhau'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Điều khoản -->
          <div class="form-check mb-3">
            <input <?= $isSubmitted && $checkbox ? "checked" : "" ?> name="checkbox" class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label" for="terms">
              Tôi đồng ý với <a href="#">Điều khoản sử dụng</a>.
            </label>
            <?php if (isset($err['checkbox'])): ?>
              <div class="invalid-feedback d-block"><?= $err['checkbox'] ?></div>
            <?php endif; ?>
          </div>

          <!-- Nút đăng ký -->
          <button name="dangKi" type="submit" class="btn btn-primary w-100 fw-bold">ĐĂNG KÝ</button>
        </form>
      </div>

      <!-- Cột phải - Giới thiệu về shop -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center text-center gap-3 mt-4 mt-md-0">
        <!-- Logo -->
        <img src="uploads/logo.jpg" alt="Logo" style="width: 200px; height: auto;">

        <!-- Tiêu đề giới thiệu -->
        <h5 class="fw-bold mt-3">Chào mừng đến với Fashion Shop</h5>

        <!-- Nội dung giới thiệu -->
        <p class="text-muted px-3">
          Chúng tôi chuyên cung cấp các loại thời trang chất lượng cao, nhập khẩu từ các quốc gia nổi tiếng như Pháp, Ý, Chile...
          Đăng ký ngay để nhận các ưu đãi hấp dẫn và trải nghiệm mua sắm tuyệt vời!
        </p>

        <!-- Link đăng nhập -->
        <p class="mt-3">
          Đã có tài khoản?
          <a href="dangnhap.php" class="fw-bold text-decoration-none text-primary">ĐĂNG NHẬP</a>
        </p>
      </div>
    </div>
  </div>
  <?php require_once "./include/footer.php"; ?>
  <script>
    function togglePassword() {
      const input = document.getElementById("password");
      const icon = document.getElementById("toggleIcon");
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