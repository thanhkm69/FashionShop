<?php
require_once "../core/db_utils.php";
if (!isset($_SESSION["nguoiDung"])) {
    header("location: ../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}
$dir = "../uploads/";
$dirMenu = "../";
$limit = 8;

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;

$offset = ($page - 1) * $limit;

$where = [];
$bind = [];
$orderBy = "";
$whereClause = "";

$where[] = "idNguoiDung = ?";
$bind[] = $id;

if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
}

$countRows = $db->getValue("SELECT COUNT(*) FROM yeuthich $whereClause", $bind);

$totalPages = ceil($countRows / $limit);

// 5. Truy vấn DB
$sql = "SELECT b.hinh,b.ten,b.giaGoc,b.giaKhuyenMai,a.id,a.idSanPham FROM yeuthich a join sanpham b ON a.idSanPham = b.id $whereClause $orderBy LIMIT $limit OFFSET $offset";
$yeuthich = $db->getAll($sql, $bind);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idYeuThich = $_POST['id'];
    $db->execute("DELETE FROM yeuthich WHERE id = ? AND idNguoiDung = ?",[$idYeuThich,$id]);
    $_SESSION["thongBao"] = "Xóa sản phẩm yêu thích thành công";
    header("location: yeuthich.php");
    exit;
} ;


?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Yêu thích</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price-text {
            font-size: 0.9rem;
        }

        .badge-sale {
            font-size: 0.75rem;
            padding: 0.3em 0.5em;
        }

        .card:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }

        .card .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php' ?>
            <div class="container py-4">
                <h4><i class="bi bi-heart-fill text-danger me-2"></i>Danh sách yêu thích</h4>
                <p class="text-muted">Các sản phẩm bạn đã thích</p>
                <hr>

                <?php if (!empty($_SESSION["thongBao"])): ?>
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <?= $_SESSION["thongBao"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php $_SESSION["thongBao"] = ""; ?>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($yeuthich as $yt) {
                        $giaGoc = (float)$yt['giaGoc'];
                        $giaKM = (float)$yt['giaKhuyenMai'];
                        $giaHienThi = $giaKM == 0 ? $giaGoc : $giaKM;
                        $phanTramGiam = $giaKM > 0 ? round(100 - ($giaKM / $giaGoc) * 100) : 0;
                    ?>
                        <div class="col">
                            <div class="card shadow-sm border-0 overflow-hidden">
                                <div class="d-flex">
                                    <!-- Ảnh + phần trăm giảm -->
                                    <div class="position-relative" style="width: 100px; flex-shrink: 0;">
                                        <a href="chitiet.php?id=<?= $yt['idSanPham'] ?>">
                                            <img src="<?= $dir . $yt['hinh'] ?>" alt="<?= $yt['ten'] ?>"
                                                class="img-fluid object-fit-cover rounded-start"
                                                style="height: 100px; width: 100px;">
                                            <?php if ($phanTramGiam > 0): ?>
                                                <span class="badge bg-danger badge-sale position-absolute top-0 start-0 m-1">
                                                    -<?= $phanTramGiam ?>%
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </div>

                                    <!-- Nội dung -->
                                    <div class="p-2 d-flex flex-column justify-content-between w-100">
                                        <a href="chi-tiet.php?id=<?= $yt['id'] ?>" class="text-decoration-none text-dark">
                                            <h6 class="card-title" title="<?= $yt['ten'] ?>">
                                                <?= $yt['ten'] ?>
                                            </h6>
                                            <span class="text-danger fw-bold price-text">
                                                <i class="bi bi-lightning-charge me-1"></i><?= number_format($giaHienThi) ?>₫
                                            </span>
                                        </a>

                                        <!-- Nút xóa -->
                                        <div class="text-end mt-2">
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $yt['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa khỏi yêu thích">
                                                    <i class="bi bi-trash3 me-1"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Phân trang -->
                <nav>
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ["page" => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php include '../admin/include/footer.php' ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
