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
$idSanPham = $_GET["id"];

$sp = $db->getOne("SELECT a.*, b.ten AS tenDM FROM sanpham a 
                        JOIN danhmuc b ON a.idDanhMuc = b.id 
                        WHERE a.id = ?", [$idSanPham]);

$btm = $db->getAll("SELECT * FROM mau WHERE idSanPham = ?", [$idSanPham]);

$bts = $db->getAll("SELECT b.*
                   FROM mau a 
                   JOIN bienthesize b ON a.id = b.idMau 
                   WHERE idSanPham = ?
                   ORDER BY a.mau
                   ", [$idSanPham]);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <!-- Action bar -->
                <div class="action-bar d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Chi tiết sản phẩm</span>
                    </div>
                    <a href="sanpham.php" class="btn btn-outline-secondary d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i> Quay lại
                    </a>
                </div>

                <!-- Card chứa thông tin sản phẩm -->
                <div class="card shadow-sm">
                    <div class="row g-0">
                        <!-- Hình ảnh sản phẩm -->
                        <div class="col-md-4 text-center border-end p-4">
                            <img src="<?= $dir . htmlspecialchars($sp['hinh']) ?>" alt="Hình sản phẩm" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                        </div>

                        <!-- Thông tin chi tiết -->
                        <div class="col-md-8">
                            <div class="card-body p-4">
                                <h4 class="card-title fw-bold mb-3"><?= htmlspecialchars($sp['ten']) ?></h4>

                                <div class="mb-2">
                                    <i class="bi bi-tag-fill text-primary me-1"></i>
                                    <strong>Danh mục:</strong> <?= htmlspecialchars($sp['tenDM']) ?>
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-currency-dollar text-success me-1"></i>
                                    <strong>Giá gốc:</strong> <?= number_format($sp['giaGoc']) ?>₫
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-cash-coin text-danger me-1"></i>
                                    <strong>Giá khuyến mãi:</strong> <?= number_format($sp['giaKhuyenMai']) ?>₫
                                </div>

                                <div class="mb-3">
                                    <i class="bi bi-text-left me-1 text-info"></i>
                                    <strong>Mô tả:</strong>
                                    <textarea class="form-control bg-light border rounded mt-1"
                                        rows="5" readonly
                                        style="max-height: 150px; overflow-y: auto; resize: none;"><?=
                                                                                                    preg_replace('/^\s*[\r\n]+/', '', trim($sp['moTa'])) ?></textarea>
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-eye<?= $sp['trangThai'] == 0 ? '-slash' : '' ?> text-<?= $sp['trangThai'] == 1 ? 'success' : 'secondary' ?> me-1"></i>
                                    <strong>Trạng thái:</strong>
                                    <?= $sp['trangThai'] == 1 ? 'Hiển thị' : 'Ẩn' ?>
                                </div>

                                <div>
                                    <i class="bi bi-calendar-event me-1 text-warning"></i>
                                    <strong>Ngày tạo:</strong>
                                    <?= date('d/m/Y H:i', strtotime($sp['ngayTao'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container px-0">
                    <?php foreach ($btm as $mau): ?>
                        <div class="row border rounded mb-4 shadow-sm bg-white p-3">
                            <!-- Cột trái: Hình + Màu -->
                            <div class="col-md-2 text-center d-flex flex-column align-items-center justify-content-center gap-2">
                                <img src="<?= $dir . htmlspecialchars($mau['hinh']) ?>"
                                    alt="<?= htmlspecialchars($mau['mau']) ?>"
                                    class="img-fluid rounded border shadow-sm"
                                    style="max-height: 100px; object-fit: cover;">

                                <span class="badge bg-dark text-white px-3 py-2 w-100 text-center">
                                    <i class="bi bi-palette-fill me-1"></i><?= htmlspecialchars($mau['mau']) ?>
                                </span>
                            </div>

                            <!-- Cột phải: Bảng size -->
                            <div class="col-md-10">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th><i class="bi bi-arrows-angle-expand me-1 text-primary"></i>Size</th>
                                            <th><i class="bi bi-box-seam me-1 text-success"></i>Số lượng</th>
                                            <th><i class="bi bi-card-text me-1 text-info"></i>Mô tả</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bts as $size): ?>
                                            <?php if ($size['idMau'] == $mau['id']): ?>
                                                <tr>
                                                    <td class="text-center"><?= htmlspecialchars($size['size']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($size['soLuong']) ?></td>
                                                    <td><?= htmlspecialchars($size['moTaSize']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>