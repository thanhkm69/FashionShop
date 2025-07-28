<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";

$limit = 8;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;

$offset = ($page - 1) * $limit;

$where = [];
$bind = [];
$orderBy = "";
$whereClause = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["saveVoucher"])) {
        $id = $_POST["id"];
        $idNguoiDung = $_SESSION["nguoiDung"]["id"];
        $checkVoucher = $db->getOne("SELECT * FROM voucher WHERE id = ?", [$id]);
        $ngayBatDau   = date('Y-m-d', strtotime($checkVoucher["ngayBatDau"]));
        $ngayKetThuc  = date('Y-m-d', strtotime($checkVoucher["ngayKetThuc"]));
        $ngayHienTai  = date('Y-m-d'); // Ngày hiện tại theo định dạng Y-m-d
        $soLuong = $checkVoucher["soLuong"];
        if ($ngayHienTai < $ngayBatDau) {
            $_SESSION["thongBao"] = "Voucher chưa được áp dụng.";
        } elseif ($ngayHienTai > $ngayKetThuc) {
            $_SESSION["thongBao"] = "Voucher đã hết hạn.";
        } elseif ($soLuong == 0) {
            $_SESSION["thongBao"] = "Voucher đã hết.";
        } else {
            $check = $db->getOne("SELECT * FROM voucherNguoiDung WHERE idVoucher = ? AND idNguoiDung = ?", [$id, $idNguoiDung]);
            if ($check) {
                $_SESSION["thongBao"] = "Voucher đã có trong kho của bạn.";
            } else {
                $db->execute("INSERT INTO vouchernguoidung (idNguoiDung,idVoucher) VALUES (?,?)", [$idNguoiDung, $id]);
                $db->execute("UPDATE voucher SET soLuong = ? WHERE id = ?", [--$checkVoucher["soLuong"], $id]);
                $_SESSION["thongBao"] = "Thêm voucher thành công";
                header("location: voucher.php");
                exit;
            }
        }
    }
}

// 1. Lọc theo kiểu giảm giá
$kieu = $_GET['kieu'] ?? '';
if ($kieu !== '') {
    $where[] = "kieu = ?";
    $bind[] = $kieu;
}

// 2. Lọc theo từ khóa (mã voucher )
$keyword = $_GET['keyword'] ?? '';
if ($keyword !== '') {
    $where[] = "ma LIKE ?";
    $bind[] = "%$keyword%";
}

// 3. Sắp xếp theo giá
$sort = $_GET['sort'] ?? '';
if ($sort === 'price_asc') {
    $orderBy = " ORDER BY giam ASC";  // Hoặc 'gia' tùy tên cột bạn đặt
} elseif ($sort === 'price_desc') {
    $orderBy = " ORDER BY giam DESC";
}

// 4. Kết hợp WHERE
if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
}

$countRows = $db->getValue("SELECT COUNT(*) FROM voucher $whereClause", $bind);

$totalPages = ceil($countRows / $limit);

// 5. Truy vấn DB
$sql = "SELECT * FROM voucher $whereClause $orderBy LIMIT $limit OFFSET $offset";
$voucher = $db->getAll($sql, $bind);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher</title>
    <style>
        .description-clamp {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            /* giống text-muted */
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25rem;
            max-height: calc(1.25rem * 2);
            /* Đảm bảo đúng 2 dòng */
            margin-bottom: 0.25rem;
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
                    <h6 class="nav-title border-bottom pb-2 mb-3"> <i class="bi bi-ticket-perforated-fill me-2"></i> Danh sách Voucher</h6>
                    <h6 class="nav-title border-bottom pb-2 mb-3">🔍 Lọc</h6>
                    <!-- Giá -->
                    <form action="" method="get" class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kieu" id="kieu_all" value="" onchange="this.form.submit()" <?= $kieu === '' ? 'checked' : '' ?>>
                            <label class="form-check-label <?= $kieu === '' ? 'text-danger fw-semibold' : '' ?>" for="kieu_all">
                                Tất cả
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kieu" id="kieu_tien" value="0" onchange="this.form.submit()" <?= $kieu === '0' ? 'checked' : '' ?>>
                            <label class="form-check-label <?= $kieu === '0' ? 'text-danger fw-semibold' : '' ?>" for="kieu_tien">
                                Giảm theo tiền
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kieu" id="kieu_phantram" value="1" onchange="this.form.submit()" <?= $kieu === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label <?= $kieu === '1' ? 'text-danger fw-semibold' : '' ?>" for="kieu_phantram">
                                Giảm theo phần trăm
                            </label>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <a href="voucher.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Xóa lọc
                            </a>
                        </div>
                    </form>

                </div>
            </aside>

            <!-- Main content: danh sách sản phẩm -->
            <section class="col-lg-9">
                <div class="bg-white p-3 shadow-sm rounded">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <h6 class="nav-title mb-0 me-3">🛍️ Danh sách voucher</h6>
                        <?php if (!empty($_SESSION["thongBao"])): ?>
                            <div class="position-relative" style="max-width: 350px;">
                                <div class="alert alert-danger alert-dismissible fade show mb-0 py-1" role="alert" style="font-size: 0.95rem;">
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
                        <i class="bi bi-sort-alpha-down me-2"></i>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Sắp xếp</option>
                            <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                            <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                        </select>
                    </form>

                    <!-- Form tìm kiếm theo mã/tên -->
                    <form action="" method="get" class="d-flex align-items-center gap-2">
                        <label for="keyword">🔍</label>
                        <input type="text" name="keyword" class="form-control form-control-sm"
                            placeholder="Nhập mã voucher..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                        <button name="search" type="submit" class="btn btn-sm btn-outline-primary">Tìm</button>
                    </form>

                </div>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 pt-3">
                    <?php if (!empty($voucher)) {
                        foreach ($voucher as $item): ?>
                            <div class="col">
                                <div class="bg-white border rounded shadow-sm h-100 p-2 clearfix">
                                    <!-- Hình ảnh bên trái -->
                                    <img src="<?= $dir . $item['hinh'] ?>" alt="<?= $item['ma'] ?>"
                                        class="rounded float-start me-3 mb-2"
                                        style="width: 50px; height: 50px; object-fit: cover;">

                                    <!-- Nội dung bên phải, bao quanh hình -->
                                    <span class="badge bg-danger mb-1"><?= htmlspecialchars($item["ma"]) ?></span><br>

                                    <!-- Mô tả: giới hạn 2 dòng, nhỏ + đậm + hiển thị "..." khi dài -->
                                    <p class="description-clamp">
                                        <?= htmlspecialchars($item["moTa"]) ?>
                                    </p>

                                    <!-- Bắt đầu + Kết thúc: trên cùng 1 dòng, nhỏ + đậm -->
                                    <div class="d-flex justify-content-between mt-1" style="font-size: 0.75rem;">
                                        <span class="fw-semibold text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= date('d/m/Y', strtotime($item["ngayBatDau"])) ?>
                                        </span>
                                        <span class="fw-semibold text-muted text-end">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= date('d/m/Y', strtotime($item["ngayKetThuc"])) ?>
                                        </span>
                                    </div>


                                    <!-- Số lượng -->
                                    <small class="d-block mt-1 text-muted">Số lượng: <?= $item["soLuong"] ?></small>

                                    <!-- Nút lưu -->
                                    <form method="post" action="" class="mt-3">
                                        <input type="hidden" name="id" value="<?= $item["id"] ?>">
                                        <button type="submit" name="saveVoucher" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="bi bi-bookmark-plus"></i> Lưu mã
                                        </button>
                                    </form>
                                </div>

                            </div>
                    <?php endforeach;
                    } else {
                        echo "<div class='col-12 text-muted'>Không tìm thấy voucher...</div>";
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