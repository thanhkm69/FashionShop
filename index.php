<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";


$dm = $db->getAll("SELECT * FROM danhmuc");

$spyt = $db->getAll("SELECT b.*,COUNT(*) as soLuongYT FROM yeuthich a JOIN sanpham b ON a.idSanPham = b.id GROUP BY a.idSanPham ORDER BY COUNT(*) DESC LIMIT 10");

$spm =  $db->getAll("SELECT c.*,COUNT(*) AS soLuongBan FROM bienthesize a JOIN mau b ON a.idMau = b.id JOIN sanpham c ON b.idSanPham = c.id GROUP BY c.id ORDER BY ngayTao DESC LIMIT 10");

$snbc = $db->getAll("SELECT d.*,SUM(a.soLuong) as soLuongBC FROM chitiethoadon a JOIN bienthesize b ON a.idSize = b. id JOIN mau c ON b.idMau = c.id JOIN sanpham d ON c.idSanPham = d.id GROUP BY d.id ORDER BY COUNT(*) DESC LIMIT 10");

$spnb = $db->getAll("SELECT c.*,SUM(soLuong) as soLuong FROM bienthesize a JOIN mau b ON a.idMau = b.id JOIN sanpham c ON b.idSanPham = c.id GROUP BY c.id ORDER BY SUM(soLuong) DESC LIMIT 10");

$spfl = $db->getAll("SELECT *, (giaGoc - giaKhuyenMai) / giaGoc * 100 AS phantram,giaKhuyenMai-giaGoc AS soTienGiam FROM sanpham ORDER BY (giaGoc - giaKhuyenMai) / giaGoc * 100 DESC LIMIT 10");



?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- SwiperJS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <title>Trang chủ</title>
    <style>
        .rounded-circle:hover {
            transform: scale(1.05);
            transition: 0.2s ease;
        }

        .custom-cart-btn {
            background-color: #fff;
            border-color: #212529;
            color: #212529;
        }

        .custom-cart-btn:hover {
            background-color: #fff !important;
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }

        #productTab .nav-link {
            border-radius: 30px;
            padding: 8px 18px;
            background-color: #f8f9fa;
            color: #000;
            margin: 0 6px;
            transition: all 0.3s ease;
        }

        #productTab .nav-link.active {
            background-color: #dc3545;
            color: #fff;
        }

        .tab-pane {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .tab-pane.show.active {
            display: block;
            opacity: 1;
        }

        .transition-slide {
            transition: transform 0.4s ease-in-out;
        }

        .card-hover:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px);
            transition: all 0.3s ease-in-out;
        }

        .bi {
            transition: transform 0.3s;
        }

        .bi:hover {
            transform: scale(1.1);
            color: #dc3545 !important;
            cursor: pointer;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .banner-hover img {
            transition: transform 0.3s ease;
        }

        .banner-hover:hover img {
            transform: scale(1.03);
        }

        .stat-box {
            background-color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 12px rgba(220, 53, 69, 0.15);
        }

        #bannerCarousel img {
            height: 500px;
            object-fit: cover;
        }

        .carousel-indicators [data-bs-target] {
            background-color: #dc3545;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-size: 60% 60%;
        }

        input::placeholder {
            color: #888;
        }

        input:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
    </style>

</head>

<body>

    <?php require_once "./include/header.php" ?>
    <div class="container">
        <!-- Slider Banner -->
        <section>
            <div class="container">
                <div id="bannerCarousel" class="carousel slide rounded-3 shadow-sm overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <a href="">
                                <img src="uploads/thoi_trang_khong_don_gian.jpg" class="d-block w-100" alt="Banner 3">
                            </a>
                        </div>
                        <div class="carousel-item">
                            <a href="">
                                <img src="uploads/thoi_trang_outlet.jpg" class="d-block w-100" alt="Banner 4">
                            </a>
                        </div>
                        <div class="carousel-item">
                            <a href="">
                                <img src="uploads/thoi_trang_don_gian.jpg" class="d-block w-100" alt="Banner 1">
                            </a>
                        </div>
                        <div class="carousel-item">
                            <a href="">
                                <img src="uploads/thoi_trang_jean.jpg" class="d-block w-100" alt="Banner 2">
                            </a>
                        </div>
                    </div>
                    <!-- Nút điều hướng -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>

                    <!-- Indicator -->
                    <div class="carousel-indicators mt-2">
                        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
                        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="3"></button>
                    </div>
                </div>
            </div>
        </section>
        <!-- Danh mục sản phẩm -->
        <section class="py-4 bg-white">
            <div class="container">
                <h5 class="mb-4"><i class="bi bi-grid-3x3-gap text-primary me-2"></i>Danh mục sản phẩm</h5>
                <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-3 text-center">
                    <?php foreach ($dm as $item): ?>
                        <div class="col">
                            <a href="sanpham.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                                <div class="rounded-circle border p-2 shadow-sm" style="width: 80px; height: 80px; overflow: hidden;">
                                    <img src="<?= $dir . $item['hinh'] ?>" class="img-fluid rounded-circle" style="object-fit: cover; width: 100%; height: 100%;" alt="<?= htmlspecialchars($item['ten']) ?>">
                                </div>
                                <small class="mt-2 text-truncate" style="max-width: 80px;"><?= htmlspecialchars($item['ten']) ?></small>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Banner quảng cáo -->
        <section class="py-4">
            <div class="container">
                <div class="p-4 bg-danger text-white rounded-4 text-center shadow-sm">
                    <h4 class="fw-bold mb-2"><i class="bi bi-fire me-2"></i>Ưu đãi cực sốc mỗi ngày</h4>
                    <p class="mb-0">Nhanh tay săn ngay hàng hot với giá siêu ưu đãi!</p>
                </div>
            </div>
        </section>
        <!-- Giới thiệu -->
        <section class="position-relative" style="background: #fff url('//bizweb.dktcdn.net/100/539/564/themes/978660/assets/section_about_bg.jpg?1751953847413') bottom left / cover no-repeat;">
            <div class="container">
                <!-- Tiêu đề section -->
                <div class="text-center">
                    <h5 class="text-uppercase text-muted fw-semibold mb-2">
                        <i class="bi bi-stars text-warning me-2"></i>Giới thiệu
                    </h5>
                    <h5 class="fw-bold text-dark">Thời trang là cách bạn thể hiện chính mình</h5>
                </div>

                <div class="row align-items-center">
                    <!-- Hình bên trái -->
                    <div class="col-md-6 mb-4 mb-md-0 text-center">
                        <img src="uploads/gt-removebg-preview.png" class="img-fluid" alt="Fashion Model" style="max-height: 420px;">
                    </div>

                    <!-- Nội dung bên phải -->
                    <div class="col-md-6">
                        <p class="text-danger fw-semibold mb-1">Khám phá</p>
                        <h5 class="fw-bold text-dark mb-3">Phong cách thời trang cho mọi cá tính</h5>
                        <p class="text-muted mb-3">
                            FashionShop mang đến cho bạn những xu hướng thời trang mới nhất, kết hợp giữa phong cách hiện đại và cá tính riêng biệt.
                        </p>
                        <p class="text-muted mb-4">
                            Tại <strong class="text-danger">FashionShop</strong>, chúng tôi tin rằng: Mỗi người đều xứng đáng được mặc đẹp theo cách của riêng mình. Hãy cùng chúng tôi tạo nên phong cách của bạn ngay hôm nay.
                        </p>
                        <a href="sanpham.php" class="btn btn-danger px-4 rounded-pill">
                            <i class="bi bi-bag-fill me-2"></i>XEM SẢN PHẨM
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- FLASH SALE SIÊU GIẢM GIÁ -->
        <section class="py-4 bg-white border-top">
            <div class="container">
                <div class="text-center mb-4">
                    <h5 class="text-uppercase fw-bold text-danger">
                        <i class="bi bi-lightning-fill me-2"></i>FLASH SALE SIÊU GIẢM GIÁ
                    </h5>
                    <p class="text-muted">Nhanh tay chớp lấy cơ hội, giảm giá cực khủng chỉ trong hôm nay!</p>
                </div>
                <div class="position-relative">
                    <!-- Nút điều hướng -->
                    <button onclick="slide('slider-flash', 'left')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="left: 0; z-index: 10;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button onclick="slide('slider-flash', 'right')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="right: 0; z-index: 10;">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <div style="overflow: hidden;">
                        <div id="slider-flash" class="d-flex transition-slide" style="gap: 10px;">
                            <?php foreach ($spfl as $item):
                                $phanTram = round($item["phantram"]);
                                $soTienGiam = abs((int)$item["soTienGiam"]);
                            ?>
                                <div class="card shadow-sm border-0 position-relative overflow-hidden" style="min-width: 220px; max-width: 220px;">
                                    <div class="position-relative">
                                        <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                            <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                        </a>
                                        <?php if ($phanTram > 0): ?>
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">-<?= $phanTram ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;"><?= htmlspecialchars($item['ten']) ?></h6>
                                            <small class="text-danger fw-semibold">-<?= number_format($soTienGiam, 0, ',', '.') ?>₫</small>
                                        </div>
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                            <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Ngăn cách bằng banner nhỏ -->
        <section>
            <div class="container">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <a href="" class="d-block rounded-3 overflow-hidden shadow-sm banner-hover">
                            <img src="uploads/phu_kien_thiet_yeu.jpg" class="img-fluid w-100" alt="Banner 1">
                        </a>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="" class="d-block rounded-3 overflow-hidden shadow-sm banner-hover">
                            <img src="uploads/thoi_trang_cong_nghe.jpg " class="img-fluid w-100" alt="Banner 2">
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Sản phẩm yêu thích nhất -->
        <section class="bg-light border-top py-5">
            <div class="container">
                <div class="text-center mb-4">
                    <h5 class="text-uppercase fw-bold text-danger">
                        <i class="bi bi-heart-fill me-2"></i>Sản phẩm yêu thích nhất
                    </h5>
                    <p class="text-muted">Top sản phẩm được khách hàng yêu thích nhiều nhất</p>
                </div>

                <div class="position-relative">
                    <button onclick="slide('slider-yt', 'left')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="left: 0; z-index: 10;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button onclick="slide('slider-yt', 'right')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="right: 0; z-index: 10;">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <div style="overflow: hidden;">
                        <div id="slider-yt" class="d-flex transition-slide" style="gap: 10px;">
                            <?php foreach ($spyt as $item): ?>
                                <?php $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100); ?>
                                <div style="min-width: 220px; max-width: 220px;">
                                    <div class="card h-100 shadow-sm border-0 position-relative card-hover overflow-hidden">
                                        <div class="position-relative">
                                            <a href="chitietsp.php?id=<?= $item['id'] ?>">
                                                <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                            </a>
                                            <?php if ($phanTram > 0): ?>
                                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">-<?= $phanTram ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;"> <?= htmlspecialchars($item['ten']) ?> </h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-heart-fill text-danger me-1"></i><?= $item['soLuongYT'] ?>
                                                </small>
                                            </div>
                                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                                <span class="text-danger fw-bold"> <?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫ </span>
                                                <small class="text-muted text-decoration-line-through"> <?= number_format($item['giaGoc'], 0, ',', '.') ?>₫ </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Thống kê thông tin -->
        <section>
            <div class="container">
                <div class="row row-cols-2 row-cols-md-4 g-4 text-center">
                    <!-- Tông danh mục -->
                    <div class="col">
                        <div class="p-3 border rounded-4 shadow-sm h-100 stat-box">
                            <div class="mb-2 text-danger fs-3"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                            <h6 class="fw-bold mb-1">Tổng danh mục</h6>
                            <p class="text-muted small mb-0">Hiện có 12 danh mục</p>
                        </div>
                    </div>
                    <!-- Tổng sản phẩm -->
                    <div class="col">
                        <div class="p-3 border rounded-4 shadow-sm h-100 stat-box">
                            <div class="mb-2 text-danger fs-3"><i class="bi bi-box-seam-fill"></i></div>
                            <h6 class="fw-bold mb-1">Tổng sản phẩm</h6>
                            <p class="text-muted small mb-0">1,250+ sản phẩm đang bán</p>
                        </div>
                    </div>
                    <!-- Đánh giá -->
                    <div class="col">
                        <div class="p-3 border rounded-4 shadow-sm h-100 stat-box">
                            <div class="mb-2 text-danger fs-3"><i class="bi bi-chat-dots-fill"></i></div>
                            <h6 class="fw-bold mb-1">Đánh giá</h6>
                            <p class="text-muted small mb-0">Hơn 4,200 đánh giá tích cực</p>
                        </div>
                    </div>
                    <!-- Số sao -->
                    <div class="col">
                        <div class="p-3 border rounded-4 shadow-sm h-100 stat-box">
                            <div class="mb-2 text-danger fs-3"><i class="bi bi-star-fill"></i></div>
                            <h6 class="fw-bold mb-1">Số sao</h6>
                            <p class="text-muted small mb-0">Trung bình 4.8/5 từ khách hàng</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Tab sản phẩm mới, bán chạy, nổi bật -->
        <section class="py-4 bg-white border-top">
            <div class="container">
                <ul class="nav nav-pills justify-content-center mb-4" id="productTab">
                    <li class="nav-item">
                        <button class="nav-link active" data-target="#sp-moi">Sản phẩm mới</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-target="#sp-banchay">Bán chạy</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-target="#sp-noibat">Nổi bật</button>
                    </li>
                </ul>

                <!-- Nội dung tabs -->
                <div class="tab-content">
                    <!-- Sản phẩm mới -->
                    <div id="sp-moi" class="tab-pane fade show active">
                        <div class="position-relative">
                            <button onclick="slide('slider-moi', 'left')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="left: 0; z-index: 10;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button onclick="slide('slider-moi', 'right')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="right: 0; z-index: 10;">
                                <i class="bi bi-chevron-right"></i>
                            </button>

                            <div style="overflow: hidden;">
                                <div id="slider-moi" class="d-flex transition-slide" style="gap: 10px;">
                                    <?php foreach ($spm as $item): ?>
                                        <?php $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100); ?>
                                        <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden" style="min-width: 220px; max-width: 220px;">
                                            <div class="position-relative">
                                                <a href="chitietsp.php?id=<?= $item['id'] ?>">
                                                    <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                                                </a>
                                                <?php if ($phanTram > 0): ?>
                                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">-<?= $phanTram ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;"><?= htmlspecialchars($item['ten']) ?></h6>
                                                    <small class="text-muted"><i class="bi bi-bag-check-fill text-success me-1"></i><?= $item['soLuongBan'] ?></small>
                                                </div>
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                                    <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sản phẩm bán chạy -->
                    <div id="sp-banchay" class="tab-pane fade">
                        <div class="position-relative">
                            <button onclick="slide('slider-bc', 'left')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="left: 0; z-index: 10;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button onclick="slide('slider-bc', 'right')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="right: 0; z-index: 10;">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <div style="overflow: hidden;">
                                <div id="slider-bc" class="d-flex transition-slide" style="gap: 10px;">
                                    <?php foreach ($snbc as $item): ?>
                                        <?php $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100); ?>
                                        <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden" style="min-width: 220px; max-width: 220px;">
                                            <div class="position-relative">
                                                <a href="chitietsp.php?id=<?= $item['id'] ?>">
                                                    <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                                                </a>
                                                <?php if ($phanTram > 0): ?>
                                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">-<?= $phanTram ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;"><?= htmlspecialchars($item['ten']) ?></h6>
                                                    <small class="text-muted"><i class="bi bi-box-seam-fill text-success me-1"></i><?= $item['soLuongBC'] ?></small>
                                                </div>
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                                    <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sản phẩm nổi bật -->
                    <div id="sp-noibat" class="tab-pane fade">
                        <div class="position-relative">
                            <button onclick="slide('slider-nb', 'left')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="left: 0; z-index: 10;">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button onclick="slide('slider-nb', 'right')" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 translate-middle-y" style="right: 0; z-index: 10;">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <div style="overflow: hidden;">
                                <div id="slider-nb" class="d-flex transition-slide" style="gap: 10px;">
                                    <?php foreach ($spnb as $item): ?>
                                        <?php $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100); ?>
                                        <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden" style="min-width: 220px; max-width: 220px;">
                                            <div class="position-relative">
                                                <a href="chitietsp.php?id=<?= $item['id'] ?>">
                                                    <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                                                </a>
                                                <?php if ($phanTram > 0): ?>
                                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">-<?= $phanTram ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;"><?= htmlspecialchars($item['ten']) ?></h6>
                                                    <small class="text-muted"><i class="bi bi-boxes text-primary me-1"></i><?= $item['soLuong'] ?></small>
                                                </div>
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                                    <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Banner giới thiệu voucher -->
        <section class="bg-light">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <!-- Text giới thiệu -->
                    <div class="col-md-7">
                        <h5 class="fw-bold text-danger mb-2">
                            <i class="bi bi-ticket-perforated-fill me-2"></i>ƯU ĐÃI VOUCHER CỰC KHỦNG
                        </h5>
                        <p class="text-muted mb-3">
                            Nhận ngay hàng loạt <span class="text-danger fw-semibold">mã giảm giá</span> hấp dẫn mỗi ngày. Áp dụng cho mọi đơn hàng, đơn cao có thể giảm tới 50.000₫!
                        </p>
                        <a href="voucher.php" class="btn btn-danger rounded-pill px-4">
                            <i class="bi bi-tag-fill me-1"></i>Khám phá ngay
                        </a>
                    </div>

                    <!-- Hình ảnh minh họa -->
                    <div class="col-md-5 text-center mt-4 mt-md-0">
                        <img src="uploads/c37d8f35-9b65-4532-a341-22aca63fe71e_VN-1976-688.jpg_2200x2200q80.jpg_.avif" alt="Voucher" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </section>
        <!-- Ưu điểm nổi bật -->
        <section class="py-4 bg-white border-top border-bottom">
            <div class="container">
                <div class="row text-center justify-content-center g-4">
                    <div class="col-6 col-md-3">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-box-seam fs-1 text-danger mb-2"></i>
                            <h6 class="fw-bold">Miễn phí vận chuyển</h6>
                            <p class="text-muted small mb-0">Áp dụng cho mọi đơn hàng từ 500k</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-arrow-repeat fs-1 text-danger mb-2"></i>
                            <h6 class="fw-bold">Đổi hàng dễ dàng</h6>
                            <p class="text-muted small mb-0">7 ngày đổi hàng vì bất kỳ lí do gì</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-headset fs-1 text-danger mb-2"></i>
                            <h6 class="fw-bold">Hỗ trợ nhanh chóng</h6>
                            <p class="text-muted small mb-0">HOTLINE 24/7 : 0964942121</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-credit-card fs-1 text-danger mb-2"></i>
                            <h6 class="fw-bold">Thanh toán đa dạng</h6>
                            <p class="text-muted small mb-0">COD, Napas, Visa, Chuyển khoản</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Form Đăng ký nhận thông tin -->
        <section class="bg-light py-4">
            <div class="container">
                <div class="text-center mb-4">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-envelope-paper-fill text-danger me-2"></i>Đăng ký nhận thông tin ưu đãi
                    </h5>
                    <p class="text-muted mb-0">Hãy để lại email để nhận thông báo về khuyến mãi và voucher hấp dẫn mỗi tuần!</p>
                </div>

                <form class="row justify-content-center g-2" action="" method="post">
                    <div class="col-md-6 col-sm-8">
                        <input type="email" name="email" class="form-control form-control-lg rounded-pill shadow-sm" placeholder="Nhập email của bạn..." required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill shadow-sm px-4">
                            <i class="bi bi-send-fill me-1"></i>Đăng ký
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <?php require_once "./include/footer.php" ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabButtons = document.querySelectorAll('#productTab .nav-link');
            const tabContents = document.querySelectorAll('.tab-pane');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    tabButtons.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('show', 'active'));

                    this.classList.add('active');
                    const target = this.getAttribute('data-target');
                    document.querySelector(target).classList.add('show', 'active');
                });
            });
        });


        const sliders = {}; // Lưu vị trí mỗi slider

        function getVisibleCards(sliderId) {
            const containerWidth = document.getElementById(sliderId).parentElement.offsetWidth;
            const cardWidth = 220 + 16;
            return Math.floor(containerWidth / cardWidth);
        }

        function slide(sliderId, direction) {
            const slider = document.getElementById(sliderId);
            const totalItems = slider.children.length;
            const visibleCards = getVisibleCards(sliderId);

            if (!(sliderId in sliders)) sliders[sliderId] = 0;

            const maxIndex = totalItems - visibleCards;

            if (direction === 'left' && sliders[sliderId] > 0) {
                sliders[sliderId]--;
            } else if (direction === 'right' && sliders[sliderId] < maxIndex) {
                sliders[sliderId]++;
            }

            const cardWidth = slider.children[0].offsetWidth + 16;
            slider.style.transform = `translateX(-${sliders[sliderId] * cardWidth}px)`;
        }

        window.addEventListener('resize', () => {
            for (let sliderId in sliders) {
                slide(sliderId, 'reset');
            }
        });
    </script>

</body>

</html>