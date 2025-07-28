<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
}else {
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
    if (isset($_POST["trangThai"])) {
        $id = $_POST["id"];
        $trangThai = $_POST["trangThai"];
        $db->execute("UPDATE voucher SET trangThai = ? WHERE id = ?", [$trangThai, $id]);
        $_SESSION["thongBao"] = "Cập nhập trang thái thành công";
        header("location: voucher.php");
        exit;
    }
}

// Xử lý tìm kiếm
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["search"])) {
        if (!empty($_GET["id"])) {
            $where[] = "id = ?";
            $bind[] = intval(test_input($_GET["id"]));
        }
        if (!empty($_GET["ma"])) {
            $where[] = "ma LIKE ?";
            $bind[] = "%" . test_input($_GET["ma"]) . "%";
        }
        if (trim($_GET["kieu"]) !== "") {
            $where[] = "kieu = ?";
            $bind[] = intval(test_input($_GET["kieu"]));
        }
        if ($_GET["trangThai"] != "") {
            $where[] = "trangThai = ?";
            $bind[] = $_GET["trangThai"];
        }
    }
}

if (!empty($where)) {
    $whereClase = " WHERE " . implode(" AND ", $where);
}


$totalRows = $db->getValue("SELECT COUNT(*) FROM voucher $whereClase", $bind);

$totalPages = ceil($totalRows / $limit);

$voucher = $db->getAll("SELECT * FROM voucher $whereClase LIMIT $limit OFFSET $offset", $bind);


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
                        <span class="fs-5 fw-semibold align-text-bottom">Voucher</span>
                    </div>
                    <a href="./them.php" class="btn btn-success d-flex align-items-center px-4">
                        <i class="bi bi-plus-circle me-2"></i>
                        <span>Thêm</span>
                    </a>
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
                        <input type="number" name="id" class="form-control" placeholder="ID"
                            value="<?= $_GET['id'] ?? '' ?>">
                    </div>


                    <div class="col-md-2">
                        <input type="text" name="ma" class="form-control" placeholder="Mã"
                            value="<?= $_GET['ma'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <select name="kieu" class="form-select">
                            <option value="">Kiểu</option>
                            <option value="0" <?= (isset($_GET['kieu']) && $_GET['kieu'] == "0") ? 'selected' : '' ?>>Giảm tiền</option>
                            <option value="1" <?= (isset($_GET['kieu']) && $_GET['kieu'] == "1") ? 'selected' : '' ?>>Giảm phần trăm</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-2">
                        <select name="trangThai" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="1" <?= ($_GET['trangThai'] ?? '') == '1' ? 'selected' : '' ?>>✅ Hiện</option>
                            <option value="0" <?= ($_GET['trangThai'] ?? '') === '0' ? 'selected' : '' ?>>❌ Ẩn</option>
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
                        <a href="voucher.php" class="btn btn-outline-secondary w-100">
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
                </form>


                <?php if (empty($voucher)): ?>
                    <div class="alert alert-warning">Không có voucher nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Mã</th>
                                    <th>Kiểu</th>
                                    <th>Mô tả</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php foreach ($voucher as $vou): ?>
                                    <tr>
                                        <td><?= $vou["id"] ?></td>
                                        <td><?= htmlspecialchars($vou["ma"]) ?></td>
                                        <td>
                                            <?= $vou["kieu"] == 1 ? "Giảm phần trăm" : "Giảm tiền" ?>
                                        </td>
                                        <td class="">
                                            <?= htmlspecialchars($vou["moTa"]) ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $vou["id"] ?>">
                                                <select name="trangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="1" <?= $vou["trangThai"] == 1 ? 'selected' : '' ?>>✅ Hiện</option>
                                                    <option value="0" <?= $vou["trangThai"] == 0 ? 'selected' : '' ?>>❌ Ẩn</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="sua.php?id=<?= $vou["id"] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>
                                            <a href="xoa.php?id=<?= $vou["id"] ?>" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Xóa
                                            </a>
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