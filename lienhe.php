<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

$check = isset($_SESSION["nguoiDung"]);
if ($check) {
    $id = $_SESSION["nguoiDung"]["id"];
    $nd = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    if (isset($_POST["gui"])) {
        $ten = empty($_POST["ten"]) ? "" : trim($_POST["ten"]);
        $sdt = empty($_POST["sdt"]) ? "" : trim($_POST["sdt"]);
        $email = empty($_POST["email"]) ? "" : trim($_POST["email"]);
        $noiDung = empty($_POST["noiDung"]) ? "" : trim($_POST["noiDung"]);

        if (empty($ten)) {
            $err["ten"] = "Vui lòng nhập tên liên hệ";
        }
        if (empty($sdt)) {
            $err["sdt"] = "Vui lòng nhập số điện liên hệ";
        }
        if (empty($email)) {
            $err["email"] = "Vui lòng nhập email liên hệ";
        }
        if (empty($noiDung)) {
            $err["noiDung"] = "Vui lòng nhập nội dung ý kiến";
        }
        if (empty($err)) {
            if ($check) {
                $db->execute("INSERT INTO lienhe(idNguoiDung,ten,sdt,email,noiDung) VALUES (?,?,?,?,?)", [$id, $ten, $sdt, $email, $noiDung]);
            } else {
                $db->execute("INSERT INTO lienhe(ten,sdt,email,noiDung) VALUES (?,?,?,?)", [$ten, $sdt, $email, $noiDung]);
            }
            $_SESSION["thongBao"] = "Ý kiến liên hệ của bạn đã được gửi đi";
            header("location: lienhe.php");
            exit;
        }
    }
}



?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liên hệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php require_once "./include/header.php" ?>
    <!-- Nhúng Bootstrap & Bootstrap Icons nếu chưa có -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <div class="container">
        <h5 class="mb-4">
            <i class="bi bi-envelope-at-fill text-primary me-2"></i>
            Liên hệ với chúng tôi
        </h5>

        <div class="row g-4 align-items-stretch">
            <!-- Thông tin + Form -->
            <div class="col-lg-6 d-flex">
                <div class="w-100 d-flex flex-column shadow p-4 bg-white rounded">
                    <div>
                        <?php if (!empty($_SESSION["thongBao"])): ?>
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                <?= $_SESSION["thongBao"] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php $_SESSION["thongBao"] = ""; ?>
                        <?php endif; ?>
                        <p><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Địa chỉ:</strong> 123 Fashion Street, HCM</p>
                        <p><i class="bi bi-telephone-fill text-success me-2"></i><strong>Hotline:</strong> +84 123 456 789</p>
                        <p><i class="bi bi-envelope-fill text-warning me-2"></i><strong>Email:</strong> support@fashionstore.vn</p>

                        <a href="#" class="btn btn-dark my-3">
                            <i class="bi bi-shop me-1"></i> Hệ thống cửa hàng
                        </a>
                    </div>

                    <form class="mt-2" action="" method="post">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-person-fill text-secondary"></i>
                                    </span>
                                    <input name="ten" type="text"
                                        value="<?= isset($ten) ? htmlspecialchars($ten) : ($check ? $nd["ten"] : "") ?>"
                                        class="form-control <?= isset($err['ten']) ? 'is-invalid' : '' ?>"
                                        placeholder="Tên" required>
                                    <?php if (isset($err["ten"])): ?>
                                        <div class="invalid-feedback"><?= $err["ten"] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-telephone-fill text-success"></i>
                                    </span>
                                    <input name="sdt" type="text"
                                        value="<?= isset($sdt) ? htmlspecialchars($sdt) : ($check ? $nd["soDienThoai"] : "") ?>"
                                        class="form-control <?= isset($err['sdt']) ? 'is-invalid' : '' ?>"
                                        placeholder="Điện thoại" required>
                                    <?php if (isset($err["sdt"])): ?>
                                        <div class="invalid-feedback"><?= $err["sdt"] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <div class="mb-3">
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-envelope-fill text-warning"></i>
                                </span>
                                <input name="email" type="email"
                                    value="<?= isset($email) ? htmlspecialchars($email) : ($check ? $nd["email"] : "") ?>"
                                    class="form-control <?= isset($err['email']) ? 'is-invalid' : '' ?>"
                                    placeholder="Email" required>
                                <?php if (isset($err["email"])): ?>
                                    <div class="invalid-feedback"><?= $err["email"] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <div class="mb-3">
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-chat-left-dots-fill text-info"></i>
                                </span>
                                <textarea name="noiDung"
                                    class="form-control <?= isset($err['noiDung']) ? 'is-invalid' : '' ?>"
                                    rows="4" placeholder="Nội dung" required><?= isset($noiDung) ? htmlspecialchars($noiDung) : '' ?></textarea>
                                <?php if (isset($err["noiDung"])): ?>
                                    <div class="invalid-feedback"><?= $err["noiDung"] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <button type="submit" name="gui" class="btn btn-primary px-4">
                            <i class="bi bi-send-fill me-1"></i> Gửi tin nhắn
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bản đồ Google Maps -->
            <div class="col-lg-6 d-flex">
                <div class="w-100 h-100 shadow rounded overflow-hidden">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d534.741001294373!2d108.06296396919936!3d12.706925456378837!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3171f74281d43eb9%3A0x6f2a5db33a3efb19!2sLetemps!5e0!3m2!1sen!2s!4v1753963972818!5m2!1sen!2s"
                        width="100%" height="100%" style="border:0; min-height: 600px;"
                        allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>


    <?php require_once "./include/footer.php" ?>
</body>

</html>