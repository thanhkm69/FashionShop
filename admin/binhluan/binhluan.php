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

if (isset($_POST["trangThai"])) {
    $id = $_POST["id"];
    $trangThai = $_POST["trangThai"];
    $db->execute("UPDATE binhluansp SET trangThai = ? WHERE id = ?", [$trangThai, $id]);
    $_SESSION["thongBao"] = "Cập nhập trang thái bình luận thành công";
    header("location: binhluan.php");
    exit;
}

if (isset($_GET["trangThai"]) && $_GET["trangThai"] != "") {
    $where[] = "d.trangThai = ?";
    $bind[] = test_input($_GET["trangThai"]);
};

if (isset($_GET["sao"]) && $_GET["sao"] != "") {
    $where[] = "d.sao = ?";
    $bind[] = test_input($_GET["sao"]);
};


$orderby = "ORDER BY d.ngayTao DESC";
if (isset($_GET["orderby"])) {
    $order = $_GET["orderby"];
    if ($order) {
        $orderby = "ORDER BY d.sao DESC";
    } else {
        $orderby = "ORDER BY d.sao ASC";
    }
}


if (!empty($where)) {
    $whereClase = " WHERE " . implode(" AND ", $where);
}


$totalRows = $db->getValue("SELECT COUNT(*) FROM binhluansp d $whereClase", $bind);

$totalPages = ceil($totalRows / $limit);

$danhGia = $db->getAll("SELECT a.*,b.mau,b.hinh as hinhMau,c.size,d.id as idDG,d.noiDung,d.sao,d.ngayTao,d.trangThai,e.hinh as hinhND,e.ten as tenND FROM sanpham a JOIN mau b ON a.id = b.idSanPham JOIN bienthesize c ON b.id = c.idMau JOIN binhluansp d ON c.id = d.idSize JOIN nguoidung e ON d.idNguoiDung = e.id $whereClase $orderby LIMIT $limit OFFSET $offset", $bind);


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý voucher</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tab-filter {
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            white-space: nowrap;
            overflow-x: auto;
        }

        .tab-filter .tab-item {
            padding: 12px 20px;
            display: inline-block;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            transition: color 0.2s ease;
        }

        .tab-filter .tab-item.active {
            color: #ee4d2d;
        }

        .tab-filter .tab-item.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: #ee4d2d;
        }

        .tab-filter .tab-item:hover {
            color: #ee4d2d;
        }

        .tab-filter .tab-item {
            padding: 8px 16px;
            display: inline-block;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab-filter .tab-item.active {
            color: #ee4d2d;
            border-bottom: 2px solid #ee4d2d;
        }

        .tab-filter .tab-item:hover {
            color: #ee4d2d;
            cursor: pointer;
        }
    </style>
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

                <!-- Dòng lọc trạng thái -->
                <div class="tab-filter mb-4">
                    <form method="get" class="d-flex">
                        <?php
                        $trangThaiList = [
                            "" => "Tất cả",
                            "0" => "Không hiển thị",
                            "1" => "Hiển thị"
                        ];

                        $currentTT = $_GET["trangThai"] ?? "";
                        foreach ($trangThaiList as $value => $label): ?>
                            <?php
                            if ($value == "") {
                                $count = $db->getValue("SELECT COUNT(*) FROM binhluansp");
                            } else {
                                $count = $db->getValue("SELECT COUNT(*) FROM binhluansp WHERE trangThai = ?", [$value]);
                            }
                            ?>
                            <button type="submit" name="trangThai" value="<?= $value ?>"
                                class="tab-item flex-grow-1 <?= ($value == $currentTT) ? 'active' : '' ?>">
                                <?= $label . " ($count)" ?>
                            </button>
                        <?php endforeach; ?>
                    </form>
                </div>

                <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <!-- Trạng thái ẩn -->
                    <input type="hidden" name="trangThai" value="<?= htmlspecialchars($_GET["trangThai"] ?? '') ?>">

                    <!-- Bên trái: Hiển thị số bản ghi -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <label for="limit" class="form-label mb-0 me-2">
                            <i class="bi bi-list-ol me-1"></i> Hiển thị:
                        </label>
                        <select name="limit" id="limit" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                            <option value="5" <?= (isset($_GET["limit"]) && $_GET["limit"] == 5) ? "selected" : "" ?>>5</option>
                            <option value="10" <?= (isset($_GET["limit"]) && $_GET["limit"] == 10) ? "selected" : "" ?>>10</option>
                            <option value="20" <?= (isset($_GET["limit"]) && $_GET["limit"] == 20) ? "selected" : "" ?>>20</option>
                            <option value="50" <?= (isset($_GET["limit"]) && $_GET["limit"] == 50) ? "selected" : "" ?>>50</option>
                        </select>
                        <span class="ms-1">bản ghi/trang</span>
                    </div>

                    <!-- Bộ lọc sao -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <label for="sao" class="form-label mb-0 me-2">
                            <i class="bi bi-star-fill text-warning me-1"></i> Số sao:
                        </label>
                        <select name="sao" id="sao" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                            <option value="">Tất cả</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET["sao"]) && $_GET["sao"] == $i) ? "selected" : "" ?>>
                                    <?= $i ?> <?= str_repeat("★", $i) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Sắp xếp -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <label for="orderby" class="form-label mb-0 me-2">
                            <i class="bi bi-sort-down-alt me-1"></i> Sắp xếp:
                        </label>
                        <select name="orderby" id="orderby" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                            <option value="">Theo sao</option>
                            <option value="0" <?= (isset($_GET["orderby"]) && $_GET["orderby"] === "0") ? "selected" : "" ?>>⭐ Thấp → Cao</option>
                            <option value="1" <?= (isset($_GET["orderby"]) && $_GET["orderby"] === "1") ? "selected" : "" ?>>⭐ Cao → Thấp</option>
                        </select>
                    </div>
                </form>

                <?php if (empty($danhGia)): ?>
                    <div class="alert alert-warning">Không có đánh giá nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Người đánh giá</th>
                                    <th>Sản phẩm</th>
                                    <th>Nội dung</th>
                                    <th>Số sao</th>
                                    <th>Ngày đánh giá</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="text-center align-middle">
                                <?php foreach ($danhGia as $dg): ?>
                                    <tr>
                                        <!-- Người đánh giá -->
                                        <td class="d-flex align-items-center gap-2">
                                            <img src="<?= $dir . $dg["hinhND"] ?>" alt="Avatar" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                                            <span><?= htmlspecialchars($dg["tenND"]) ?></span>
                                        </td>

                                        <!-- Sản phẩm -->
                                        <td class="text-start">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= $dir . $dg["hinhMau"] ?>" alt="SP" class="rounded" width="60" height="60" style="object-fit:cover;">
                                                <div>
                                                    <div><strong><?= htmlspecialchars($dg["ten"]) ?></strong></div>
                                                    <div class="text-muted small">Màu: <?= htmlspecialchars($dg["mau"]) ?> | Size: <?= htmlspecialchars($dg["size"]) ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Nội dung -->
                                        <td class="text-start"><?= nl2br(htmlspecialchars($dg["noiDung"])) ?></td>

                                        <!-- Số sao -->
                                        <td>
                                            <?php
                                            $sao = (int)$dg["sao"];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $sao
                                                    ? '<i class="bi bi-star-fill text-warning"></i>'
                                                    : '<i class="bi bi-star text-muted"></i>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Ngày đánh giá -->
                                        <td class="text-nowrap"><?= date("d-m-Y H:i:s", strtotime($dg["ngayTao"])); ?></td>

                                        <!-- Trạng thái + chọn -->
                                        <td>
                                            <div class="d-flex flex-column align-items-center gap-2">
                                                <!-- Badge trạng thái -->
                                                <span class="badge px-3 py-2 rounded-pill fw-semibold <?= $dg["trangThai"] == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                                    <i class="bi <?= $dg["trangThai"] == 1 ? 'bi-eye-fill' : 'bi-eye-slash-fill' ?> me-1"></i>
                                                    <?= $dg["trangThai"] == 1 ? 'Hiển' : 'Ẩn' ?>
                                                </span>

                                                <!-- Form đổi trạng thái -->
                                                <form method="post" class="d-flex align-items-center gap-1">
                                                    <input type="hidden" name="id" value="<?= $dg["idDG"] ?>">
                                                    <select name="trangThai" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                                                        <option value="1" <?= $dg["trangThai"] == 1 ? "selected" : "" ?>>Hiển</option>
                                                        <option value="0" <?= $dg["trangThai"] == 0 ? "selected" : "" ?>>Ẩn</option>
                                                    </select>
                                                </form>
                                            </div>
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