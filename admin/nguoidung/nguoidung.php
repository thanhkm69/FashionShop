<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}

$limit = isset($_GET["limit"]) && is_numeric($_GET["limit"]) ? (int) $_GET["limit"] : 5;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $limit;

$sqlBase = "FROM nguoidung";
$where = [];
$bind = [];

// Xử lý cập nhật trạng thái hoặc quyền
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["updateStatus"])) {
        $db->execute("UPDATE nguoidung SET trangThai = ? WHERE id = ?", [$_POST["trangThai"], $_POST["id"]]);
        $_SESSION["thongBao"] = "Cập nhật trạng thái thành công!";
        header("Location: nguoidung.php");
        exit;
    }
    if (isset($_POST["updateRole"])) {
        $db->execute("UPDATE nguoidung SET phanQuyen = ? WHERE id = ?", [$_POST["phanQuyen"], $_POST["id"]]);
        $_SESSION["thongBao"] = "Cập nhật phân quyền thành công!";
        header("Location: nguoidung.php");
        exit;
    }
}

// Xử lý tìm kiếm
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    if (!empty($_GET["id"])) {
        $where[] = "id = ?";
        $bind[] = $_GET["id"];
    }
    if (!empty($_GET["ten"])) {
        $where[] = "ten LIKE ?";
        $bind[] = "%" . $_GET["ten"] . "%";
    }
    if (!empty($_GET["email"])) {
        $where[] = "email LIKE ?";
        $bind[] = "%" . $_GET["email"] . "%";
    }
    if (!empty($_GET["soDienThoai"])) {
        $where[] = "soDienThoai LIKE ?";
        $bind[] = "%" . $_GET["soDienThoai"] . "%";
    }
    if (isset($_GET["role"]) && $_GET["role"] !== "") {
        $where[] = "phanQuyen = ?";
        $bind[] = $_GET["role"];
    }
    if (isset($_GET["status"]) && $_GET["status"] !== "") {
        $where[] = "trangThai = ?";
        $bind[] = $_GET["status"];
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

// Đếm tổng số bản ghi
$countSql = "SELECT COUNT(*) AS total " . $sqlBase . $whereClause;
$totalRows = $db->getOne($countSql, $bind)["total"];
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách người dùng
$sql = "SELECT * " . $sqlBase . $whereClause . " $orderClause LIMIT $limit OFFSET $offset";

$result = $db->getAll($sql, $bind);

$dir = "../../uploads/";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
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
                        <i class="bi bi-people-fill fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Người dùng</span>
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

                <form method="get" class="d-flex flex-nowrap align-items-end gap-2 mb-3 w-100">
                    <input type="hidden" name="search" value="1">

                    <input class="form-control" style="flex: 1;" type="number" name="id" placeholder="ID" value="<?= $_GET["id"] ?? '' ?>">

                    <input class="form-control" style="flex: 1.5;" type="text" name="ten" placeholder="Tên" value="<?= $_GET["ten"] ?? '' ?>">

                    <input class="form-control" style="flex: 2;" type="email" name="email" placeholder="Email" value="<?= $_GET["email"] ?? '' ?>">

                    <input class="form-control" style="flex: 1.5;" type="text" name="soDienThoai" placeholder="Điện thoại" value="<?= $_GET["soDienThoai"] ?? '' ?>">

                    <select class="form-select" style="flex: 1.3;" name="role">
                        <option value="">Quyền</option>
                        <option value="Admin" <?= ($_GET["role"] ?? '') === 'Admin' ? 'selected' : '' ?>>👑 Admin</option>
                        <option value="User" <?= ($_GET["role"] ?? '') === 'User' ? 'selected' : '' ?>>👤 User</option>
                    </select>

                    <select class="form-select" style="flex: 1.3;" name="status">
                        <option value="">Trạng thái</option>
                        <option value="1" <?= ($_GET["status"] ?? '') === '1' ? 'selected' : '' ?>>✅ Hoạt động</option>
                        <option value="0" <?= ($_GET["status"] ?? '') === '0' ? 'selected' : '' ?>>❌ Vô hiệu</option>
                    </select>

                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                       <i class="bi bi-search"></i> Tìm
                    </button>

                    <a href="nguoidung.php" class="btn btn-secondary text-nowrap" style="flex: 1;">
                         <i class="bi bi-arrow-repeat"></i> Xóa
                    </a>
                </form>




                <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <!-- Giữ lại các tham số tìm kiếm -->
                    <input type="hidden" name="search" value="1">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET["id"] ?? "") ?>">
                    <input type="hidden" name="ten" value="<?= htmlspecialchars($_GET["ten"] ?? "") ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET["email"] ?? "") ?>">
                    <input type="hidden" name="soDienThoai" value="<?= $_GET["soDienThoai"] ?? "" ?>">
                    <input type="hidden" name="role" value="<?= htmlspecialchars($_GET["role"] ?? "") ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($_GET["status"] ?? "") ?>">

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
                    <div class="alert alert-warning">Không có người dùng nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Ảnh</th>
                                    <th>Email</th>
                                    <th>Tên</th>
                                    <th>Điện thoại</th>
                                    <th>Địa chỉ</th>
                                    <th>Quyền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result as $user): ?>
                                    <tr>
                                        <td><?= $user["id"] ?></td>
                                        <td class="text-center">
                                            <img src="<?= $dir . htmlspecialchars($user["hinh"]) ?>" alt="Ảnh" style="width: 50px; height: 50px;" class="img-thumbnail">
                                        </td>

                                        <td><?= htmlspecialchars($user["email"]) ?></td>
                                        <td><?= htmlspecialchars($user["ten"]) ?></td>
                                        <td><?= empty($user["soDienThoai"]) ? "Chưa có" : $user["soDienThoai"] ?></td>
                                       
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $user["id"] ?>">
                                                <select name="phanQuyen" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="Admin" <?= $user["phanQuyen"] == "Admin" ? 'selected' : '' ?>>👑 Admin</option>
                                                    <option value="User" <?= $user["phanQuyen"] == "User" ? 'selected' : '' ?>>👤 User</option>
                                                </select>
                                                <input type="hidden" name="updateRole" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $user["id"] ?>">
                                                <select name="trangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="1" <?= $user["trangThai"] == 1 ? 'selected' : '' ?>>✅ Hoạt động</option>
                                                    <option value="0" <?= $user["trangThai"] == 0 ? 'selected' : '' ?>>❌ Vô hiệu</option>
                                                </select>
                                                <input type="hidden" name="updateStatus" value="1">
                                            </form>
                                        </td>
                                         <td>
                                            <a href="diachi.php?id=<?= $user["id"] ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-geo-alt me-1"></i>Sổ địa chỉ
                                            </a>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="sua.php?id=<?= $user["id"] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square me-1"></i>Sửa</a>
                                            <a href="xoa.php?id=<?= $user["id"] ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Xóa</a>
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