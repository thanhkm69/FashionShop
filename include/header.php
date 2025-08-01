<?php 
if (isset($_SESSION["nguoiDung"])) {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
    $yt = $db->getValue("SELECT COUNT(*) FROM yeuthich WHERE idNguoiDung = ?", [$id]);
    $gh = $db->getValue("SELECT COUNT(*) FROM giohang WHERE idNguoiDung = ?", [$id]);
}
?>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
      color: #000;
    }

    .nav-item a,
    .icon-link,
    .dropdown-toggle {
      color: #000 !important;
      font-weight: 500;
      transition: all 0.3s;
      text-decoration: none !important;
    }

    .nav-item a:hover,
    .icon-link:hover,
    .dropdown-toggle:hover {
      color: #dc3545 !important;
      transform: translateY(-2px);
    }

    .icon-link {
      position: relative;
    }

    .icon-link .badge {
      position: absolute;
      top: -5px;
      right: -10px;
      background: red;
      color: white;
      font-size: 0.7rem;
    }

    .header-icons {
      gap: 24px;
      /* Tăng từ 10px lên 24px */
    }

    .dropdown-menu {
      min-width: 180px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: all 0.3s ease;
      animation: none !important;
    }

    .dropdown:hover .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-toggle::after {
      margin-left: 0.3em;
    }

    .dropdown-menu .dropdown-item:hover {
      background-color: #f8f9fa;
      color: #dc3545;
    }

    .dropdown-item i {
      margin-right: 8px;
    }

    .username-label {
      font-weight: 600;
      margin-left: 8px;
      color: #000;
    }

    .nav-item.dropdown:hover .dropdown-menu {
      display: block;
      margin-top: 0;
    }

    .footer-link {
      color: #212529;
      text-decoration: none;
      transition: 0.3s;
    }

    .footer-link:hover {
      color: #dc3545;
      text-decoration: underline;
    }

    .footer-icon {
      color: #212529;
      margin-right: 15px;
      font-size: 1.2rem;
      transition: all 0.3s ease;
    }

    .footer-icon:hover {
      color: #dc3545;
      transform: translateY(-2px);
    }

    body {
      background-color: #f8f9fa;
    }

    .register-container {
      max-width: 900px;
    }

    .form-control:focus {
      box-shadow: none;
    }

    .custom-navbar {
      height: 70px;
      background-color: #fff;
    }

    .logo-img {
      height: 100%;
      max-height: 70px;
      object-fit: contain;
    }

    /* ✅ Khi ở màn hình nhỏ (dưới 992px), chỉnh lại kiểu */
    @media (max-width: 991.98px) {
      .custom-navbar {
        min-height: 70px;
        height: auto;
        /* Cho phép mở rộng nếu menu mở */
        flex-direction: column;
        align-items: flex-start;
      }

      .navbar-collapse {
        width: 100%;
        background-color: #fff;
        padding: 10px 15px;
      }

      .logo-img {
        max-height: 60px;
      }
    }

    .navbar {
      margin-bottom: 40px;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      object-fit: cover;
      border-radius: 50%;
      border: 1px solid #ccc;
    }
  </style>

  <!-- Header -->
  <nav class="navbar navbar-expand-lg bg-light border-bottom sticky-top custom-navbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="<?= $dirMenu ?>index.php">
        <img src="<?=$dir?>logo.jpg" alt="Logo" class="logo-img">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
          <li class="nav-item"><a class="nav-link active" href="<?= $dirMenu ?>index.php">Trang chủ</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="<?= $dirMenu ?>sanpham.php">Sản phẩm</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Áo</a></li>
              <li><a class="dropdown-item" href="#">Giày</a></li>
            </ul>
          </li>
            <li class="nav-item"><a class="nav-link" href="<?= $dirMenu ?>voucher.php"">Voucher</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $dirMenu ?>lienhe.php"">Liên hệ</a></li>
        </ul>

        <!-- Khối icon chức năng -->
        <div class="d-flex align-items-center header-icons icon-section gap-3">

          <!-- Đăng nhập / Đăng ký -->
          <?php if (!isset($_SESSION["nguoiDung"])) { ?>
            <div class="login-register-section">
              <ul class="navbar-nav flex-row">
                <li class="nav-item">
                  <a class="nav-link" href="<?= $dirMenu ?>dangki.php"><i class="fa-solid fa-user-plus me-1"></i>Đăng ký</a>
                </li>
                <li class="nav-item me-2">
                  <a class="nav-link" href="<?= $dirMenu ?>dangnhap.php"><i class="fa-solid fa-right-to-bracket me-1"></i>Đăng nhập</a>
                </li>
              </ul>
            </div>
          <?php } else { ?>
            <!-- Yêu thích -->
            <div class="wishlist-section me-3">
              <a href="<?= $dirMenu ?>user/yeuthich.php" class="icon-link position-relative">
                <i class="fa-regular fa-heart fs-5"></i>
                <span class="badge rounded-pill"><?= $yt ?></span>
              </a>
            </div>

            <!-- Giỏ hàng -->
            <div class="cart-section me-3">
              <a href="<?= $dirMenu ?>user/giohang.php" class="icon-link position-relative">
                <i class="fa-solid fa-cart-shopping fs-5"></i>
                <span class="badge rounded-pill"><?= $gh ?></span>
              </a>
            </div>
            <!-- Tài khoản -->
            <div class="account-section dropdown">
              <a class="dropdown-toggle icon-link text-decoration-none d-flex align-items-center" href="#"
                role="button" data-bs-toggle="dropdown">
                <img src="<?=$dir.$nguoiDung["hinh"] ?>" alt="" class="user-avatar rounded-circle">
                <span class="ms-2"><?= $nguoiDung["ten"] ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($nguoiDung["phanQuyen"] == "Admin") { ?>
                  <li><a class="dropdown-item" href="<?= $dirMenu ?>admin/dashboard/dashboard.php"><i class="fa-solid fa-gear me-2"></i>Quản lý</a></li>
                <?php } ?>
                  <li><a class="dropdown-item" href="<?= $dirMenu ?>user/thongtin.php"><i class="fa-regular fa-user me-2"></i>Thông tin tài khoản</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="<?= $dirMenu ?>dangxuat.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
              </ul>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </nav>