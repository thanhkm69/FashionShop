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

$idHoaDon = $_GET["id"];

$tt = $db->getOne("SELECT a.*,dc.chiTiet , x.ten AS tenXa, h.ten AS tenHuyen, t.ten AS tenTinh FROM hoadon a JOIN diachi dc ON a.idDiaChi = dc.id JOIN xa x ON dc.idXa = x.maGHN JOIN huyen h ON x.idHuyen = h.maGHN JOIN tinh t ON h.idTinh = t.maGHN WHERE a.id = ?", [$idHoaDon]);
$chiTietHD = $db->getAll("SELECT a.*,c.size, d.hinh, d.mau, e.ten AS tenSanPham FROM chitiethoadon a JOIN bienthesize c ON a.idSize = c.id JOIN mau d ON c.idMau = d.id JOIN sanpham e ON d.idSanPham = e.id WHERE a.idHoaDon = ?", [$idHoaDon]);
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
        .hóa-don-thanh-toan p {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            font-size: 16px;
        }

        .hóa-don-thanh-toan i {
            font-size: 18px;
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
                        <span class="fs-5 fw-semibold align-text-bottom">Chi tiết hóa đơn</span>
                    </div>
                </div>

                <!-- Thông tin hóa đơn -->
                <div class="mb-4">
                    <h6><i class="bi bi-person-lines-fill me-2"></i>Thông tin người nhận</h6>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <div class="col col-md-8">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">
                                    <i class="bi bi-hash text-secondary me-2"></i>
                                    <strong>Mã hóa đơn:</strong> <?= $tt["id"] ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-person text-primary me-2"></i>
                                    <strong>Họ tên:</strong> <?= htmlspecialchars($tt["ten"]) ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-telephone text-success me-2"></i>
                                    <strong>Số điện thoại:</strong> <?= htmlspecialchars($tt["soDienThoai"]) ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-geo-alt text-danger me-2"></i>
                                    <strong>Địa chỉ:</strong>
                                    <?= htmlspecialchars($tt["chiTiet"] . ', ' . $tt["tenXa"] . ', ' . $tt["tenHuyen"] . ', ' . $tt["tenTinh"]) ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col col-md-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">
                                    <i class="bi bi-truck text-info me-2"></i>
                                    <strong>Trạng thái đơn hàng:</strong> <?= htmlspecialchars($tt["trangThaiDH"]) ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-cash-stack text-warning me-2"></i>
                                    <strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($tt["phuongThucTT"]) ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-receipt text-secondary me-2"></i>
                                    <strong>Trạng thái thanh toán:</strong>
                                    <?= $tt["trangThaiTT"] == 1
                                        ? '<span class="text-success">Đã thanh toán</span>'
                                        : '<span class="text-danger">Chưa thanh toán</span>' ?>
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <strong>Thời gian mua:</strong> <?= htmlspecialchars($tt["thoiGianMua"]) ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>


                <!-- Chi tiết sản phẩm -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hình</th>
                                <th>Sản phẩm</th>
                                <th>Màu</th>
                                <th>Size</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chiTietHD as $ct): ?>
                                <tr>
                                    <td><img src="<?= $dir . htmlspecialchars($ct["hinh"]) ?>" alt="" style="width: 60px; height: auto;"></td>
                                    <td><?= htmlspecialchars($ct["tenSanPham"]) ?></td>
                                    <td><?= htmlspecialchars($ct["mau"]) ?></td>
                                    <td><?= htmlspecialchars($ct["size"]) ?></td>
                                    <td><?= number_format($ct["gia"]) ?>đ</td>
                                    <td><?= $ct["soLuong"] ?></td>
                                    <td><?= number_format($ct["tongTien"]) ?>đ</td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Hàng: Giảm giá -->
                            <tr>
                                <td colspan="6" class="text-end fw-bold">
                                    <i class="bi bi-ticket-fill text-danger me-1"></i> Giảm giá:
                                </td>
                                <td>
                                    <?= $tt["giam"] !== null ? "-" . number_format($tt["giam"]) . 'đ' : '<span class="text-muted">Không có</span>' ?>
                                </td>
                            </tr>

                            <!-- Hàng: Phí ship -->
                            <tr>
                                <td colspan="6" class="text-end fw-bold">
                                    <i class="bi bi-truck text-success me-1"></i> Phí ship:
                                </td>
                                <td><?= number_format($tt["phiShip"]) ?>đ</td>
                            </tr>

                            <!-- Hàng: Tổng thanh toán -->
                            <tr>
                                <td colspan="6" class="text-end fw-bold fs-5 text-primary">
                                    <i class="bi bi-wallet2 me-1"></i> Tổng thanh toán:
                                </td>
                                <td class="fw-bold fs-5 text-primary">
                                    <?= number_format($tt["tongTien"]) ?>đ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Nút quay lại -->
                <div class="text-start">
                    <a href="hoadon.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </main>


            <?php include '../include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>