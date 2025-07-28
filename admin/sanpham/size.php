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
$idSanPham = $_GET["id"];
$idMau = $_GET["idMau"];
$mau = $db->getOne("SELECT hinh,mau FROM mau WHERE id = ?", [$idMau]);
$limit = isset($_GET["limit"]) && is_numeric($_GET["limit"]) ? (int) $_GET["limit"] : 5;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $limit;

$conditions = ["idMau = ?"];
$bind = [$idMau];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["them"])) {
    $tenSize = trim($_POST["tenSize"]);
    $moTaSize = trim($_POST["moTaSize"]);
    $soLuong = intval($_POST["soLuong"]);
    // $imgName = null;

    if (empty($tenSize)) {
        $err["tenSize"] = "Vui lòng nhập tên size";
    }
    if (empty($moTaSize)) {
        $err["moTaSize"] = "Vui lòng nhập mô tả size";
    }
    // if (!empty($_FILES["hinh"]["name"])) {
    //     $imgName = validateImg($err, $dir, $_FILES["hinhSize"]);
    // }
    if (empty($err)) {
        $db->execute("INSERT INTO bienthesize(idMau, size,soLuong, moTaSize) VALUES (?, ?, ?, ?)", [$idMau, $tenSize, $soLuong, $moTaSize]);
        $_SESSION["thongBao"] = "Thêm size thành công";
        header("location: size.php?id=" . $idSanPham . "&idMau=" . $idMau);
        exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $idSize = $_GET["idSize"] ?? "";
    $tenSize = $_GET["tenSize"] ?? "";
    $moTa = $_GET["moTa"] ?? "";

    if ($idSize !== "") {
        $conditions[] = "id = ?";
        $bind[] = $idSize;
    }


    if ($tenSize !== "") {
        $conditions[] = "size LIKE ?";
        $bind[] = "%$tenSize%";
    }

    if ($moTa !== "") {
        $conditions[] = "moTaSize LIKE ?";
        $bind[] = "%$moTa%";
    }
}

//  Sắp xếp
$orderClause = "ORDER BY size ASC";
if (isset($_GET["orderby"])) {
    if ($_GET["orderby"] === "1") {
        $orderClause = "ORDER BY size DESC";
    } elseif ($_GET["orderby"] === "0") {
        $orderClause = "ORDER BY size ASC";
    }
}

//  Ghép WHERE
$whereClause = " WHERE " . implode(" AND ", $conditions);

//  Lấy tổng số dòng
$countSql = "SELECT COUNT(*) AS total FROM bienthesize $whereClause";
$totalRows = $db->getOne($countSql, $bind)["total"];
$totalPages = ceil($totalRows / $limit);

//  Lấy dữ liệu phân trang
$sql = "SELECT * FROM bienthesize $whereClause $orderClause LIMIT $limit OFFSET $offset";
$result = $db->getAll($sql, $bind);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <title>Quản lí size</title>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="action-bar mb-3">
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-textarea-resize fs-4"></i>
                            <span class="fs-5 fw-semibold align-text-bottom">Size</span>

                        </div>
                        <form action="" method="post" enctype="multipart/form-data"
                            class="form-them-mau d-flex flex-wrap align-items-end bg-light p-3 rounded border gap-3 mb-4">

                            <!-- <div class="flex-grow-1" style="min-width: 200px;">
                                <label for="hinhMau" class="form-label fw-medium">🖼️ Hình ảnh</label>
                                <input type="file" class="form-control form-control-sm" id="hinhSize" name="hinhSize">
                            </div> -->

                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label for="tenSize" class="form-label fw-medium">📏 Tên size</label>
                                <input type="text" class="form-control form-control-sm" id="tenSize" name="tenSize" required>
                            </div>

                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label for="soLuong" class="form-label fw-medium">📦 Số lượng tồn kho</label>
                                <input type="number" min="0" class="form-control form-control-sm" id="soLuong" name="soLuong" required>
                            </div>

                            <div class="flex-grow-1" style="min-width: 250px;">
                                <label for="moTaSize" class="form-label fw-medium">📝 Mô tả size</label>
                                <input type="text" class="form-control form-control-sm" id="moTaSize" name="moTaSize" required>
                            </div>

                            <div>
                                <button type="submit" name="them" class="btn btn-success d-flex align-items-center px-3">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    <span>Thêm size</span>
                                </button>
                            </div>
                        </form>
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

                        <input value="<?= $idSanPham ?>" type="hidden" name="id">
                        <input value="<?= $idMau ?>" type="hidden" name="idMau">
                        <div class="col-md-2">
                            <input value="<?= isset($_GET['idSize']) ? $_GET['idSize'] : "" ?>" type="number" name="idSize" class="form-control" placeholder="ID Size">
                        </div>
                        <div class="col-md-3">
                            <input value="<?= isset($_GET['tenSize']) ? $_GET['tenSize'] : "" ?>" type="text" name="tenSize" class="form-control" placeholder="Tên Size">
                        </div>
                        <div class="col-md-3">
                            <input value="<?= isset($_GET['moTa']) ? $_GET['moTa'] : "" ?>" type="text" name="moTa" class="form-control" placeholder="Mô Tả Size">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="search" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Tìm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="size.php?id=<?= $idSanPham ?>&idMau=<?= $idMau ?>" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-repeat"></i> Xóa
                            </a>
                        </div>
                    </form>

                    <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <!-- Giữ lại các tham số tìm kiếm -->
                        <input type="hidden" name="search" value="1">
                        <input value="<?= $_GET['id'] ?>" type="hidden" name="id">
                        <input type="hidden" name="idSize" value="<?= $_GET["idSize"] ?? "" ?>">
                        <input type="hidden" name="tenSize" value="<?= $_GET["tenSize"] ?? "" ?>">
                        <input type="hidden" name="moTa" value="<?= $_GET["moTa"] ?? "" ?>">

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
                        <div class="alert alert-warning">Không có size nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center table-striped align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Màu</th>
                                        <th>Size</th>
                                        <th>Số lượng</th>
                                        <th>Mô tả size</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result as $size): ?>
                                        <tr>
                                            <td><?= $size["id"] ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <img src="<?= $dir . htmlspecialchars($mau["hinh"]) ?>" alt="Ảnh sản phẩm" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <span class="fw-semibold"><?= htmlspecialchars($mau["mau"]) ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($size["size"]) ?></td>
                                            <td><?= $size["soLuong"] ?></td>
                                            <td><?= htmlspecialchars($size["moTaSize"]) ?></td>

                                            <td class="text-nowrap text-center">
                                                <a href="suasize.php?id=<?= $idSanPham ?>&idMau=<?= $idMau ?>&idSize=<?= $size['id'] ?>" class="btn btn-warning btn-sm me-1">
                                                    <i class="bi bi-pencil-square"></i> Sửa
                                                </a>
                                                <a href="xoasize.php?id=<?= $idSanPham ?>&idMau=<?= $idMau ?>&idSize=<?= $size['id'] ?>" class="btn btn-danger btn-sm">
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
                    <a href="mausac.php?id=<?= $idSanPham ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
                    </a>
            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>