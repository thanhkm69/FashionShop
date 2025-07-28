<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}

$dir = "../../uploads/";
$where = [];
$bind = [];
$whereClase = "";


$limit = isset($_GET["limit"]) ? intval($_GET["limit"]) : 5;

$page = isset($_GET["page"]) ?  intval($_GET["page"]) : 1;

$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["trangThaiDH"])) {
        $id = $_POST["id"];
        $trangThaiDH = $_POST["trangThaiDH"];
        $db->execute("UPDATE hoadon SET trangThaiDH = ? WHERE id = ?", [$trangThaiDH, $id]);
        $_SESSION["thongBao"] = "Cập nhập trang thái đơn hàng thành công";
        header("location: hoadon.php");
        exit;
    }
}

// Xử lý tìm kiếm
if (isset($_GET["search"])) {
    // Lọc theo mã hóa đơn
    if (!empty($_GET["ma"])) {
        $where[] = "ma LIKE ?";
        $bind[] = "%" . test_input($_GET["ma"]) . "%";
    }

    // Lọc theo trạng thái đơn hàng
    if (isset($_GET["trangThaiDH"]) && $_GET["trangThaiDH"] !== "") {
        $where[] = "trangThaiDH = ?";
        $bind[] = test_input($_GET["trangThaiDH"]);
    }

    // Lọc theo phương thức thanh toán
    if (isset($_GET["phuongThucTT"]) && $_GET["phuongThucTT"] !== "") {
        $where[] = "phuongThucTT = ?";
        $bind[] = test_input($_GET["phuongThucTT"]);
    }

    // Lọc theo trạng thái thanh toán
    if (isset($_GET["trangThaiTT"]) && $_GET["trangThaiTT"] !== "") {
        $where[] = "trangThaiTT = ?";
        $bind[] = intval($_GET["trangThaiTT"]);
    }
}


if (!empty($where)) {
    $whereClase = " WHERE " . implode(" AND ", $where);
}


$totalRows = $db->getValue("SELECT COUNT(*) FROM hoadon $whereClase", $bind);

$totalPages = ceil($totalRows / $limit);

$hoadon = $db->getAll("SELECT * FROM hoadon $whereClase LIMIT $limit OFFSET $offset", $bind);


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý voucher</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="action-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Hóa đơn</span>
                    </div>
                </div>
                <?php if (!empty($_SESSION["thongBao"])): ?>
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <?= $_SESSION["thongBao"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php $_SESSION["thongBao"] = ""; ?>
                <?php endif; ?>

                <!-- Search Form -->
                <form method="get" class="row g-3 mb-4 align-items-end">

                    <div class="col-md-2">
                        <input type="text" name="ma" class="form-control" placeholder="Nhập mã hóa đơn" value="<?= $_GET['ma'] ?? '' ?>">
                    </div>


                    <div class="col-md-2">
                        <select name="trangThaiDH" class="form-select">
                            <option value="">Trạng thái đơn</option>
                            <option value="Đang xác nhận" <?= ($_GET['trangThaiDH'] ?? '') == "Đang xác nhận" ? 'selected' : '' ?>>Đang xác nhận</option>
                            <option value="Đã xác nhận" <?= ($_GET['trangThaiDH'] ?? '') == "Đã xác nhận" ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="Đang giao hàng" <?= ($_GET['trangThaiDH'] ?? '') == "Đang giao hàng" ? 'selected' : '' ?>>Đang giao hàng</option>
                            <option value="Giao hàng thành công" <?= ($_GET['trangThaiDH'] ?? '') == "Giao hàng thành công" ? 'selected' : '' ?>>Giao hàng thành công</option>
                            <option value="Hoàn thành" <?= ($_GET['trangThaiDH'] ?? '') == "Hoàn thành" ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="Trả hàng" <?= ($_GET['trangThaiDH'] ?? '') == "Trả hàng" ? 'selected' : '' ?>>Trả hàng</option>
                            <option value="Đã hủy" <?= ($_GET['trangThaiDH'] ?? '') == "Đã hủy" ? 'selected' : '' ?>>Đã hủy</option>
                        </select>

                    </div>

                    <div class="col-md-2">
                        <select name="phuongThucTT" class="form-select">
                            <option value="">Phương thức</option>
                            <option value="cod" <?= ($_GET['phuongThucTT'] ?? '') == "cod" ? 'selected' : '' ?>>COD</option>
                            <option value="vnpay" <?= ($_GET['phuongThucTT'] ?? '') == "vnpay" ? 'selected' : '' ?>>VN PAY</option>
                        </select>

                    </div>

                    <div class="col-md-2">
                        <select name="trangThaiTT" class="form-select">
                            <option value="">Thanh toán</option>
                            <option value="1" <?= ($_GET['trangThaiTT'] ?? '') == "1" ? 'selected' : '' ?>>✅ Đã thanh toán</option>
                            <option value="0" <?= ($_GET['trangThaiTT'] ?? '') === "0" ? 'selected' : '' ?>>❌ Chưa thanh toán</option>
                        </select>

                    </div>

                    <!-- Search button -->
                    <div class="col-md-2">
                        <button type="submit" name="search" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </div>

                    <!-- Reset button -->
                    <div class="col-md-2">
                        <a href="hoadon.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-repeat"></i> Xóa
                        </a>
                    </div>
                </form>


                <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <!-- Bên trái: Hiển thị số bản ghi -->
                    <div class="d-flex align-items-center">
                        <label for="limit" class="form-label mb-0 me-2">
                            <i class="bi bi-list-ol me-1"></i> Hiển thị:
                        </label>
                        <select name="limit" id="limit" onchange="this.form.submit()" class="form-select w-auto">
                            <option value="5" <?= (isset($_GET["limit"]) && $_GET["limit"] == 5) ? "selected" : "" ?>>5</option>
                            <option value="10" <?= (isset($_GET["limit"]) && $_GET["limit"] == 10) ? "selected" : "" ?>>10</option>
                            <option value="20" <?= (isset($_GET["limit"]) && $_GET["limit"] == 20) ? "selected" : "" ?>>20</option>
                            <option value="50" <?= (isset($_GET["limit"]) && $_GET["limit"] == 50) ? "selected" : "" ?>>50</option>
                        </select>
                        <span class="ms-2">bản ghi/trang</span>
                    </div>

                    <!-- Bên phải: Sắp xếp -->
                    <div class="d-flex align-items-center">
                        <label for="orderby" class="form-label mb-0 me-2">
                            <i class="bi bi-sort-alpha-down me-1"></i> Sắp xếp:
                        </label>
                        <select name="orderbyGia" id="orderbyGia" onchange="this.form.submit()" class="form-select w-auto">
                            <option value="">Theo giá</option>
                            <option value="0" <?= (isset($_GET["orderbyGia"]) && $_GET["orderbyGia"] === "0") ? "selected" : "" ?>>💵 Thấp → Cao</option>
                            <option value="1" <?= (isset($_GET["orderbyGia"]) && $_GET["orderbyGia"] === "1") ? "selected" : "" ?>>💰 Cao → Thấp</option>
                        </select>

                    </div>
                </form>

                <?php if (empty($hoadon)): ?>
                    <div class="alert alert-warning">Không có đơn hàng nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Mã</th>
                                    <th>Người đặt</th>
                                    <th>Trạng thái đơn hàng</th>
                                    <th>Phương thức thanh toán</th>
                                    <th>Trạng thái thanh toán</th>
                                    <th>Thời gian đặt hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php foreach ($hoadon as $hd): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($hd["ma"]) ?></td>
                                        <th><?= $hd["ten"] ?></th>
                                        <td>
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $hd["id"] ?>">
                                                <select name="trangThaiDH" id="trangThaiDH" class="form-select" onchange="this.form.submit()">
                                                    <option <?= $hd["trangThaiDH"] == "Đang xác nhận" ? "selected" : "" ?> value="Đang xác nhận">Đang xác nhận</option>
                                                    <option <?= $hd["trangThaiDH"] == "Đã xác nhận" ? "selected" : "" ?> value="Đã xác nhận">Đã xác nhận</option>
                                                    <option <?= $hd["trangThaiDH"] == "Đang giao hàng" ? "selected" : "" ?> value="Đang giao hàng">Đang giao hàng</option>
                                                    <option <?= $hd["trangThaiDH"] == "Giao hàng thành công" ? "selected" : "" ?> value="Giao hàng thành công">Giao hàng thành công</option>
                                                    <option <?= $hd["trangThaiDH"] == "Trả hàng" ? "selected" : "" ?> value="Trả hàng">Trả hàng</option>
                                                    <option <?= $hd["trangThaiDH"] == "Hoàn thành" ? "selected" : "" ?> value="Hoàn thành">Hoàn thành</option>
                                                    <option <?= $hd["trangThaiDH"] == "Đã hủy" ? "selected" : "" ?> value="Đã hủy">Đã hủy</option>
                                                </select>
                                            </form>
                                        </td>
                                        <!-- Phương thức thanh toán -->
                                        <td>
                                            <?php if ($hd["phuongThucTT"] == "cod"): ?>
                                                <span class="badge bg-secondary"><i class="bi bi-truck"></i> Thanh toán khi nhận hàng (COD)</span>
                                            <?php elseif ($hd["phuongThucTT"] == "vnpay"): ?>
                                                <span class="badge bg-info text-dark"><i class="bi bi-credit-card"></i> VN PAY</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark">Không rõ</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Trạng thái thanh toán -->
                                        <td>
                                            <?php if ($hd["trangThaiTT"] == 1): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Đã thanh toán</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Chưa thanh toán</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date("d-m-Y H:m:s", strtotime($hd["thoiGianMua"])); ?>
                                        </td>
                                        <th><?= number_format($hd["tongTien"]) ?>đ</th>
                                        <td class="text-nowrap">
                                            <a href="chitiet.php?id=<?= $vou["id"] ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye-fill"></i>
                                                <span>Chi tiết</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Phân trang -->
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ["page" => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>

                <?php endif; ?>
            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>