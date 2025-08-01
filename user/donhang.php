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
$where = [];
$bind = [];

if (isset($_GET["trangThai"]) && $_GET["trangThai"] != "") {
    $where[] = "a.trangThaiDH = ?";
    $bind[] = test_input($_GET["trangThai"]);
}

$whereClause = "";
if (!empty($where)) {
    $whereClause = " AND " . implode(" AND ", $where);
}

$sql = "SELECT a.*, dc.id as idDC, dc.chiTiet, x.ten as tenXa, h.ten as tenHuyen, t.ten as tenTinh 
    FROM hoadon a
    JOIN diachi dc ON a.idDiaChi = dc.id 
    JOIN xa x ON dc.idXa = x.maGHN 
    JOIN huyen h ON x.idHuyen = h.maGHN 
    JOIN tinh t ON h.idTinh = t.maGHN 
    WHERE a.idNguoiDung = ? $whereClause
    ORDER BY a.thoiGianMua DESC";

$params = array_merge([$id], $bind);
$hoaDonList = $db->getAll($sql, $params);


$diaChi = $db->getAll("SELECT a.id,b.maGHN,b.ten as tenXa,c.ten as tenHuyen,d.ten as tenTinh,a.macDinh,a.chiTiet 
FROM diachi a 
JOIN xa b ON a.idXa = b.maGHN 
JOIN huyen c ON b.idHuyen = c.maGHN 
JOIN tinh d ON c.idTinh = d.maGHN 
WHERE idNguoiDung = ?", [$id]);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["huydon"])) {
        $idHoaDon = $_POST["id"];
        $trangThai = "Đã hủy";

        // Cập nhật trạng thái đơn hàng
        $db->execute("UPDATE hoadon SET trangThaiDH = ? WHERE id = ?", [$trangThai, $idHoaDon]);

        // Lấy chi tiết đơn hàng
        $chiTiet = $db->getAll("SELECT idSize, soLuong FROM chitiethoadon WHERE idHoaDon = ?", [$idHoaDon]);

        // Cập nhật lại số lượng tồn kho cho từng idSize
        foreach ($chiTiet as $item) {
            $idSize = $item['idSize'];
            $soLuong = $item['soLuong'];

            $db->execute("UPDATE bienthesize SET soLuong = soLuong + ? WHERE id = ?", [$soLuong, $idSize]);
        }

        // Chuyển hướng
        header("location: donhang.php?trangThai=" . (isset($_GET["trangThai"]) ? $_GET["trangThai"] : ""));
        exit;
    }

    if (isset($_POST["thayDoiDC"])) {
        $idHoaDon = $_POST["id"];
        $tongTienDonHang = 1000000;
        $result = tinhPhiGHN($db, $id, 1552, $tongTienDonHang, 2, $_POST["dc"]);

        if (empty($result["thongBao"])) {
            $phiShipMoi = $result["phiShip"];
            $hd = $db->getOne("SELECT * FROM hoadon WHERE id = ?", [$idHoaDon]);
            $phiShipCu = $hd["phiShip"];
            $tongTien = $hd["tongTien"];
            $tongTien = $tongTien - $phiShipCu + $phiShipMoi;
            $db->execute("UPDATE hoadon SET phiShip = ? , tongTien = ?, idDiaChi = ? WHERE id = ?", [$phiShipMoi, $tongTien, $_POST["dc"], $idHoaDon]);
        }
        // Chuyển hướng
        header("location: donhang.php?trangThai=" . (isset($_GET["trangThai"]) ? $_GET["trangThai"] : ""));
        exit;
    }
    if (isset($_POST["mualai"])) {
        $idHoaDon = $_POST["id"];
        $hdct = $db->getAll("SELECT a.*, b.size ,b.soLuong as soLuongTK,c.mau,d.ten FROM chitiethoadon a 
                         JOIN bienthesize b ON a.idSize = b.id 
                         JOIN mau c ON b.idMau = c.id 
                         JOIN sanpham d ON c.idSanPham = d.id
                         WHERE idHoaDon = ?", [$idHoaDon]);


        $loi = [];

        // Kiểm tra tồn kho trước
        foreach ($hdct as $ct) {
            if (intval($ct["soLuong"]) > intval($ct["soLuongTK"])) {
                $loi[] = "Sản phẩm <strong>{$ct["ten"]}</strong> - Màu <strong>{$ct["mau"]}</strong> - Size <strong>{$ct["size"]}</strong> không đủ hàng (còn {$ct["soLuongTK"]}, cần {$ct["soLuong"]})";
            }
        }

        // echo "<pre>";
        // var_dump($loi);
        // exit;
        // echo "</pre>";

        if (!empty($loi)) {
            $_SESSION["loiMuaLai"] = $loi;
            header("Location: {$_SERVER["HTTP_REFERER"]}");
            exit;
        }

        // Nếu đủ hàng thì thêm vào giỏ
        foreach ($hdct as $ct) {
            $db->execute(
                "INSERT INTO giohang (idSize,idNguoiDung,gia,soLuong) VALUES (?,?,?,?)",
                [$ct["idSize"], $id, $ct["gia"], $ct["soLuong"]]
            );
        }

        $trangThai = isset($_GET["trangThai"]) ? $_GET["trangThai"] : "";
        header("location: dathang.php?trangThai=" . urlencode($trangThai));
        exit;
    }


    if (isset($_POST["hoanthanh"])) {
        $idHoaDon = $_POST["id"];
        $trangThai = "Hoàn thành";

        // Cập nhật trạng thái đơn hàng thành "Hoàn thành"
        $db->execute("UPDATE hoadon SET trangThaiDH = ? WHERE id = ?", [$trangThai, $idHoaDon]);

        // (Tùy chọn) Có thể thực hiện các hành động khác nếu cần

        // Chuyển hướng lại trang đơn hàng
        header("location: donhang.php?trangThai=" . (isset($_GET["trangThai"]) ? $_GET["trangThai"] : ""));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đơn hàng</title>
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
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php' ?>
            <div class="container py-4">
                <h4> <i class="bi bi-bag-check-fill text-danger me-2"></i>Danh sách đơn hàng</h4>
                <p class="text-muted">Các đơn hàng của bạn</p>
                <hr>

                <?php if (!empty($_SESSION["thongBao"])): ?>
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <?= $_SESSION["thongBao"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php $_SESSION["thongBao"] = ""; ?>
                <?php endif; ?>
                <form method="get">
                    <div class="tab-filter d-flex">
                        <?php
                        $trangThaiList = [
                            "" => "Tất cả",
                            "Đang xác nhận" => "Đang xác nhận",
                            "Đã xác nhận" => "Đã xác nhận",
                            "Đang giao hàng" => "Đang giao hàng",
                            "Giao hàng thành công" => "Giao hàng thành công",
                            "Trả hàng" => "Trả hàng",
                            "Hoàn thành" => "Hoàn thành",
                            "Đã hủy" => "Đã hủy",
                        ];

                        $currentTT = $_GET["trangThai"] ?? "";
                        foreach ($trangThaiList as $value => $label): ?>
                            <?php
                            if ($value == "") {
                                $count = $db->getValue("SELECT COUNT(*) FROM hoadon WHERE idNguoiDung = ?", [$id]);
                            } else {
                                $count = $db->getValue("SELECT COUNT(*) FROM hoadon WHERE trangThaiDH LIKE ? AND idNguoiDung = ?", [$value, $id]);
                            }
                            ?>
                            <button type="submit" name="trangThai" value="<?= $value ?>"
                                class="tab-item <?= ($value === $currentTT) ? 'active' : '' ?>">
                                <?= $label . " ($count)" ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>

                <?php if (!empty($hoaDonList)): ?>
                    <?php $stt = 1; ?>
                    <?php foreach ($hoaDonList as $don): ?>
                        <div class="card mb-4 shadow-sm rounded border-start border-4 small">
                            <!-- Header -->
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Đơn hàng #<?= $don["ma"] ?></strong> - <?= date("d/m/Y H:i", strtotime($don["thoiGianMua"])) ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php
                                    $trangThaiDH = $don["trangThaiDH"];
                                    $badgeClass = match ($trangThaiDH) {
                                        "Đang xác nhận" => "bg-warning text-dark",
                                        "Đã xác nhận" => "bg-primary",
                                        "Đang giao hàng" => "bg-dark text-white",
                                        "Giao hàng thành công" => "bg-info text-dark",
                                        "Hoàn thành" => "bg-success",
                                        "Đã hủy" => "bg-secondary",
                                        default => "bg-light"
                                    };
                                    ?>
                                    <!-- Trạng thái đơn hàng -->
                                    <span class="badge <?= $badgeClass ?>"><i class="bi bi-truck"></i> <?= $trangThaiDH ?></span>

                                    <!-- Trạng thái thanh toán -->
                                    <?= $don["trangThaiTT"] == 1
                                        ? '<span class="badge bg-success"><i class="bi bi-credit-card"></i> Đã thanh toán</span>'
                                        : '<span class="badge bg-danger"><i class="bi bi-cash"></i> Chưa thanh toán</span>' ?>
                                </div>
                            </div>
                            <?php $trangThai = $don["trangThaiDH"]; ?>
                            <!-- Body -->
                            <div class="card-body">
                                <div class="d-flex align-items-center flex-nowrap" style="gap: 8px;margin-bottom: 1rem;">
                                    <strong>Phương thức:</strong> <?= strtoupper($don["phuongThucTT"]) ?> |
                                    <strong class="ms-2">Địa chỉ giao hàng:</strong>
                                    <form action="" method="post" class="d-inline-flex align-items-center ms-2" style="gap: 6px; flex-wrap: nowrap;">
                                        <select <?= $trangThai != "Đang xác nhận" ? "disabled" : "" ?> name="dc" class="form-select form-select-sm" style="width: 500px; min-width: 200px;" required>
                                            <?php foreach ($diaChi as $item) { ?>
                                                <option <?= $don["idDC"] == $item["id"] ? "selected" : "" ?> value="<?= $item["id"] ?>">
                                                    <?= "{$item["chiTiet"]}, {$item["tenXa"]}, {$item["tenHuyen"]}, {$item["tenTinh"]}" ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <?php if ($trangThai == "Đang xác nhận") { ?>
                                            <input type="hidden" name="id" value="<?= $don["id"] ?>">
                                            <button type="submit" name="thayDoiDC" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-geo-alt-fill"></i> Thay đổi
                                            </button>
                                        <?php } ?>
                                    </form>
                                </div>


                                <!-- Bảng sản phẩm -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-nowrap align-middle text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Ảnh</th>
                                                <th>Tên sản phẩm</th>
                                                <th>Màu</th>
                                                <th>Size</th>
                                                <th>Giá</th>
                                                <th>Số lượng</th>
                                                <th>Tổng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $chiTiet = $db->getAll("SELECT b.*, c.size, d.mau, d.hinh, e.ten as tenSP
                        FROM chitiethoadon b 
                        JOIN bienthesize c ON b.idSize = c.id 
                        JOIN mau d ON c.idMau = d.id 
                        JOIN sanpham e ON d.idSanPham = e.id 
                        WHERE b.idHoaDon = ?", [$don["id"]]);
                                            $i = 1;
                                            foreach ($chiTiet as $ct): ?>
                                                <tr>
                                                    <?php if (!empty($_SESSION["loiMuaLai"])): ?>
                                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            <?php foreach ($_SESSION["loiMuaLai"] as $loi): ?>
                                                                <div>- <?= $loi ?></div>
                                                            <?php endforeach; ?>
                                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                                                        </div>
                                                        <?php unset($_SESSION["loiMuaLai"]); ?>
                                                    <?php endif; ?>
                                                    <td><?= $i++ ?></td>
                                                    <td><img src="../uploads/<?= $ct["hinh"] ?>" alt="" width="60"></td>
                                                    <td><?= $ct["tenSP"] ?></td>
                                                    <td><?= $ct["mau"] ?></td>
                                                    <td><?= $ct["size"] ?></td>
                                                    <td><?= number_format($ct["gia"]) ?> đ</td>
                                                    <td><?= $ct["soLuong"] ?></td>
                                                    <td><?= number_format($ct["tongTien"]) ?> đ</td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tổng kết & Nút hành động -->
                                <div class="row align-items-center mt-4">
                                    <!-- Nút hành động -->
                                    <div class="col-md-6 text-start">

                                        <?php if ($trangThai === "Đang xác nhận"): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $don["id"] ?>">
                                                <button type="submit" name="huydon" class="btn btn-outline-danger" onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');">
                                                    <i class="bi bi-x-octagon-fill"></i> Hủy đơn hàng
                                                </button>
                                            </form>

                                        <?php elseif ($trangThai === "Đã xác nhận"): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $don["id"] ?>">
                                                <button type="submit" name="huydon" class="btn btn-outline-danger" onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');">
                                                    <i class="bi bi-x-octagon-fill"></i> Hủy đơn hàng
                                                </button>
                                            </form>

                                        <?php elseif ($trangThai === "Giao hàng thành công"): ?>
                                            <a href="tra_hang.php?id=<?= $don["id"] ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-box-arrow-in-left"></i> Trả đơn hàng
                                            </a>
                                            <!-- Nút mở modal -->
                                            <button type="button" class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#cartOptionModal<?= $don['id'] ?>" title="Hoàn thành">
                                                <i class="bi bi-bag-check"></i> Hoàn thành
                                            </button>

                                            <!-- Modal xác nhận hoàn thành -->
                                            <div class="modal fade" id="cartOptionModal<?= $don["id"] ?>" tabindex="-1" aria-labelledby="cartOptionModalLabel<?= $don["id"] ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <form method="post" action="">
                                                            <input type="hidden" name="id" value="<?= $don["id"] ?>">

                                                            <!-- Modal Header -->
                                                            <div class="modal-header border-bottom-0">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Xác nhận hoàn thành đơn hàng
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                                            </div>

                                                            <!-- Modal Body -->
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning small d-flex align-items-center gap-2" role="alert">
                                                                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                                                                    <div>
                                                                        Sau khi xác nhận <strong>Hoàn thành</strong>, bạn sẽ <u>không thể trả hàng</u> cho đơn này nữa.
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Modal Footer -->
                                                            <div class="modal-footer border-top-0 p-2">
                                                                <div class="d-flex w-100 gap-2">
                                                                    <button type="submit" name="hoanthanh" class="btn btn-dark w-50">
                                                                        <i class="bi bi-check-circle me-1"></i> Xác nhận hoàn thành
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">
                                                                        <i class="bi bi-x-circle me-1"></i> Hủy
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>


                                        <?php elseif ($trangThai === "Hoàn thành"): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $don["id"] ?>">
                                                <a href="danhgia.php?id=<?= $don["id"] ?>" class="btn btn-outline-success me-2">
                                                    <i class="bi bi-star-fill"></i> Đánh giá sản phẩm
                                                </a>
                                                <button type="submit" name="mualai" class="btn btn-outline-primary">
                                                    <i class="bi bi-cart-plus"></i> Mua lại đơn hàng
                                                </button>
                                            </form>
                                        <?php elseif ($trangThai === "Đã hủy"): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="id" value="<?= $don["id"] ?>">
                                                <button type="submit" name="mualai" class="btn btn-outline-primary">
                                                    <i class="bi bi-cart-plus"></i> Mua lại đơn hàng
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tổng kết -->
                                    <div class="col-md-6 text-end">
                                        <p>Giảm giá: <strong><?= ($don["giam"] ?? 0) ? number_format($don["giam"]) . " đ" : "Không có" ?></strong></p>
                                        <p>Phí vận chuyển: <strong><?= number_format($don["phiShip"]) ?> đ</strong></p>
                                        <h5 class="text-danger">Tổng tiền: <?= number_format($don["tongTien"]) ?> đ</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">Bạn chưa có đơn hàng nào.</div>
                <?php endif; ?>
            </div>
            <?php include '../admin/include/footer.php' ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>