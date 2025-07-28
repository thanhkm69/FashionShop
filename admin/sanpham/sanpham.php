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

$categoriesList = $db->getAll("SELECT * FROM danhmuc");

$sqlBase = "FROM sanpham p LEFT JOIN danhmuc c ON p.idDanhMuc = c.id";

$limit = isset($_GET["limit"]) && is_numeric($_GET["limit"]) ? (int) $_GET["limit"] : 5;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;

$offset = ($page - 1) * $limit;


$where = [];
$bind = [];

// Xử lý cập nhật trạng thái hoặc quyền
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["updateStatus"])) {
        $db->execute("UPDATE sanpham SET trangThai = ? WHERE id = ?", [$_POST["trangThai"], $_POST["id"]]);
        $_SESSION["thongBao"] = "Cập nhật trạng thái thành công!";
        header("Location: sanpham.php");
        exit;
    }
}

// Xử lý tìm kiếm
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["search"])) {
        if (!empty($_GET["productID"])) {
            $where[] = "p.id = ?";
            $bind[] = intval(test_input($_GET["productID"]));
        }
        if (!empty($_GET["nameProduct"])) {
            $where[] = "p.ten LIKE ?";
            $bind[] = "%" . test_input($_GET["nameProduct"]) . "%";
        }
        if (!empty($_GET["categoriesID"])) {
            $where[] = "p.idDanhMuc = ?";
            $bind[] = intval(test_input($_GET["categoriesID"]));
        }
        if ($_GET["statusProduct"] != "") {
            $where[] = "p.trangThai = ?";
            $bind[] = $_GET["statusProduct"];
        }
    }
}
// Sắp xếp
$orderClause = "ORDER BY ten ASC";
// if (isset($_GET["orderby"])) {
//     if ($_GET["orderby"] === "1") {
//         $orderClause = "ORDER BY ten DESC";
//     } elseif ($_GET["orderby"] === "0") {
//         $orderClause = "ORDER BY ten ASC";
//     }
// }

if (isset($_GET["orderbyGia"])) {
    if ($_GET["orderbyGia"] === "1") {
        $orderClause = "ORDER BY giaGoc DESC";
    } elseif ($_GET["orderbyGia"] === "0") {
        $orderClause = "ORDER BY giaGoc ASC";
    }
}

$whereClause = "";
if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
}

// Đếm tổng số bản ghi
$countSql = "SELECT COUNT(*) AS total " . $sqlBase . $whereClause;
$totalRows = $db->getOne($countSql, $bind)["total"];
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách người dùng
$sql = "SELECT p.*, c.ten AS tenDanhMuc $sqlBase $whereClause $orderClause LIMIT $limit OFFSET $offset";

$result = $db->getAll($sql, $bind);

$dir = "../../uploads/";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
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
                        <span class="fs-5 fw-semibold align-text-bottom">Sản phẩm</span>
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

                    <!-- Product ID -->
                    <div class="col-md-2">
                        <input type="number" name="productID" class="form-control" placeholder="ID"
                            value="<?= $_GET['productID'] ?? '' ?>">
                    </div>

                    <!-- Product Name -->
                    <div class="col-md-2">
                        <input type="text" name="nameProduct" class="form-control" placeholder="Tên"
                            value="<?= $_GET['nameProduct'] ?? '' ?>">
                    </div>

                    <!-- Category -->
                    <div class="col-md-2">
                        <select name="categoriesID" class="form-select">
                            <option value="">Danh mục</option>
                            <?php foreach ($categoriesList as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['categoriesID']) && $_GET['categoriesID'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= $cat['ten'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- Status -->
                    <div class="col-md-2">
                        <select name="statusProduct" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="1" <?= ($_GET['statusProduct'] ?? '') == '1' ? 'selected' : '' ?>>✅ Hiện</option>
                            <option value="0" <?= ($_GET['statusProduct'] ?? '') === '0' ? 'selected' : '' ?>>❌ Ẩn</option>
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
                        <a href="sanpham.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-repeat"></i> Xóa
                        </a>
                    </div>
                </form>




                <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <!-- Giữ lại các tham số tìm kiếm -->
                    <input type="hidden" name="search" value="1">
                    <input type="hidden" name="productID" value="<?= htmlspecialchars($_GET["productID"] ?? "") ?>">
                    <input type="hidden" name="nameProduct" value="<?= htmlspecialchars($_GET["nameProduct"] ?? "") ?>">
                    <input type="hidden" name="categoriesID" value="<?= htmlspecialchars($_GET["categoriesID"] ?? "") ?>">
                    <input type="hidden" name="statusProduct" value="<?= $_GET["statusProduct"] ?? "" ?>">

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


                <?php if (empty($result)): ?>
                    <div class="alert alert-warning">Không có sản phẩm nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Ảnh</th>
                                    <th>Tên</th>
                                    <th>Danh mục</th>
                                    <th>Giá gốc</th>
                                    <th>Trạng thái</th>
                                    <th>Màu sắc</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php foreach ($result as $sanpham): ?>
                                    <tr>
                                        <td><?= $sanpham["id"] ?></td>
                                        <td class="text-center">
                                            <img src="<?= $dir . htmlspecialchars($sanpham["hinh"]) ?>" alt="Ảnh" style="width: 50px; height: 50px;" class="img-thumbnail">
                                        </td>
                                        <td><?= htmlspecialchars($sanpham["ten"]) ?></td>
                                        <td>
                                            <?= htmlspecialchars($sanpham["tenDanhMuc"]) ?>
                                        </td>
                                        <td><?= number_format($sanpham["giaGoc"]) ?>₫</td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $sanpham["id"] ?>">
                                                <select name="trangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="1" <?= $sanpham["trangThai"] == 1 ? 'selected' : '' ?>>✅ Hiện</option>
                                                    <option value="0" <?= $sanpham["trangThai"] == 0 ? 'selected' : '' ?>>❌ Ẩn</option>
                                                </select>
                                                <input type="hidden" name="updateStatus" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="mausac.php?id=<?= $sanpham["id"] ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-palette me-1"></i> Màu sắc
                                            </a>
                                        </td>

                                        <td class="text-nowrap">
                                            <a href="sua.php?id=<?= $sanpham["id"] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>
                                            <a href="xoa.php?id=<?= $sanpham["id"] ?>" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Xóa
                                            </a>
                                            <a href="chitietsp.php?id=<?= $sanpham["id"] ?>" class="btn btn-primary btn-sm">
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