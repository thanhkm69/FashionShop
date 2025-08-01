<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

$step = 1;
$email = '';
$otpVerified = false;
$err = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Bước 1: Gửi OTP qua email
    if (isset($_POST["submit_email"])) {
        $email = test_input($_POST["email"]);
        validateEmail($err, $email);

        if (empty($err)) {
            $user = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
            if ($user) {
                $otp = generateOTP();
                $now = date("Y-m-d H:i:s");

                $db->execute("UPDATE nguoidung SET otp = ?, otpTime = ? WHERE id = ? AND email = ?", [
                    $otp,
                    $now,
                    $user["id"],
                    $user["email"]
                ]);

                $subject = "Khôi phục mật khẩu";
                $body = "Mã OTP của bạn là: $otp. Có hiệu lực trong 2 phút.";
                MailService::send($email, USERNAME_EMAIL, $subject, $body);

                $step = 2;
            } else {
                $err["email"] = "Email không tồn tại.";
            }
        }
    }

    // Bước 2: Xác thực OTP
    if (isset($_POST["submit_otp"])) {
        $email = test_input($_POST["email"]);
        $otpInput = test_input($_POST["otp"]);
        if (empty($otpInput)) {
            $err["otp"] = "Vui lòng nhập mã OTP";
        }

        if (empty($err)) {
            $user = $db->getOne("SELECT otp, otpTime FROM nguoidung WHERE email = ?", [$email]);
            if ($user) {
                $otp_time = strtotime($user["otpTime"]);
                $now = time();
                if (($now - $otp_time) > 120) {
                    $err["otp"] = "Mã OTP đã hết hạn.";
                    $db->execute("UPDATE nguoidung SET otp = NULL, otpTime = NULL WHERE email = ?", [$email]);
                } elseif ($otpInput === $user["otp"]) {
                    $db->execute("UPDATE nguoidung SET otp = NULL, otpTime = NULL WHERE email = ?", [$email]);
                    $otpVerified = true;
                    $step = 3;
                } else {
                    $err["otp"] = "Mã OTP không đúng.";
                }
            } else {
                $err["otp"] = "Không tìm thấy người dùng.";
            }
        }

        // ✅ Bắt buộc set lại step = 2 nếu có lỗi
        if (!empty($err)) {
            $step = 2;
        }
    }

    // Bước 3: Đặt lại mật khẩu
    if (isset($_POST["submit_password"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $confirm = $_POST["confirm"];

        validatePassword($err, $password);
        if ($password !== $confirm) {
            $err["confirm"] = "Mật khẩu không khớp.";
        }

        if (empty($err)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $db->execute("UPDATE nguoidung SET matKhau = ? WHERE email = ?", [$password_hash, $email]);

            $subject = "Đặt lại mật khẩu thành công";
            $body = "Bạn đã đặt lại mật khẩu thành công. Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!";
            MailService::send($email, USERNAME_EMAIL, $subject, $body);

            header("location: dangnhap.php");
            exit();
        } else {
            $step = 3;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <style>
        .toggle-password {
            position: absolute;
            cursor: pointer;
            color: #aaa;
        }

        .toggle-password:hover {
            color: #333;
        }

        .input-group .form-control {
            height: 40px;
        }

        .input-group .btn {
            height: 40px;
            /* Bằng với input */
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ced4da;
            border-left: none;
            border-radius: 0 0.375rem 0.375rem 0;
        }

        .input-group .btn i {
            font-size: 16px;
            line-height: 1;
        }
    </style>
</head>

<body>
    <?php require_once "./include/header.php" ?>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="card shadow p-4" style="width: 100%; max-width: 450px;">
            <h4 class="text-center text-primary mb-4">
                <i class="fa-solid fa-key"></i> Quên mật khẩu
            </h4>

            <!-- Bước 1: Nhập Email -->
            <?php if ($step === 1): ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input type="email" name="email" id="email" class="form-control <?= isset($err['email']) ? 'is-invalid' : '' ?>"
                            placeholder="Nhập email bạn đã đăng ký" value="<?= htmlspecialchars($email) ?>" required>
                        <?php if (isset($err["email"])): ?>
                            <div class="invalid-feedback"><?= $err["email"] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="submit_email" class="btn btn-primary w-100">
                        <i class="fa-solid fa-paper-plane"></i> Gửi mã OTP
                    </button>
                </form>
            <?php endif; ?>

            <!-- Bước 2: Nhập OTP -->
            <?php if ($step === 2): ?>
                <form method="post">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Mã OTP</label>
                        <input type="text" name="otp" id="otp" class="form-control <?= isset($err['otp']) ? 'is-invalid' : '' ?>"
                            placeholder="Nhập mã OTP được gửi tới email">
                        <?php if (isset($err["otp"])): ?>
                            <div class="invalid-feedback"><?= $err["otp"] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="submit_otp" class="btn btn-success w-100">
                        <i class="fa-solid fa-check"></i> Xác nhận OTP
                    </button>
                </form>
            <?php endif; ?>

            <!-- Bước 3: Đặt lại mật khẩu -->
            <?php if ($step === 3): ?>
                <form method="post">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                    <!-- Mật khẩu mới -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                class="form-control <?= isset($err['matKhau']) ? 'is-invalid' : '' ?>"
                                placeholder="Nhập mật khẩu mới">
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($err["matKhau"])): ?>
                            <div class="invalid-feedback d-block"><?= $err["matKhau"] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Xác nhận mật khẩu -->
                    <div class="mb-3">
                        <label for="confirm" class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <input type="password" name="confirm" id="confirm"
                                class="form-control <?= isset($err['confirm']) ? 'is-invalid' : '' ?>"
                                placeholder="Nhập lại mật khẩu">
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#confirm">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($err["confirm"])): ?>
                            <div class="invalid-feedback d-block"><?= $err["confirm"] ?></div>
                        <?php endif; ?>
                    </div>


                    <button type="submit" name="submit_password" class="btn btn-warning w-100">
                        <i class="fa-solid fa-rotate"></i> Đặt lại mật khẩu
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php require_once "./include/footer.php" ?>
    <script>
        document.querySelectorAll(".toggle-password").forEach(icon => {
            icon.addEventListener("click", function() {
                const input = document.querySelector(this.getAttribute("toggle"));
                const eye = this.querySelector("i");

                if (input.type === "password") {
                    input.type = "text";
                    eye.classList.remove("fa-eye");
                    eye.classList.add("fa-eye-slash");
                } else {
                    input.type = "password";
                    eye.classList.remove("fa-eye-slash");
                    eye.classList.add("fa-eye");
                }
            });
        });
    </script>
</body>

</html>