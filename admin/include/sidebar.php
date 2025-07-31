<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar {
        background-color: #f1f4ff;
        border-right: 1px solid #e0e3f1;
    }

    .sidebar .nav-link {
        padding: 12px 18px;
        font-weight: 500;
        border-radius: 6px;
        color: #2c2e33;
        transition: all 0.3s ease;
        transform: translateY(0);
    }

    .sidebar .nav-link i {
        transition: all 0.3s ease;
    }

    /* Hover + Active đổi màu chữ và icon */
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: #dc3545 !important;
        background-color: rgba(0, 0, 0, 0.03);
        transform: translateX(6px);
    }

    .sidebar .nav-link:hover i,
    .sidebar .nav-link.active i {
        color: #dc3545 !important;
    }

    .nav-item a,
    .icon-link,
    .dropdown-toggle {
        color: #000 !important;
        font-weight: 500;
        transition: all 0.3s ease;
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
        top: -6px;
        right: -6px;
        background: red;
        color: white;
        font-size: 0.7rem;
        padding: 4px 6px;
        line-height: 1;
    }

    .dashboard-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        letter-spacing: 0.5px;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15);
    }
</style>


<div class="sidebar bg-light border-end pt-3 px-2" style="width: 300px; min-height: 100vh;">
    <div class="text-center mb-4">
        <a href="../../index.php">
            <img src="../../uploads/logo.jpg" alt="Logo" style="width: 170px; height: auto;">
        </a>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>" href="../dashboard/dashboard.php">
                <i class="bi bi-speedometer2 me-2 text-dark"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'nguoidung.php') ? 'active' : ''; ?>" href="../nguoidung/nguoidung.php">
                <i class="bi bi-people-fill me-2"></i> Người dùng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'danhmuc.php') ? 'active' : ''; ?>" href="../danhmuc/danhmuc.php">
                <i class="bi bi-folder-fill me-2"></i> Danh mục
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'sanpham.php') ? 'active' : ''; ?>" href="../sanpham/sanpham.php">
                <i class="bi bi-box-seam me-2 text-dark"></i> Sản phẩm
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'voucher.php') ? 'active' : ''; ?>" href="../voucher/voucher.php">
                <i class="bi bi-ticket-detailed-fill me-2"></i> Mã voucher
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'hoadon.php') ? 'active' : ''; ?>" href="../hoadon/hoadon.php">
                <i class="bi bi-cart-fill me-2"></i> Đơn hàng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'binhluan.php') ? 'active' : ''; ?>" href="../binhluan/binhluan.php">
                <i class="bi bi-chat-left-dots-fill me-2"></i> Đánh giá
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?= ($currentPage == 'lienhe.php') ? 'active' : ''; ?>" href="../lienhe/lienhe.php">
                <i class="bi bi-envelope-at-fill me-2"></i> Liên hệ
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'thongke.php') ? 'active' : ''; ?>" href="thongke.php">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Thống kê
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'caidat.php') ? 'active' : ''; ?>" href="caidat.php">
                <i class="bi bi-gear-fill me-2"></i> Cài đặt
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../index.php">
                <i class="bi bi-house-door-fill me-2"></i> Trang chủ
            </a>
        </li>
        <li class="nav-item">
            <a id="logout-link" class="nav-link" href="../../dangxuat.php">
                <i class="bi bi-box-arrow-right me-2 text-dark"></i> Đăng xuất
            </a>
        </li>

    </ul>
</div>