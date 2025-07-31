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


if (isset($_GET["trangThai"]) && $_GET["trangThai"] != "") {
    $where[] = "trangThai = ?";
    $bind[] = test_input($_GET["trangThai"]);
};


$orderby = "ORDER BY thoiGian DESC";


if (!empty($where)) {
    $whereClase = " WHERE " . implode(" AND ", $where);
}


$totalRows = $db->getValue("SELECT COUNT(*) FROM binhluansp d $whereClase", $bind);

$totalPages = ceil($totalRows / $limit);

$lienhe = $db->getAll("SELECT * FROM lienhe $whereClase $orderby LIMIT $limit OFFSET $offset", $bind);


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

        i {
            display: inline !important;
            vertical-align: middle;
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
                            "0" => "Chưa trả lời",
                            "1" => "Đã trả lời"
                        ];

                        $currentTT = $_GET["trangThai"] ?? "";
                        foreach ($trangThaiList as $value => $label): ?>
                            <?php
                            if ($value == "") {
                                $count = $db->getValue("SELECT COUNT(*) FROM lienhe");
                            } else {
                                $count = $db->getValue("SELECT COUNT(*) FROM lienhe WHERE trangThai = ?", [$value]);
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
                </form>

                <?php if (empty($lienhe)): ?>
                    <div class="alert alert-warning">Không có liên hệ nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle shadow-sm">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th><i class="bi bi-person-fill me-1"></i> Người liên hệ</th>
                                    <th><i class="bi bi-chat-dots-fill me-1"></i> Nội dung</th>
                                    <th><i class="bi bi-clock-fill me-1"></i> Thời gian</th>
                                    <th><i class="bi bi-reply-fill me-1"></i> Trả lời</th>
                                    <th><i class="bi bi-clock-history me-1"></i> Thời gian TL</th>
                                    <th><i class="bi bi-gear-fill me-1"></i> Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="text-center align-middle">
                                <?php foreach ($lienhe as $lh): ?>
                                    <tr>
                                        <!-- Người liên hệ -->
                                        <td class="text-start">
                                            <div class="d-flex flex-column align-items-start gap-1">
                                                <!-- Tên + SĐT trên 1 dòng -->
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <span><i class="bi bi-person-fill me-1 text-primary"></i> <?= htmlspecialchars($lh["ten"]) ?></span>
                                                    <span><i class="bi bi-telephone-fill me-1 text-success"></i> <?= htmlspecialchars($lh["sdt"]) ?></span>
                                                </div>
                                                <!-- Email bên dưới -->
                                                <span class="d-inline-flex align-items-center">
                                                    <i class="bi bi-envelope-fill me-1 text-warning"></i> <?= htmlspecialchars($lh["email"]) ?>
                                                </span>

                                            </div>
                                        </td>
                                        <!-- Nội dung -->
                                        <td class="text-start">
                                            <div class="px-2"><?= nl2br(htmlspecialchars($lh["noiDung"])) ?></div>
                                        </td>

                                        <!-- Thời gian gửi -->
                                        <td class="text-nowrap"><?= date("d-m-Y H:i:s", strtotime($lh["thoiGian"])) ?></td>

                                        <!-- Nội dung trả lời -->
                                        <td class="text-start">
                                            <?= $lh["traLoi"] ? nl2br(htmlspecialchars($lh["traLoi"])) : '<span class="text-muted fst-italic">Chưa có phản hồi</span>' ?>
                                        </td>

                                        <!-- Thời gian trả lời -->
                                        <td class="text-nowrap">
                                            <?= $lh["thoiGianTL"] ? date("d-m-Y H:i:s", strtotime($lh["thoiGianTL"])) : '<span class="text-muted">—</span>' ?>
                                        </td>

                                        <!-- Trạng thái -->
                                        <td>
                                            <div class="d-flex flex-column align-items-center gap-2">
                                                <?php if ($lh["trangThai"] == 1): ?>
                                                    <span class="badge rounded-pill px-3 py-2 fw-semibold bg-success-subtle text-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Đã trả lời
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill px-3 py-2 fw-semibold bg-danger-subtle text-danger">
                                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Chưa trả lời
                                                    </span>
                                                    <a href="traloi.php?id=<?= $lh["id"] ?>" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                                        <i class="bi bi-reply-fill"></i> Trả lời
                                                    </a>
                                                <?php endif; ?>
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