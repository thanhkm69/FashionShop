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
        <a href="<?= $dirMenu ?>index.php">
            <img src="<?= $dir ?>logo.jpg" alt="Logo" style="width: 170px; height: auto;">
        </a>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'thongtin.php') ? 'active' : ''; ?>" href="./thongtin.php">
                <i class="bi bi-person-circle me-2"></i> Thông tin
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'diachi.php') ? 'active' : ''; ?>" href="./diachi.php">
                <i class="bi bi-geo-alt-fill me-2"></i> Địa chỉ
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'voucher.php') ? 'active' : ''; ?>" href="./voucher.php">
                <i class="bi bi-ticket-detailed-fill me-2"></i> Voucher
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'yeuthich.php') ? 'active' : ''; ?>" href="./yeuthich.php">
                <i class="bi bi-heart-fill me-2"></i> Yêu thích
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark <?php echo ($currentPage == 'donhang.php') ? 'active' : ''; ?>" href="./donhang.php">
                <i class="bi bi-bag-check-fill me-2"></i> Đơn hàng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $dirMenu ?>index.php">
                <i class="bi bi-house-fill me-2"></i> Trang chủ
            </a>
        </li>
    </ul>
</div>