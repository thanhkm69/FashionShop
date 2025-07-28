<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
}else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}


$limit = isset($_GET["limit"]) && is_numeric($_GET["limit"]) ? (int) $_GET["limit"] : 5;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $limit;

$sqlBase = "FROM danhmuc";
$where = [];
$bind = [];

// Xử lý cập nhật trạng thái 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["updateStatus"])) {
        $db->execute("UPDATE danhmuc SET trangThai = ? WHERE id = ?", [$_POST["trangThai"], $_POST["id"]]);
        $_SESSION["thongBao"] = "Cập nhật trạng thái thành công!";
        header("Location: danhmuc.php");
        exit;
    }
}

// Xử lý tìm kiếm
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $categoriesID = $_GET["categoriesID"] ?? "";
    $nameCategories = $_GET["nameCategories"] ?? "";
    $status = $_GET["status"] ?? "";

    if ($status != "") {
        $where[] = "trangThai = ?";
        $bind[] = $status;
    }

    if (!empty($categoriesID)) {
        $where[] = "id = ?";
        $bind[] = $categoriesID;
    }

    if (!empty($nameCategories)) {
        $where[] = "ten LIKE ?";
        $bind[] = "%" . $nameCategories . "%";
    }
}
// Sắp xếp
$orderClause = "ORDER BY ten ASC";
if (isset($_GET["orderby"])) {
    if ($_GET["orderby"] === "1") {
        $orderClause = "ORDER BY ten DESC";
    } elseif ($_GET["orderby"] === "0") {
        $orderClause = "ORDER BY ten ASC";
    }
}

$whereClause = "";
if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
}


$countSql = "SELECT COUNT(*) AS total " . $sqlBase . $whereClause;
$totalRows = $db->getOne($countSql, $bind)["total"];
$totalPages = ceil($totalRows / $limit);


$sql = "SELECT * " . $sqlBase . $whereClause . " $orderClause LIMIT $limit OFFSET $offset";

$result = $db->getAll($sql, $bind);

$dir = "../../uploads/";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục</title>
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
                        <i class="bi bi-folder-fill fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Danh mục</span>
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
                <form class="row g-2 mb-3" method="get">
                    <div class="col-md-3">
                        <input value="<?= isset($categoriesID) ? $categoriesID : "" ?>" type="number" name="categoriesID" class="form-control" placeholder="ID">
                    </div>
                    <div class="col-md-3">
                        <input value="<?= isset($nameCategories) ? $nameCategories : "" ?>" type="text" name="nameCategories" class="form-control" placeholder="Tên">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="1" <?= (isset($_GET["status"]) && $_GET["status"] === "1") ? "selected" : "" ?>>✅ Hiện</option>
                            <option value="0" <?= (isset($_GET["status"]) && $_GET["status"] === "0") ? "selected" : "" ?>>❌ Ẩn</option>
                        </select>
                    </div>
                     <div class="col-md-2">
                        <button type="submit" name="search" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </div>

                    <!-- Reset button -->
                    <div class="col-md-2">
                        <a href="product.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-repeat"></i> Xóa
                        </a>
                    </div>
                </form>


                <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <!-- Giữ lại các tham số tìm kiếm -->
                    <input type="hidden" name="search" value="1">
                    <input type="hidden" name="id" value="<?= $_GET["categoriesID"] ?? "" ?>">
                    <input type="hidden" name="ten" value="<?= $_GET["nameCategories"] ?? "" ?>">
                    <input type="hidden" name="email" value="<?= $_GET["status"] ?? "" ?>">

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
                        <select name="orderby" id="orderby" onchange="this.form.submit()" class="form-select w-auto">
                            <option value="">-- Chọn --</option>
                            <option value="0" <?= (isset($_GET["orderby"]) && $_GET["orderby"] === "0") ? "selected" : "" ?>>📈A → Z</option>
                            <option value="1" <?= (isset($_GET["orderby"]) && $_GET["orderby"] === "1") ? "selected" : "" ?>>📉Z → A</option>
                        </select>
                    </div>
                </form>

                <?php if (empty($result)): ?>
                    <div class="alert alert-warning">Không có danh mục nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th style="width: 20%;">ID</th>
                                    <th style="width: 20%;">Ảnh</th>
                                    <th style="width: 20%;">Tên</th>
                                    <th style="width: 20%;">Trạng thái</th>
                                    <th style="width: 20%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php foreach ($result as $danhmuc): ?>
                                    <tr>
                                        <td><?= $danhmuc["id"] ?></td>
                                        <td>
                                            <img src="<?= $dir . htmlspecialchars($danhmuc["hinh"]) ?>"
                                                alt="Ảnh"
                                                class="img-thumbnail"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td class="text-start"><?= htmlspecialchars($danhmuc["ten"]) ?></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $danhmuc["id"] ?>">
                                                <select name="trangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="1" <?= $danhmuc["trangThai"] == 1 ? 'selected' : '' ?>>✅ Hiện</option>
                                                    <option value="0" <?= $danhmuc["trangThai"] == 0 ? 'selected' : '' ?>>❌ Ẩn</option>
                                                </select>
                                                <input type="hidden" name="updateStatus" value="1">
                                            </form>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="sua.php?id=<?= $danhmuc["id"] ?>" class="btn btn-warning btn-sm me-1">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>
                                            <a href="xoa.php?id=<?= $danhmuc["id"] ?>" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Xóa
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