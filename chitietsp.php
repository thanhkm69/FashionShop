<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

// Lấy thông tin sản phẩm
$id = $_GET["id"];
$sp = $db->getOne("SELECT a.*, b.ten as tenDM FROM sanpham a JOIN danhmuc b ON a.idDanhMuc = b.id WHERE a.id = ?", [$id]);
$phanTram  = $sp["giaKhuyenMai"] / $sp["giaGoc"] * 100;

//Lấy mô tả size

$moTaSize = $db->getAll("SELECT c.size,c.moTaSize FROM sanpham a JOIN mau b ON a.id = b.idSanPham JOIN bienthesize c ON b.id = c.idMau WHERE a.id = ? GROUP BY c.size", [$id]);

// Lấy danh sách màu
$mauList = $db->getAll("SELECT * FROM mau WHERE idSanPham = ?", [$id]);

// Gán màu được chọn
$selectedMau = $_POST["mau"] ?? ($mauList[0]["id"] ?? null);

// Lấy danh sách size theo màu
$sizeList = $db->getAll("SELECT * FROM bienthesize WHERE idMau = ?", [$selectedMau]);

// Gán size được chọn
$selectedSize = $_POST["size"] ?? null;

// Nếu size không tồn tại trong danh sách size hiện tại → gán mặc định dòng đầu
$validSizeIDs = array_column($sizeList, 'id');
if (!in_array($selectedSize, $validSizeIDs)) {
    $selectedSize = $sizeList[0]["id"] ?? null;
}

$selectedSizeInfo = $db->getOne("SELECT * FROM bienthesize WHERE id = ?", [$selectedSize]);

$spLQ = $db->getAll("SELECT a.*,b.ten as tenDM FROM sanpham a JOIN danhmuc b ON a.idDanhMuc = b.id WHERE a.idDanhMuc = ? AND a.id != ?", [$sp["idDanhMuc"], $id]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["yeuthich"])) {
        if (!isset($_SESSION["nguoiDung"])) {
            header("location: dangnhap.php");
            exit();
        }
        $idNguoiDung = $_SESSION["nguoiDung"]["id"];
        $check = $db->getOne("SELECT * FROM yeuthich WHERE idSanPham = ? AND idNguoiDung = ?", [$id, $idNguoiDung]);
        if ($check) {
            $_SESSION["thongBao"] = "Đã trong kho yêu thích";
            header("location: chitietsp.php?id=$id");
            exit();
        } else {
            $db->execute("INSERT INTO yeuthich(idSanPham, idNguoiDung) VALUES (?, ?)", [$id, $idNguoiDung]);
            $_SESSION["thongBao"] = "Yêu thích thành công";
            header("location: chitietsp.php?id=$id");
            exit();
        }
    }

    if (isset($_POST["addToCart"])) {
        if (!isset($_SESSION["nguoiDung"])) {
            header("location: dangnhap.php");
            exit();
        }

        // Lấy dữ liệu người dùng
        $idNguoiDung = $_SESSION["nguoiDung"]["id"]; // bạn lưu user vào session như thế này đúng không?

        // Lấy dữ liệu từ form
        $idSize    = $_POST["size"];
        $soLuong   = $_POST["soLuong"];
        $gia       = $sp["giaKhuyenMai"] == 0 ? $sp["giaGoc"] : $sp["giaKhuyenMai"];


        // Kiểm tra hợp lệ
        if ($idSize && $soLuong > 0 && $gia >= 0) {

            // Kiểm tra nếu sản phẩm đã có trong giỏ thì cộng dồn
            $sql = "SELECT * FROM giohang WHERE idNguoiDung = ? AND idSize = ?";
            $giohangItem = $db->getOne($sql, [$idNguoiDung, $idSize]);

            if ($giohangItem) {
                // Đã có -> cập nhật số lượng
                $newSoLuong = $giohangItem["soLuong"] + $soLuong;
                $db->execute(
                    "UPDATE giohang SET soLuong = ? WHERE id = ?",
                    [$newSoLuong, $giohangItem["id"]]
                );
            } else {
                // Chưa có -> thêm mới
                $db->execute(
                    "INSERT INTO giohang (idNguoiDung, idSize, soLuong, gia) VALUES (?, ?, ?, ?)",
                    [$idNguoiDung, $idSize, $soLuong, $gia]
                );
            }
            $_SESSION["thongBao"] = "Đã thêm vào giỏ hàng!";
            header("location: chitietsp.php?id=$id");
            exit();
        } else {
            $_SESSION["thongBao"] = "Vui lòng chọn đủ thông tin sản phẩm!";
            header("location: chitietsp.php?id=$id");
            exit();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .product-detail-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .product-img-sticky {
            position: sticky;
            top: 80px;
        }

        .product-img {
            border-radius: 12px;
            border: 1px solid #eee;
            transition: transform 0.3s ease;
        }

        .product-img:hover {
            transform: scale(1.03);
        }

        .img-thumbnail-color {
            border-radius: 8px;
            border: 2px solid transparent;
            transition: border-color 0.2s ease;
        }

        .img-thumbnail-color.active,
        .img-thumbnail-color:hover {
            border-color: #ffc107;
        }

        h3.mb-2 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .badge.bg-success {
            font-size: 1rem;
            padding: 0.5em 0.75em;
            border-radius: 8px;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-warning {
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            font-size: 0.9rem;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }

        .form-check-inline input[type="radio"] {
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .product-img-sticky {
                position: static;
            }

            .product-img {
                max-height: 250px;
            }

            .product-detail-card {
                padding: 15px;
            }
        }

        .row.align-items-center>.col-sm-4 {
            text-align: right;
        }

        @media (max-width: 576px) {
            .row.align-items-center>.col-sm-4 {
                text-align: left;
                margin-bottom: 4px;
            }
        }

        .form-check-inline {
            margin-right: 12px;
        }

        @media (max-width: 576px) {
            .row .col-sm-4 {
                text-align: left;
            }

            .form-control.w-25 {
                width: 100% !important;
            }
        }

        .transition-slider {
            transition: transform 0.4s ease-in-out;
        }
    </style>

</head>

<body>

    <?php require_once "./include/header.php" ?>
    <div class="container mt-4">
        <!-- Thông báo -->
        <?php if (!empty($_SESSION["thongBao"])): ?>
            <div id="order-success" class="alert alert-success close">
                🎉 <?= $_SESSION["thongBao"]; ?>
            </div>
            <?php unset($_SESSION["thongBao"]); ?>
        <?php endif; ?>

        <!-- Chi tiết sản phẩm -->
        <section class="product-detail-card row g-5 mb-5">
            <?php if ($sp["trangThai"] == 0): ?>
                <p>Sản phẩm hiện đang ẩn</p>
            <?php else: ?>
                <!-- Hình ảnh sản phẩm -->
                <div class="col-lg-5 text-center">
                    <div class="product-img-sticky">
                        <!-- Ảnh lớn -->
                        <img id="mainProductImg" src="<?= $dir . ($db->getOne("SELECT hinh FROM mau WHERE id = ?", [$selectedMau])["hinh"] ?? $sp["hinh"]) ?>"
                            alt="" class="product-img mb-3"
                            style="width: 100%; max-height: 400px; object-fit: cover;" />


                        <!-- Ảnh nhỏ tương ứng với màu -->
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($mauList as $index => $m): ?>
                                <img src="<?= $dir . $m["hinh"]; ?>"
                                    class="img-thumbnail-color"
                                    data-mau-id="<?= $m["id"] ?>"
                                    data-img="<?= $dir . $m["hinh"] ?>"
                                    style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; cursor: pointer;" />
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>

                <!-- Thông tin -->
                <div class="col-lg-7">
                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-muted"></i> Tên sản phẩm:
                        </label>
                        <div class="col-sm-8 pt-2"><?= $sp["ten"] ?></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label fw-semibold">
                            <i class="bi bi-grid me-1 text-muted"></i> Danh mục:
                        </label>
                        <div class="col-sm-8 pt-2"><?= $sp["tenDM"] ?></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label fw-semibold">
                            <i class="bi bi-cash-coin me-1 text-muted"></i> Giá:
                        </label>
                        <div class="col-sm-8 pt-2">
                            <span class="text-danger fs-5 fw-bold">
                                <?= number_format($sp["giaKhuyenMai"], 0, ",", ".") ?>đ
                            </span>
                            <del class="text-muted ms-2">
                                <?= number_format($sp["giaGoc"], 0, ",", ".") ?>đ
                            </del>
                            <?php
                            $phanTram = 100 - round($sp["giaKhuyenMai"] / $sp["giaGoc"] * 100);
                            if ($phanTram > 0): ?>
                                <span class="badge bg-danger ms-2">-<?= $phanTram ?>%</span>
                            <?php endif; ?>
                        </div>

                    </div>


                    <form action="" method="post" class="mb-3">
                        <!-- Chọn màu -->
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label fw-semibold">
                                <i class="bi bi-palette2 me-1"></i>Chọn màu:
                            </label>
                            <div class="col-sm-8 pt-2">
                                <?php foreach ($mauList as $index => $m): ?>
                                    <div class="form-check form-check-inline">
                                        <input onchange="this.form.submit()" class="form-check-input" type="radio" name="mau"
                                            id="mau<?= $m['id'] ?>" value="<?= $m["id"] ?>"
                                            <?= $selectedMau == $m["id"] ? "checked" : "" ?>>
                                        <label class="form-check-label" for="mau<?= $m['id'] ?>"><?= $m["mau"] ?></label>
                                    </div>
                                <?php endforeach; ?>
                                <a href="#huongDanSize"></a>
                            </div>
                        </div>

                        <!-- Chọn size -->
                        <?php if (!empty($sizeList)): ?>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label fw-semibold">
                                    <i class="bi bi-aspect-ratio me-1"></i>Chọn size:
                                </label>
                                <div class="col-sm-8 pt-2">
                                    <?php foreach ($sizeList as $index => $s): ?>
                                        <div class="form-check form-check-inline">
                                            <input onchange="this.form.submit()" class="form-check-input" type="radio" name="size"
                                                id="size<?= $index ?>" value="<?= $s["id"] ?>"
                                                <?= $selectedSize == $s["id"] ? "checked" : "" ?>>
                                            <label class="form-check-label" for="size<?= $index ?>"><?= $s["size"] ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                    <a href="#huongDanSize">(Hướng dẫn chọn size)</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Số lượng còn -->
                        <?php if ($selectedSizeInfo): ?>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label fw-semibold">
                                    <i class="bi bi-box-seam me-1"></i>Số lượng còn:
                                </label>
                                <div class="col-sm-8 pt-2">
                                    <span class="badge bg-success"><?= $selectedSizeInfo["soLuong"] ?></span>
                                    <?php if (intval($selectedSizeInfo["soLuong"]) <= 0): ?>
                                        <div class="text-danger mt-2">Sản phẩm đang hết hàng</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Số lượng mua -->
                        <div class="row mb-2">
                            <label for="qty" class="col-sm-4 col-form-label fw-semibold">
                                <i class="bi bi-plus-slash-minus me-1 text-muted"></i> Số lượng mua:
                            </label>

                            <div class="col-sm-8">
                                <input type="number" value="1" min="1" max="<?= $selectedSizeInfo["soLuong"] ?? 1 ?>"
                                    name="soLuong" id="qty" class="form-control w-25" required>
                            </div>
                        </div>

                        <!-- Nút thêm vào giỏ hàng -->
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label fw-semibold pt-2">
                                <i class="bi bi-lightning-fill me-1 text-muted"></i> Hành động:
                            </label>
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center gap-4">
                                    <button type="submit" name="yeuthich" value="1" class="btn" title="Yêu thích">
                                        <i class="fa-regular fa-heart fs-5"></i>
                                    </button>
                                    <button type="submit" name="addToCart" value="1" class="btn" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-cart-shopping fs-5"></i>
                                    </button>
                                    <a href="#" id="btnBuyNow" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-bag-check me-1"></i> Mua ngay
                                    </a>

                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="sanpham.php" class="btn btn-outline-secondary btn-sm mt-2">Quay lại danh sách</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Mô tả chi tiết -->
        <section id="huongDanSize" class="review-list mb-5">
            <h4 class="mb-3">
                <i class="bi bi-file-text me-1"></i> Mô tả chi tiết
                <span id="review-summary" class="fs-6 fw-normal ms-2 text-muted"></span>
            </h4>

            <!-- Nội dung mô tả -->
            <div class="review-item mb-4">
                <p><?= nl2br(htmlspecialchars($sp["moTa"])) ?></p>
            </div>

            <!-- Bảng mô tả size nếu có -->
            <?php if (!empty($moTaSize)) { ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th><i class="bi bi-aspect-ratio me-1"></i>Size</th>
                                <th><i class="bi bi-rulers me-1"></i>Chi tiết kích thước</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moTaSize as $row) { ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($row["size"]) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row["moTaSize"])) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>


        <!-- Đánh giá -->
        <section class="review-list mb-5">
            <h4 class="mb-3">
                Đánh giá của khách hàng
                <span id="review-summary" class="fs-6 fw-normal ms-2 text-muted"></span>
            </h4>
            <!-- Mỗi đánh giá gồm: avatar, tên, sao, ngày, bình luận -->
            <div class="review-item d-flex align-items-start" data-rating="5">
                <img src="https://randomuser.me/api/portraits/women/65.jpg" class="review-avatar" alt="Alice Nguyen" />
                <div>
                    <div>
                        <span class="review-user">Alice Nguyen</span>
                        <span class="review-rating" data-stars="5"></span>
                        <span class="review-date">· 2 ngày trước</span>
                    </div>
                    <div class="review-comment">iPhone 15 Pro thật tuyệt vời. Chất lượng camera đỉnh cao, pin dùng cả ngày!</div>
                </div>
            </div>
            <div class="review-item d-flex align-items-start" data-rating="4">
                <img src="https://randomuser.me/api/portraits/men/36.jpg" class="review-avatar" alt="Trung Pham" />
                <div>
                    <div>
                        <span class="review-user">Trung Pham</span>
                        <span class="review-rating" data-stars="4"></span>
                        <span class="review-date">· 1 tuần trước</span>
                    </div>
                    <div class="review-comment">Nhanh, đẹp, nhưng giá hơi cao. Dù sao vẫn rất thích!</div>
                </div>
            </div>
            <div class="review-item d-flex align-items-start" data-rating="5">
                <img src="https://randomuser.me/api/portraits/women/8.jpg" class="review-avatar" alt="Jane Doe" />
                <div>
                    <div>
                        <span class="review-user">Jane Doe</span>
                        <span class="review-rating" data-stars="5"></span>
                        <span class="review-date">· 3 tuần trước</span>
                    </div>
                    <div class="review-comment">Chiếc iPhone tốt nhất tôi từng dùng. Rất đáng mua!</div>
                </div>
            </div>
        </section>

        <!-- Sản phẩm liên quan -->
        <section class="product-relatives mb-4">
            <h4 class="mb-3">Sản phẩm liên quan</h4>
            <div class="position-relative">
                <!-- Nút trái -->
                <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y z-1" onclick="slideLeft()">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <!-- Slider chứa sản phẩm -->
                <div class="overflow-hidden">
                    <div id="relatedSlider" class="d-flex transition-slider" style="gap: 16px;">
                        <?php foreach ($spLQ as $item) { ?>
                            <div class="card related-card text-center" style="min-width: 25%;">
                                <img src="uploads/<?php echo $item["hinh"]; ?>" class="card-img-top" alt="" />
                                <div class="card-body">
                                    <div class="card-title fw-bold">Tên: <?php echo $item["ten"]; ?></div>
                                    <div class="text-muted mb-2">Danh mục: <?php echo $item["tenDM"]; ?></div>
                                    <a href="chitietsp.php?id=<?php echo $item["id"]; ?>" class="btn btn-outline-primary btn-sm">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Nút phải -->
                <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y z-1" onclick="slideRight()">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </section>

    </div>
    <?php require_once "./include/footer.php" ?>
    <script>
        document.querySelectorAll('.img-thumbnail-color').forEach(function(img) {
            img.addEventListener('click', function() {
                const newImgSrc = this.dataset.img;
                const mauID = this.dataset.mauId;

                // Đổi ảnh lớn
                const mainImg = document.getElementById('mainProductImg');
                if (mainImg) mainImg.src = newImgSrc;

                // Đổi radio
                const radio = document.getElementById('mau' + mauID);
                if (radio) {
                    radio.checked = true;
                    radio.form.submit();
                }

                // Xóa active cũ và thêm active mới
                document.querySelectorAll('.img-thumbnail-color').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
            });
        });
        let currentIndex = 0;

        function slideLeft() {
            const slider = document.getElementById('relatedSlider');
            const totalItems = slider.children.length;
            if (currentIndex > 0) {
                currentIndex--;
                updateSliderPosition();
            }
        }

        function slideRight() {
            const slider = document.getElementById('relatedSlider');
            const totalItems = slider.children.length;
            if (currentIndex < totalItems - 4) {
                currentIndex++;
                updateSliderPosition();
            }
        }

        function updateSliderPosition() {
            const slider = document.getElementById('relatedSlider');
            const cardWidth = slider.children[0].offsetWidth + 16; // 16 là khoảng cách gap
            slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btnBuyNow = document.getElementById('btnBuyNow');
            const form = btnBuyNow.closest('form');

            btnBuyNow.addEventListener('click', function(e) {
                e.preventDefault();

                const size = form.querySelector('input[name="size"]:checked');
                const qty = form.querySelector('input[name="soLuong"]');

                if (!size) {
                    alert("Vui lòng chọn size trước khi mua!");
                    return;
                }

                const idSize = size.value;
                const soLuong = qty.value;

                // Tạo link
                const link = `./user/dathang.php?idSize=${idSize}&soLuong=${soLuong}`;

                // Chuyển hướng
                window.location.href = link;
            });
        });
    </script>
</body>

</html>