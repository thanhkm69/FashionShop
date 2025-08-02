<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

$dm = $db->getAll("SELECT * FROM danhmuc");

$spyt = $db->getAll("SELECT b.*,COUNT(*) as soLuongYT FROM yeuthich a JOIN sanpham b ON a.idSanPham = b.id GROUP BY a.idSanPham ORDER BY COUNT(*) DESC LIMIT 5");

$spm =  $db->getAll("SELECT c.*,COUNT(*) AS soLuongBan FROM bienthesize a JOIN mau b ON a.idMau = b.id JOIN sanpham c ON b.idSanPham = c.id GROUP BY c.id ORDER BY ngayTao DESC LIMIT 5");

$snbc = $db->getAll("SELECT d.*,SUM(a.soLuong) as soLuongBC FROM chitiethoadon a JOIN bienthesize b ON a.idSize = b. id JOIN mau c ON b.idMau = c.id JOIN sanpham d ON c.idSanPham = d.id GROUP BY d.id ORDER BY COUNT(*) DESC LIMIT 5");

$spnb = $db->getAll("SELECT c.*,SUM(soLuong) as soLuong FROM bienthesize a JOIN mau b ON a.idMau = b.id JOIN sanpham c ON b.idSanPham = c.id GROUP BY c.id ORDER BY SUM(soLuong) DESC LIMIT 5");

$spfl = $db->getAll("SELECT *, (giaGoc - giaKhuyenMai) / giaGoc * 100 AS phantram,giaKhuyenMai-giaGoc AS soTienGiam FROM sanpham ORDER BY (giaGoc - giaKhuyenMai) / giaGoc * 100 DESC LIMIT 5");


?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trang chủ</title>
    <style>
        .rounded-circle:hover {
            transform: scale(1.05);
            transition: 0.2s ease;
        }

        .custom-cart-btn {
            background-color: #fff;
            border-color: #212529;
            /* mặc định btn-outline-dark */
            color: #212529;
        }

        .custom-cart-btn:hover {
            background-color: #fff !important;
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }
    </style>
</head>

<body>

    <?php require_once "./include/header.php" ?>
    <div class="container">
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
        <!-- Giới thiệu -->
        <section class="py-5 position-relative" style="background: #fff url('//bizweb.dktcdn.net/100/539/564/themes/978660/assets/section_about_bg.jpg?1751953847413') bottom left / cover no-repeat;">
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
        <!-- Sản phẩm giảm giá -->
        <section class="py-5 bg-white">
            <div class="container">
                <!-- Tiêu đề -->
                <div class="text-center mb-4">
                    <h5 class="text-uppercase fw-bold text-warning">
                        <i class="bi bi-lightning-fill me-2"></i>FLASH SALE SIÊU GIẢM GIÁ
                    </h5>
                </div>

                <!-- Grid sản phẩm -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
                    <?php foreach ($spfl as $item):
                        $phanTram = round($item["phantram"]);
                        $soTienGiam = abs((int)$item["soTienGiam"]);
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden">
                                <div class="position-relative">
                                    <!-- Ảnh sản phẩm -->
                                    <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                        <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                    </a>

                                    <!-- Badge phần trăm giảm -->
                                    <?php if ($phanTram > 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">
                                            -<?= $phanTram ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nút giỏ hàng -->
                                    <a href="them_vao_gio.php?id=<?= $item['id'] ?>" class="btn btn-outline-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow custom-cart-btn" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>
                                </div>

                                <!-- Nội dung -->
                                <div class="card-body d-flex flex-column">
                                    <!-- Tên + số tiền giảm -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($item['ten']) ?>
                                        </h6>
                                        <small class="text-danger fw-semibold">
                                            -<?= number_format($soTienGiam, 0, ',', '.') ?>₫
                                        </small>
                                    </div>

                                    <!-- Giá -->
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                        <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                    </div>

                                    <!-- Nút mua ngay -->
                                    <a href="mua_ngay.php?id=<?= $item['id'] ?>" class="btn btn-danger w-100 rounded-pill mt-auto">
                                        <i class="bi bi-bag-fill me-1"></i>MUA NGAY
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Sản phẩm yêu thích nhất -->
        <section class="py-5 bg-light">
            <div class="container">
                <!-- Tiêu đề -->
                <div class="text-center mb-4">
                    <h5 class="text-uppercase text-danger fw-bold">
                        <i class="bi bi-heart-fill me-2"></i>Sản phẩm yêu thích nhất
                    </h5>
                </div>

                <!-- Grid sản phẩm -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
                    <?php foreach ($spyt as $item):
                        $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100);
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 position-relative card-hover overflow-hidden">
                                <div class="position-relative">
                                    <!-- Ảnh sản phẩm -->
                                    <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                        <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" alt="<?= $item['ten'] ?>" style="height: 220px; object-fit: cover;">
                                    </a>

                                    <!-- Phần trăm giảm giá -->
                                    <?php if ($phanTram > 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">
                                            -<?= $phanTram ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nút giỏ hàng -->
                                    <a style="background-color: white;" href="them_vao_gio.php?id=<?= $item['id'] ?>"
                                        class="btn btn-outline-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow custom-cart-btn"
                                        title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>

                                </div>

                                <!-- Nội dung -->
                                <div class="card-body d-flex flex-column position-relative">
                                    <!-- Tên + yêu thích -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($item['ten']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-heart-fill text-danger me-1"></i><?= $item['soLuongYT'] ?>
                                        </small>
                                    </div>

                                    <!-- Giá -->
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-danger fw-bold">
                                            <?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫
                                        </span>
                                        <small class="text-muted text-decoration-line-through">
                                            <?= number_format($item['giaGoc'], 0, ',', '.') ?>₫
                                        </small>
                                    </div>

                                    <!-- Nút mua ngay -->
                                    <a href="mua_ngay.php?id=<?= $item['id'] ?>" class="btn btn-danger w-100 rounded-pill mt-auto">
                                        <i class="bi bi-bag-check me-1"></i> MUA NGAY
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Sản phẩm mới nhất -->
        <section class="py-5 bg-light">
            <div class="container">
                <!-- Tiêu đề -->
                <div class="text-center mb-4">
                    <h5 class="text-uppercase text-dark fw-bold">
                        <i class="bi bi-stars text-warning me-2"></i>Sản phẩm mới nhất
                    </h5>
                </div>

                <!-- Grid sản phẩm -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
                    <?php foreach ($spm as $item):
                        $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100);
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden">
                                <div class="position-relative">
                                    <!-- Ảnh sản phẩm -->
                                    <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                        <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                    </a>

                                    <!-- Phần trăm giảm -->
                                    <?php if ($phanTram > 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">
                                            -<?= $phanTram ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nút giỏ hàng -->
                                    <a href="them_vao_gio.php?id=<?= $item['id'] ?>" class="btn btn-outline-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow custom-cart-btn" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>
                                </div>

                                <!-- Nội dung -->
                                <div class="card-body d-flex flex-column">
                                    <!-- Tên + số lượng bán -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($item['ten']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-bag-check-fill text-success me-1"></i><?= $item['soLuongBan'] ?>
                                        </small>
                                    </div>

                                    <!-- Giá -->
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                        <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                    </div>

                                    <!-- Nút mua ngay -->
                                    <a href="mua_ngay.php?id=<?= $item['id'] ?>" class="btn btn-danger w-100 rounded-pill mt-auto">
                                        <i class="bi bi-bag-fill me-1"></i>MUA NGAY
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Sản phẩm bán chạy -->
        <section class="py-5 bg-white">
            <div class="container">
                <!-- Tiêu đề -->
                <div class="text-center mb-4">
                    <h5 class="text-uppercase text-success fw-bold">
                        <i class="bi bi-graph-up-arrow me-2"></i>Sản phẩm bán chạy nhất
                    </h5>
                </div>

                <!-- Grid sản phẩm -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
                    <?php foreach ($snbc as $item):
                        $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100);
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden">
                                <div class="position-relative">
                                    <!-- Ảnh sản phẩm -->
                                    <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                        <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                    </a>

                                    <!-- Badge phần trăm giảm -->
                                    <?php if ($phanTram > 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">
                                            -<?= $phanTram ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nút giỏ hàng -->
                                    <a href="them_vao_gio.php?id=<?= $item['id'] ?>" class="btn btn-outline-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow custom-cart-btn" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>
                                </div>

                                <!-- Nội dung -->
                                <div class="card-body d-flex flex-column">
                                    <!-- Tên + số lượng bán -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($item['ten']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-box-seam-fill text-success me-1"></i><?= $item['soLuongBC'] ?>
                                        </small>
                                    </div>

                                    <!-- Giá -->
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                        <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                    </div>

                                    <!-- Nút mua ngay -->
                                    <a href="mua_ngay.php?id=<?= $item['id'] ?>" class="btn btn-danger w-100 rounded-pill mt-auto">
                                        <i class="bi bi-bag-fill me-1"></i>MUA NGAY
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Sản phẩm nổi bật (Số lượng tồn kho) -->
        <section class="py-5 bg-light">
            <div class="container">
                <!-- Tiêu đề -->
                <div class="text-center mb-4">
                    <h5 class="text-uppercase text-primary fw-bold">
                        <i class="bi bi-star-fill me-2"></i>Sản phẩm nổi bật
                    </h5>
                </div>

                <!-- Grid sản phẩm -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
                    <?php foreach ($spnb as $item):
                        $phanTram = round((($item["giaGoc"] - $item["giaKhuyenMai"]) / $item["giaGoc"]) * 100);
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden">
                                <div class="position-relative">
                                    <!-- Ảnh sản phẩm -->
                                    <a href="chitietsp.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                                        <img src="<?= $dir . $item['hinh'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $item['ten'] ?>">
                                    </a>

                                    <!-- Phần trăm giảm -->
                                    <?php if ($phanTram > 0): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-end">
                                            -<?= $phanTram ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nút giỏ hàng -->
                                    <a href="them_vao_gio.php?id=<?= $item['id'] ?>" class="btn btn-outline-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow custom-cart-btn" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>
                                </div>

                                <!-- Nội dung -->
                                <div class="card-body d-flex flex-column">
                                    <!-- Tên + số lượng -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title text-dark mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($item['ten']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-boxes text-primary me-1"></i><?= $item['soLuong'] ?>
                                        </small>
                                    </div>

                                    <!-- Giá -->
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <span class="text-danger fw-bold"><?= number_format($item['giaKhuyenMai'], 0, ',', '.') ?>₫</span>
                                        <small class="text-muted text-decoration-line-through"><?= number_format($item['giaGoc'], 0, ',', '.') ?>₫</small>
                                    </div>

                                    <!-- Nút mua ngay -->
                                    <a href="mua_ngay.php?id=<?= $item['id'] ?>" class="btn btn-danger w-100 rounded-pill mt-auto">
                                        <i class="bi bi-bag-fill me-1"></i>MUA NGAY
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <?php require_once "./include/footer.php" ?>
</body>

</html>