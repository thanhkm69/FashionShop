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

$countRows = $db->getValue("SELECT COUNT(*) FROM vouchernguoidung $whereClause", $bind);

$totalPages = ceil($countRows / $limit);

// 5. Truy vấn DB
$sql = "SELECT b.hinh,b.ma,b.moTa FROM vouchernguoidung a join voucher b ON a.idVoucher = b.id $whereClause $orderBy LIMIT $limit OFFSET $offset";
$voucher = $db->getAll($sql, $bind);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher của tôi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .description-clamp {
            font-size: 0.875rem;
            font-weight: 600;
            color: #333;
            line-height: 1.3;

        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: 0.2s;
        }
        
        


    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php' ?>
            <div class="container py-4">
                <h4><i class="bi bi-ticket-perforated-fill text-danger me-1"></i> Voucher của tôi</h4>
                <p class="text-muted"><i class="bi bi-gear-fill me-1"></i> Các mã giảm giá bạn đã lưu</p>
                <hr>

                <?php if (!empty($_SESSION["thongBao"])): ?>
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <?= $_SESSION["thongBao"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php $_SESSION["thongBao"] = ""; ?>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php if (!empty($voucher)) {
                        foreach ($voucher as $item):
                    ?>
                            <div class="col">
                                <div class="card shadow-sm border-0 overflow-hidden">
                                    <div class="d-flex">
                                        <!-- Cột trái: ảnh voucher -->
                                        <!-- Ảnh + phần trăm giảm -->
                                        <div class="position-relative" style="width: 100px; flex-shrink: 0;">
                                            <a href="">
                                                <img src="<?= $dir . $item['hinh'] ?>" alt="<?= $yt['ten'] ?>"
                                                    class="img-fluid object-fit-cover rounded-start"
                                                    style="height: 100px; width: 100px;">
                                            </a>
                                        </div>
                                        <!-- Cột phải: nội dung voucher -->
                                        <div class="p-2 d-flex flex-column justify-content-between w-100">
                                            <div>
                                                <span class="badge bg-danger mb-1\\"><?= htmlspecialchars($item["ma"]) ?></span>
                                                <p class="mb-0 text-muted description-clamp" style="font-size: 0.9rem;">
                                                    <?= htmlspecialchars($item["moTa"]) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php endforeach;
                    } else {
                        echo "<div class='col-12 text-muted'>Không tìm thấy voucher...</div>";
                    } ?>
                </div>


                <nav>
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ["page" => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php include '../admin/include/footer.php' ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>