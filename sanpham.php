<?php

require_once "./core/db_utils.php";

$dir = "./uploads/";
$dirMenu = "./";

$danhMuc = $db->getAll("SELECT * FROM danhmuc");

$limit = 8;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM sanpham";
$where = [];
$bind = [];
$orderby = "";
$whereClause = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["yeuthich"])) {
        if (!isset($_SESSION["nguoiDung"])) {
            header("location: dangnhap.php");
            exit();
        }
        $idNguoiDung = $_SESSION["nguoiDung"]["id"];
        $idSanPham = $_POST["idSanPham"];
        $check = $db->getOne("SELECT * FROM yeuthich WHERE idSanPham = ? AND idNguoiDung = ?", [$idSanPham, $idNguoiDung]);
        if ($check) {
            $_SESSION["thongBao"] = "Đã trong kho yêu thích";
            header("location: sanpham.php");
            exit();
        } else {
            $db->execute("INSERT INTO yeuthich(idSanPham, idNguoiDung) VALUES (?, ?)", [$idSanPham, $idNguoiDung]);
            $_SESSION["thongBao"] = "Yêu thích thành công";
            header("location: sanpham.php");
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
        $idSize    = $_POST["idSize"];
        $soLuong   = $_POST["soLuong"];
        $gia       = $_POST["gia"];

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
            header("location: sanpham.php");
            exit();
        } else {
            $_SESSION["thongBao"] = "Vui lòng chọn đủ thông tin sản phẩm!";
            header("location: sanpham.php");
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["id"])) {
        $where[] = "idDanhMuc = ?";
        $bind[] = $_GET["id"];
    }
    if (isset($_GET["loc"])) {
        $price = $_GET["price"];
        $giaHienThi = "IF(giaKhuyenmai = 0, giaGoc, giaKhuyenmai)";
        if ($price == "200000") {
            $where[] = "$giaHienThi < ?";
            $bind[] = $price;
        } else {
            $price = explode("-", $price);
            $min = min($price);
            $max = max($price);
            $where[] = "$giaHienThi >= ?";
            $bind[] = $min;
            $where[] = "$giaHienThi < ?";
            $bind[] = $max;
        }
    }

    if (isset($_GET["search"])) {
        $where[] = "ten like ?";
        $bind[] = "%{$_GET['keyword']}%";
    }

    if (isset($_GET["sort"])) {
        $sort = $_GET["sort"];
        $giaHienThi = "IF(giaKhuyenmai = 0, giaGoc, giaKhuyenmai)";
        if ($sort == "name_asc") {
            $orderby = "ORDER BY ten ASC";
        } elseif ($sort == "name_desc") {
            $orderby = "ORDER BY ten DESC";
        } elseif ($sort == "date_asc") {
            $orderby = "ORDER BY ngayTao ASC";
        } elseif ($sort == "date_desc") {
            $orderby = "ORDER BY ngayTao DESC";
        } elseif ($sort == "price_asc") {
            $orderby = "ORDER BY $giaHienThi ASC";
        } elseif ($sort == "price_desc") {
            $orderby = "ORDER BY $giaHienThi DESC";
        }
    }
}

if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
}

$countRows = $db->getValue("SELECT COUNT(*) FROM sanpham $whereClause", $bind);

$totalPages = ceil($countRows / $limit);

$sanPham = $db->getAll($sql . " " . $whereClause . " " . $orderby . " LIMIT $limit OFFSET $offset", $bind);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm</title>
    <style>
        .custom-price-radio {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            transition: all 0.2s ease-in-out;
        }

        .custom-price-radio:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }

        .custom-price-radio input[type="radio"]:checked+label {
            color: #dc3545;
        }

        .custom-price-radio label {
            margin-left: 8px;
        }

        .nav-title {
            font-size: 1.08rem;
            font-weight: 600;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.4em 0.6em;
            border-radius: 0.25rem;
        }

        /* Hover cho các nút outline-dark và light → chuyển màu đỏ khi hover */
        .btn-outline-dark:hover,
        .btn-light:hover {
            background-color: #ffffffff;
            color: #fff !important;
            border-color: #dc3545 !important;
        }

        /* Icon bên trong nút khi hover: trắng */
        .btn-outline-dark:hover i,
        .btn-light:hover i {
            color: #dc3545 !important;
        }

        /* Nếu có btn-dark (nút đen), đổi sang đỏ khi hover (nếu cần) */
        .btn-dark:hover {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        /* Badge giữ màu ổn định */
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }


        .btn-buy-now {
            border: 1px solid #000000ff;
            color: #dc3545;
            background-color: transparent;
            transition: 0.2s ease-in-out;
        }

        .btn-buy-now i {
            color: #dc3545;
        }

        /* Hover: nền đỏ nhạt, chữ và icon trắng */
        .btn-buy-now:hover {
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .btn-buy-now:hover i {
            color: #dc3545 !important;
        }

        .btn-check:checked+.btn-outline-secondary {
            background-color: #000;
            color: #fff;
            border-color: #000;
        }

        .btn-check:checked+.btn-outline-dark {
            background-color: #000;
            color: #fff;
            border-color: #000;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        /* Nút yêu thích */
        .btn-outline-dark:hover i,
        .btn-light:hover i {
            color: #dc3545 !important;
        }

        /* Nút Mua/Giỏ: màu đỏ đậm nổi bật */
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }

        /* Badge giảm giá */
        .badge.bg-danger {
            font-size: 0.8rem;
            padding: 0.4em 0.6em;
            background-color: #dc3545 !important;
        }

        /* Icon trái tim khi hover */
        .btn-outline-dark:hover {
            background-color: #fff;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .text-truncate-2 {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            /* số dòng */
            -webkit-box-orient: vertical;
            line-height: 1.4rem;
            max-height: 2.8rem;
            /* 2 dòng x 1.4rem */
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-dark:hover {
            background-color: #343a40 !important;
            /* Màu xám đậm hơn đen */
            border-color: #343a40 !important;
        }

        /* Đảm bảo màu chữ trắng với outline-danger khi có text-white */
        .btn-outline-danger.text-white {
            color: #fff;
            border-color: #dc3545;
            background-color: #dc3545;
        }

        .btn-outline-danger.text-white:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: #fff;
        }
    </style>
</head>

<body>
    <?php require_once "./include/header.php"; ?>
    <div class="container mb-5">
        <div class="row">
            <!-- Sidebar category -->
            <aside class="col-lg-3 mb-4">
                <div class="bg-white p-3 shadow-sm rounded">
                    <h6 class="nav-title border-bottom pb-2 mb-3">📂 Danh mục sản phẩm</h6>
                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <a href="sanpham.php" class="nav-link <?= !isset($_GET["id"]) ? "text-danger" : "" ?>">Tất cả</a>
                        </li>
                        <?php if (empty($danhMuc)) {
                            echo "<li class='list-group-item text-muted'>Chưa có danh mục</li>";
                        } else {
                            foreach ($danhMuc as $category) { ?>
                                <li class="list-group-item">
                                    <a href="sanpham.php?id=<?= $category["id"]; ?>" class="nav-link <?= isset($_GET["id"]) && $_GET["id"] == $category["id"] ? "text-danger" : "" ?>">
                                        <?= $category["ten"] ?>
                                    </a>
                                </li>
                        <?php }
                        } ?>
                    </ul>

                    <!-- Bộ lọc -->
                    <form action="" method="get">
                        <h6 class="nav-title border-bottom pb-2 mb-3">🔍 Lọc theo giá</h6>

                        <!-- Giá -->
                        <form action="" method="get" class="d-flex flex-column gap-2">
                            <?php if (isset($_GET["id"])) { ?>
                                <input type="hidden" name="id" value="<?= $_GET["id"] ?>">
                            <?php } ?>
                            <?php if (isset($_GET["keyword"])) { ?>
                                <input type="hidden" name="keyword" value="<?= $_GET["keyword"] ?>">
                            <?php } ?>
                            <?php if (isset($_GET["sort"])) { ?>
                                <input type="hidden" name="sort" value="<?= $_GET["sort"] ?>">
                            <?php } ?>
                            <?php
                            $prices = [
                                "200000" => "Dưới 200,000₫",
                                "200000-500000" => "200,000đ - 500,000đ",
                                "500000-1000000" => "500,000đ - 1.000,000đ",
                            ];
                            foreach ($prices as $value => $label) { ?>
                                <div class="form-check custom-price-radio p-2 rounded shadow-sm bg-light">
                                    <input onchange="this.form.submit()" class="form-check-input me-2" type="radio" name="price" id="price<?= $value ?>" value="<?= $value ?>" <?= (isset($_GET["price"]) && $_GET["price"] == $value) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="price<?= $value ?>">
                                        <?= $label ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <input type="hidden" name="loc" value="1">
                        </form>
                        <div class="d-flex justify-content-between mt-3">
                            <a href="sanpham.php?<?= isset($_GET["id"]) ? "id={$_GET['id']}&" : "" ?><?= isset($_GET["sort"]) ? "sort={$_GET['sort']}" : "" ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Xóa lọc
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Main content: danh sách sản phẩm -->
            <section class="col-lg-9">
                <div class="bg-white p-3 shadow-sm rounded">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <h6 class="nav-title mb-0 me-3">🛍️ Danh sách sản phẩm</h6>
                        <?php if (!empty($_SESSION["thongBao"])): ?>
                            <div class="position-relative" style="max-width: 350px;">
                                <div class="alert alert-success alert-dismissible fade show mb-0 py-1" role="alert" style="font-size: 0.95rem;">
                                    <?= $_SESSION["thongBao"] ?>
                                    <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="alert"
                                        aria-label="Close"
                                        style="position: absolute; top: 50%; right: 8px; transform: translateY(-50%);">
                                    </button>
                                </div>
                            </div>
                            <?php $_SESSION["thongBao"] = ""; ?>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- BỘ LỌC TÌM KIẾM VÀ SẮP XẾP -->
                <div class="d-flex justify-content-between mt-4 flex-wrap border-top pt-3">
                    <!-- Form sắp xếp -->
                    <form action="" method="get" class="d-flex align-items-center gap-2">
                        <?php if (isset($_GET["id"])) { ?>
                            <input type="hidden" name="id" value="<?= $_GET["id"] ?>">
                        <?php } ?>
                        <?php if (isset($_GET["keyword"])) { ?>
                            <input type="hidden" name="keyword" value="<?= $_GET["keyword"] ?>">
                        <?php } ?>
                        <?php if (isset($_GET["price"])) { ?>
                            <input class="form-check-input me-2" type="hidden" name="price" value="<?= $_GET["price"] ?>" <?= ($_GET["price"] == $_GET["price"]) ? 'checked' : '' ?>>
                        <?php } ?>
                        <i class="bi bi-sort-alpha-down me-2"></i>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Sắp xếp</option>
                            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : '' ?>>Tên A-Z</option>
                            <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : '' ?>>Tên Z-A</option>
                            <option value="date_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'date_asc') ? 'selected' : '' ?>>Cũ nhất</option>
                            <option value="date_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'date_desc') ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : '' ?>>Giá tăng dần</option>
                            <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : '' ?>>Giá giảm dần</option>
                        </select>
                    </form>
                    <!-- Form tìm kiếm -->
                    <form action="" method="get" class="d-flex align-items-center gap-2">
                        <?php if (isset($_GET["id"])) { ?>
                            <input type="hidden" name="id" value="<?= $_GET["id"] ?>">
                        <?php } ?>
                        <?php if (isset($_GET["sort"])) { ?>
                            <input type="hidden" name="sort" value="<?= $_GET["sort"] ?>">
                        <?php } ?>
                        <?php if (isset($_GET["price"])) { ?>
                            <input class="form-check-input me-2" type="hidden" name="price" value="<?= $_GET["price"] ?>" <?= ($_GET["price"] == $_GET["price"]) ? 'checked' : '' ?>>
                        <?php } ?>
                        <label for="keyword">🔍</label>
                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Tìm sản phẩm..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                        <button name="search" type="submit" class="btn btn-sm btn-outline-primary">Tìm</button>
                    </form>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 pt-3">
                    <?php if (!empty($sanPham)) {
                        foreach ($sanPham as $sp) {
                            $giaGoc = (float)$sp['giaGoc'];
                            $giaKM = (float)$sp['giaKhuyenMai'];
                            $giaHienThi = $giaKM == 0 ? $giaGoc : $giaKM;
                            $phanTramGiam = round(100 - ($giaKM / $giaGoc) * 100);
                            $idSP = $sp["id"];
                            $mauSac = $db->getAll("SELECT * FROM mau WHERE idSanPham = ?", [$idSP]);
                    ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0 position-relative card-hover overflow-hidden">
                                    <div class="position-relative">
                                        <a href="chitietsp.php?id=<?= $sp['id'] ?>" class="text-decoration-none text-dark">
                                            <img src="<?= $dir . $sp['hinh'] ?>" class="card-img-top" alt="<?= $sp['ten'] ?>">
                                        </a>

                                        <?php if ($phanTramGiam > 0) : ?>
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">-<?= $phanTramGiam ?>%</span>
                                        <?php endif; ?>

                                        <!-- Yêu thích -->
                                        <form action="" method="post">
                                            <input type="hidden" name="idSanPham" value="<?= $sp["id"] ?>">
                                            <button type="submit" name="yeuthich" class="btn btn-outline-dark btn-sm border border-dark bg-white position-absolute top-0 end-0 m-2 shadow" title="Yêu thích">
                                                <i class="fa-regular fa-heart fs-6 text-dark"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="card-body d-flex flex-column position-relative">
                                        <!-- Mô tả sản phẩm (giới hạn 2 dòng) -->
                                        <h6 class="card-title mb-2 text-truncate-2" title="<?= $sp['ten'] ?>">
                                            <?= $sp['moTa'] ?>
                                        </h6>

                                        <!-- Giá sản phẩm -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <?php if ($phanTramGiam > 0) : ?>
                                                <span class="text-danger fw-bold mb-0">
                                                    <i class="fa-solid fa-bolt me-1"></i><?= number_format($giaKM) ?>₫
                                                </span>
                                                <small class="text-muted text-decoration-line-through mb-0"><?= number_format($sp['giaGoc']) ?>₫</small>
                                            <?php else: ?>
                                                <span class="text-dark fw-bold"><?= number_format($sp['giaKhuyenMai']) ?>₫</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="action-buttons row gx-2">
                                            <div class="col-6">
                                                <button class="btn btn-dark w-100 btn-sm text-white"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cartOptionModal<?= $sp['id'] ?>"
                                                    title="Thêm vào giỏ hàng">
                                                    <i class="fa-solid fa-cart-plus"></i>
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-outline-danger w-100 btn-sm text-white"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cartOptionModal<?= $sp['id'] ?>"
                                                    title="Mua ngay">
                                                     <i class="bi bi-bag-check"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <!-- Modal chọn màu & size -->
                                <div class="modal fade" id="cartOptionModal<?= $idSP ?>" tabindex="-1" aria-labelledby="cartOptionModalLabel<?= $idSP ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <form method="post" action="sanpham.php">
                                                <!-- Dữ liệu gửi về server -->
                                                <input type="hidden" name="idSanPham" value="<?= $idSP ?>">
                                                <input type="hidden" name="idMau" id="hiddenMau<?= $idSP ?>">
                                                <input type="hidden" name="idSize" id="selectedSize<?= $idSP ?>">
                                                <input type="hidden" name="gia" id="gia<?= $idSP ?>" value="<?= $giaHienThi ?>">
                                                <input type="hidden" name="soLuong" id="selectedQuantity<?= $idSP ?>">

                                                <!-- Modal Header -->
                                                <div class="modal-header border-bottom-0">
                                                    <h5 class="modal-title"><i class="fa-solid fa-shirt"></i> Chọn màu và size</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                                </div>

                                                <!-- Modal Body -->
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <!-- Hình ảnh -->
                                                        <div class="col-md-4 text-center">
                                                            <img id="productImage<?= $idSP ?>" src="uploads/<?= $sp['hinh'] ?>" alt="<?= $sp['ten'] ?>" class="img-thumbnail" style="max-width: 100%;">
                                                        </div>
                                                        <!-- Thông tin sản phẩm -->
                                                        <div class="col-md-8">
                                                            <h5 class="fw-bold"><?= $sp['ten'] ?></h5>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-danger fw-bold fs-5"><?= number_format($giaKM) ?> ₫</span>
                                                                <span class="text-decoration-line-through text-muted"><?= number_format($giaGoc) ?> ₫</span>
                                                            </div>

                                                            <!-- Màu sắc -->
                                                            <div class="mb-2">
                                                                <label class="form-label">Màu sắc</label>
                                                                <div class="d-flex flex-wrap gap-2 color-radio-group" data-id="<?= $idSP ?>">
                                                                    <?php foreach ($mauSac as $mau): ?>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input color-radio" type="radio"
                                                                                name="chonMau<?= $idSP ?>"
                                                                                id="mau<?= $idSP ?>_<?= $mau['id'] ?>"
                                                                                value="<?= $mau['id'] ?>"
                                                                                data-id="<?= $idSP ?>"
                                                                                data-img="uploads/<?= $mau['hinh'] ?>"> <!-- Dòng thêm -->
                                                                            <label class="form-check-label badge rounded-pill bg-light text-dark border"
                                                                                for="mau<?= $idSP ?>_<?= $mau['id'] ?>">
                                                                                <?= $mau['mau'] ?>
                                                                            </label>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>

                                                            <!-- Size -->
                                                            <div class="mb-2" id="sizeGroup<?= $idSP ?>">
                                                                <label class="form-label">Size</label>
                                                                <div class="d-flex flex-wrap gap-2" id="sizeRadioGroup<?= $idSP ?>">
                                                                    <span class="text-muted">Chọn màu trước...</span>
                                                                </div>
                                                                <small id="tonKhoText<?= $idSP ?>" class="text-muted text-danger d-none fw-semibold"></small>
                                                            </div>

                                                            <!-- Số lượng -->
                                                            <div class="mb-2 d-none" id="quantityGroup<?= $idSP ?>">
                                                                <label class="form-label">Số lượng</label>
                                                                <input type="number" class="form-control form-control-sm w-50"
                                                                    name="quantity"
                                                                    id="quantityInput<?= $idSP ?>"
                                                                    min="1" placeholder="Nhập số lượng">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Modal Footer -->
                                                <div class="modal-footer border-top-0 p-2">
                                                    <div class="d-flex w-100 gap-2">
                                                        <button type="submit" name="addToCart" class="btn btn-dark w-50">
                                                            <i class="fa-solid fa-cart-plus me-1"></i> Thêm vào giỏ
                                                        </button>
                                                        <a href="#" class="btn btn-danger w-50" id="muaNgayBtn<?= $idSP ?>">
                                                              <i class="bi bi-bag-check me-1"></i> Mua ngay
                                                        </a>
                                                    </div>
                                                </div>


                                            </form>

                                        </div>
                                    </div>
                                </div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        document.querySelectorAll(".color-radio").forEach(function(radioEl) {
                                            radioEl.addEventListener("change", function() {
                                                const idMau = this.value;
                                                const idSP = this.getAttribute("data-id");
                                                const newImage = this.getAttribute("data-img");
                                                const imgEl = document.getElementById("productImage" + idSP);
                                                if (newImage && imgEl) {
                                                    imgEl.src = newImage;
                                                }
                                                const hiddenMau = document.getElementById("hiddenMau" + idSP);
                                                const hiddenSize = document.getElementById("selectedSize" + idSP);
                                                const selectedQuantity = document.getElementById("selectedQuantity" + idSP);

                                                const sizeGroup = document.getElementById("sizeRadioGroup" + idSP);
                                                const quantityGroup = document.getElementById("quantityGroup" + idSP);
                                                const quantityInput = document.getElementById("quantityInput" + idSP);
                                                const tonKhoText = document.getElementById("tonKhoText" + idSP);

                                                // Gán ID màu vào input hidden
                                                hiddenMau.value = idMau;

                                                // Reset size và quantity
                                                sizeGroup.innerHTML = '<span class="text-muted">Đang tải...</span>';
                                                quantityGroup.classList.add("d-none");
                                                quantityInput.value = "";
                                                tonKhoText.classList.add("d-none");
                                                tonKhoText.textContent = "";
                                                hiddenSize.value = "";
                                                selectedQuantity.value = "";

                                                // Gọi ajax lấy size theo màu
                                                fetch("ajax_get_sizes.php", {
                                                        method: "POST",
                                                        headers: {
                                                            "Content-Type": "application/x-www-form-urlencoded",
                                                        },
                                                        body: "idMau=" + encodeURIComponent(idMau),
                                                    })
                                                    .then(res => res.ok ? res.json() : Promise.reject("Lỗi kết nối"))
                                                    .then(data => {
                                                        sizeGroup.innerHTML = "";

                                                        if (Array.isArray(data) && data.length > 0) {
                                                            data.forEach(size => {
                                                                const idSize = size.id;
                                                                const tonKho = size.soLuong;
                                                                const radioId = `size${idSP}_${idSize}`;

                                                                // Tạo radio
                                                                const radio = document.createElement("input");
                                                                radio.type = "radio";
                                                                radio.name = "size" + idSP;
                                                                radio.value = idSize;
                                                                radio.id = radioId;
                                                                radio.className = "form-check-input size-radio";
                                                                radio.setAttribute("data-tonkho", tonKho);
                                                                radio.setAttribute("data-id", idSP);

                                                                // Tạo label
                                                                const label = document.createElement("label");
                                                                label.className = "form-check-label badge rounded-pill bg-light text-dark border";
                                                                label.setAttribute("for", radioId);
                                                                label.textContent = size.size;

                                                                // Gói vào wrapper
                                                                const wrapper = document.createElement("div");
                                                                wrapper.className = "form-check form-check-inline";
                                                                wrapper.appendChild(radio);
                                                                wrapper.appendChild(label);
                                                                sizeGroup.appendChild(wrapper);

                                                                // Bắt sự kiện chọn size
                                                                radio.addEventListener("change", function() {
                                                                    const selectedTonKho = parseInt(this.getAttribute("data-tonkho")) || 0;

                                                                    if (selectedTonKho > 0) {
                                                                        quantityGroup.classList.remove("d-none");
                                                                        quantityInput.max = selectedTonKho;
                                                                        quantityInput.min = 1;
                                                                        quantityInput.value = 1;
                                                                        quantityInput.placeholder = `Tối đa ${selectedTonKho} sản phẩm`;

                                                                        tonKhoText.classList.remove("d-none");
                                                                        tonKhoText.textContent = `Số lượng tồn kho ${selectedTonKho}`;

                                                                        hiddenSize.value = idSize;
                                                                        selectedQuantity.value = 1;
                                                                    } else {
                                                                        quantityGroup.classList.add("d-none");
                                                                        quantityInput.value = "";
                                                                        tonKhoText.classList.add("d-none");
                                                                        tonKhoText.textContent = "";

                                                                        hiddenSize.value = "";
                                                                        selectedQuantity.value = "";
                                                                    }
                                                                });
                                                            });

                                                            // Khi người dùng nhập số lượng thủ công
                                                            quantityInput.addEventListener("input", function() {
                                                                selectedQuantity.value = this.value;
                                                            });
                                                        } else {
                                                            sizeGroup.innerHTML = '<span class="text-danger">Không có size</span>';
                                                        }
                                                    })
                                                    .catch(() => {
                                                        sizeGroup.innerHTML = '<span class="text-danger">Lỗi tải size</span>';
                                                    });
                                            });
                                        });
                                    });
                                    document.addEventListener("DOMContentLoaded", function() {
                                        // Thêm sự kiện cho nút Mua ngay
                                        const btnMuaNgay = document.getElementById("muaNgayBtn<?= $idSP ?>");

                                        if (btnMuaNgay) {
                                            btnMuaNgay.addEventListener("click", function(e) {
                                                e.preventDefault();

                                                const idSanPham = <?= $idSP ?>;
                                                const idMau = document.getElementById("hiddenMau<?= $idSP ?>").value;
                                                const idSize = document.getElementById("selectedSize<?= $idSP ?>").value;
                                                const soLuong = document.getElementById("selectedQuantity<?= $idSP ?>").value;

                                                if (!idMau || !idSize || !soLuong) {
                                                    alert("Vui lòng chọn màu, size và số lượng trước khi mua!");
                                                    return;
                                                }

                                                // Redirect sang trang đặt hàng
                                                const url = `./user/dathang.php?idSize=${idSize}&soLuong=${soLuong}`;
                                                window.location.href = url;
                                            });
                                        }
                                    });
                                </script>

                            </div>

                    <?php }
                    } else {
                        echo "<div class='col-12 text-muted'>Không tìm thấy sản phẩm...</div>";
                    } ?>
                </div>

        </div>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ["page" => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        </section>
    </div>
    <?php require_once "./include/footer.php"; ?>
</body>

</html>