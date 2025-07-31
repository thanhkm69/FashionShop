<?php
require_once "../core/db_utils.php";

$idNguoiDung = $_SESSION["nguoiDung"]["id"] ?? null;

$email = $db->getOne("SELECT email FROM nguoidung WHERE id = ?", [$idNguoiDung]);

$dir = "../uploads/";
$dirMenu = "../";

if (!$idNguoiDung) {
    header("Location: ../dangnhap.php");
    exit;
}

if (isset($_GET["idSize"], $_GET["soLuong"])) {
    $idSize = intval($_GET["idSize"]);
    $soLuong = intval($_GET["soLuong"]);

    // Trường hợp 1: Lấy 1 sản phẩm theo idSize, số lượng từ URL
    $cart = $db->getAll("
        SELECT 
            0 AS id, 
            b.id AS idSize, 
            d.giaKhuyenMai AS gia, 
            ? AS soLuong, 
            ? AS idNguoiDung, 
            b.size, 
            b.soLuong AS soLuongTK, 
            c.hinh, 
            c.mau, 
            c.idSanPham, 
            d.ten, 
            e.ten AS tenDM
        FROM bienthesize b
        JOIN mau c ON b.idMau = c.id 
        JOIN sanpham d ON c.idSanPham = d.id 
        JOIN danhmuc e ON d.idDanhMuc = e.id 
        WHERE b.id = ?
    ", [$soLuong, $idNguoiDung, $idSize]);
} else {
    // Trường hợp 2: Lấy toàn bộ giỏ hàng thật từ bảng `giohang`
    $cart = $db->getAll("
        SELECT 
            a.id, a.idSize, a.gia, a.soLuong, a.idNguoiDung, 
            b.size, b.soLuong AS soLuongTK, 
            c.hinh, c.mau, c.idSanPham, 
            d.ten, 
            e.ten AS tenDM 
        FROM giohang a 
        JOIN bienthesize b ON a.idSize = b.id 
        JOIN mau c ON b.idMau = c.id 
        JOIN sanpham d ON c.idSanPham = d.id 
        JOIN danhmuc e ON d.idDanhMuc = e.id 
        WHERE a.idNguoiDung = ?
    ", [$idNguoiDung]);
}

// Lấy thông tin người dùng + địa chỉ + voucher
$thongTin = $db->getOne("SELECT * FROM nguoiDung WHERE id = ?", [$idNguoiDung]);
$diaChi = $db->getAll("SELECT a.id,b.maGHN,b.ten as tenXa,c.ten as tenHuyen,d.ten as tenTinh,a.macDinh,a.chiTiet 
FROM diachi a 
JOIN xa b ON a.idXa = b.maGHN 
JOIN huyen c ON b.idHuyen = c.maGHN 
JOIN tinh d ON c.idTinh = d.maGHN 
WHERE idNguoiDung = ?", [$idNguoiDung]);
$voucher = $db->getAll("SELECT a.id,b.ma,b.kieu,b.giam,b.donToiDa,b.giamToiDa,a.idVoucher
FROM vouchernguoidung a 
JOIN voucher b ON a.idVoucher = b.id 
WHERE a.idNguoiDung = ?", [$idNguoiDung]);

// Tính tạm tính
if (isset($_GET["idSize"], $_GET["soLuong"])) {
    $tong = $cart[0]["gia"] * $cart[0]["soLuong"];
} else {
    $tong = $db->getValue("SELECT SUM(gia * soLuong) FROM giohang WHERE idNguoiDung = ?", [$idNguoiDung]) ?? 0;
}

// Phí ship mặc định
$fromDistrict = 1552;
$phiShip = 30000;
$ghiChuShip = "Phí mặc định";
$loaiGHN = $_POST["loaiGHN"] ?? 2;
$thongBaoGHN = "";

// Xử lý địa chỉ được chọn
$idXa = null;
if (!empty($_POST["diaChi"])) {
    $dc = $db->getOne("SELECT * FROM diachi WHERE id = ?", [$_POST["diaChi"]]);
    $idXa = $dc["idXa"] ?? null;
} else {
    $macDinh = $db->getOne("SELECT idXa FROM diachi WHERE idNguoiDung = ? AND macDinh = 1", [$idNguoiDung]);
    $idXa = $macDinh["idXa"] ?? null;
}

// Gọi API GHN nếu có xã
if ($idXa) {
    $xa = $db->getOne("SELECT b.maGHN AS wardCode, c.maGHN AS districtId 
        FROM xa b 
        JOIN huyen c ON b.idHuyen = c.maGHN 
        WHERE b.maGHN = ?", [$idXa]);

    if ($xa && $xa["wardCode"] && $xa["districtId"]) {
        $postData = [
            "service_type_id"   => (int)$loaiGHN,
            "insurance_value"   => 1000000,
            "coupon"            => null,
            "from_district_id"  => $fromDistrict,
            "to_district_id"    => (int)$xa["districtId"],
            "to_ward_code"      => (string)$xa["wardCode"],
            "weight"            => 500,
            "length"            => 20,
            "width"             => 20,
            "height"            => 10
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Token: " . GHN_TOKEN,
                "ShopId: " . GHN_SHOP_ID
            ]
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        if (!empty($result["data"]["total"])) {
            $phiShip = $result["data"]["total"];
            $ghiChuShip = "Phí GHN tính toán";
        } else {
            $thongBaoGHN = "<div class='alert alert-warning'>⚠ Không thể tính phí giao hàng cho địa chỉ này. Hệ thống sẽ dùng phí mặc định.</div>";
        }
    } else {
        $thongBaoGHN = "<div class='alert alert-danger'>❌ Khu vực của bạn chưa được GHN hỗ trợ. Vui lòng chọn địa chỉ khác.</div>";
    }
} else {
    $thongBaoGHN = "<div class='alert alert-info'>💬 Bạn chưa chọn địa chỉ giao hàng.</div>";
}

// Xử lý giảm giá voucher
$gg = 0;
$voucherID_applied = null;
$maxDiscount = 0;

foreach ($voucher as &$item) {
    if ($tong < $item["donToiDa"]) {
        $item["trangThai"] = 1;
        $item["discount"] = 0;
        continue;
    }

    $item["trangThai"] = 0;

    if ($item["kieu"] == "0") {
        $discount = $item["giam"]; // Giảm cố định
    } else {
        $discount = $tong * ($item["giam"] / 100);
        if ($item["giamToiDa"] > 0 && $discount > $item["giamToiDa"]) {
            $discount = $item["giamToiDa"];
        }
    }

    $item["discount"] = $discount; // Ghi vào mỗi item để hiển thị sau này

    if ($discount > $maxDiscount) {
        $maxDiscount = $discount;
        $gg = $discount;
        $voucherID_applied = $item["idVoucher"];
    }
}
unset($item);



if (isset($_POST["voucher"])) {
    $voucherID = $_POST["voucher"];

    if ($voucherID != "") {
        $vou = $db->getOne("SELECT a.id, b.ma, b.kieu, b.giam, b.donToiDa, b.giamToiDa 
            FROM vouchernguoidung a 
            JOIN voucher b ON a.idVoucher = b.id 
            WHERE a.idNguoiDung = ? AND a.idVoucher = ?", [$idNguoiDung, $voucherID]);

        if ($vou["kieu"] == "0") {
            $gg = $vou["giam"];
        } else {
            $gg = $tong * ($vou["giam"] / 100);
            if ($vou["giamToiDa"] > 0 && $gg > $vou["giamToiDa"]) {
                $gg = $vou["giamToiDa"];
            }
        }
    } else {
        // Không chọn voucher → không giảm
        $gg = 0;
    }
}


// echo "<pre>";
// var_dump($voucher);
// echo "</pre>";
// Tính tổng tiền cuối cùng
$tongTien = $tong - $gg + $phiShip;

// Đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["dathang"])) {
    $ten = trim($_POST["hoTen"] ?? "");
    $soDienThoai = trim($_POST["soDienThoai"] ?? "");
    $idDiaChi = $_POST["diaChi"];
    $phuongThucTT = $_POST["payment"];
    $voucherND = $_POST["voucher"] ?? null;

    if ($ten === "") $err["ten"] = "Vui lòng nhập tên người nhận";
    if ($soDienThoai === "") $err["soDienThoai"] = "Vui lòng nhập số điện thoại";
    if ($idDiaChi === "") $err["diaChi"] = "Vui lòng chọn địa chỉ giao hàng";

    if (empty($err)) {
        $ma = generateOTP();

        if ($voucherND != "" && $voucherND != null) {
            $db->execute("INSERT INTO hoadon(idNguoiDung,ten,soDienThoai,idDiaChi,ma,phuongThucTT,phiShip,tongTien,idVoucher,giam) 
        VALUES (?,?,?,?,?,?,?,?,?,?)", [$idNguoiDung, $ten, $soDienThoai, $idDiaChi, $ma, $phuongThucTT, $phiShip, $tongTien, $voucherND, $gg]);
        } else {
            $db->execute("INSERT INTO hoadon(idNguoiDung,ten,soDienThoai,idDiaChi,ma,phuongThucTT,phiShip,tongTien) 
        VALUES (?,?,?,?,?,?,?,?)", [$idNguoiDung, $ten, $soDienThoai, $idDiaChi, $ma, $phuongThucTT, $phiShip, $tongTien]);
        }

        $idHoaDon = $db->getLastInsertId();

        // Insert chi tiết hóa đơn
        foreach ($cart as $item) {
            $tongSP = $item["gia"] * $item["soLuong"];
            $db->execute("INSERT INTO chitiethoadon (idHoaDon,idSize,gia,soLuong,tongTien) VALUES (?,?,?,?,?)", [
                $idHoaDon,
                $item["idSize"],
                $item["gia"],
                $item["soLuong"],
                $tongSP
            ]);
            $db->execute("DELETE FROM gioHang WHERE id = ?", [$item["id"]]);
        }

        $chiTiet = $db->getAll("SELECT idSize, soLuong FROM chitiethoadon WHERE idHoaDon = ?", [$idHoaDon]);

        // Cập nhật lại số lượng tồn kho cho từng idSize
        foreach ($chiTiet as $item) {
            $idSize = $item['idSize'];
            $soLuong = $item['soLuong'];

            $db->execute("UPDATE bienthesize SET soLuong = soLuong - ? WHERE id = ?", [$soLuong, $idSize]);
        }

        if ($voucherND != "" && $voucherND != null) {
            $db->execute("DELETE FROM vouchernguoidung WHERE idVoucher = ?", [$voucherND]);
        }
        // Lấy chi tiết hóa 
        $chiTietHD = $db->getAll("
    SELECT 
        a.*, 
        b.ma, b.ten AS tenNguoiNhan, b.phiShip, b.giam, b.soDienThoai, b.tongTien AS tongHD, b.thoiGianMua, b.phuongThucTT,
        c.size, 
        d.hinh, d.mau, 
        e.ten AS tenSanPham, 
        dc.chiTiet AS chiTietDiaChi, 
        x.ten AS tenXa, 
        h.ten AS tenHuyen, 
        t.ten AS tenTinh
    FROM chitiethoadon a 
    JOIN hoadon b ON a.idHoaDon = b.id 
    JOIN bienthesize c ON a.idSize = c.id
    JOIN mau d ON c.idMau = d.id
    JOIN sanpham e ON d.idSanPham = e.id
    JOIN diachi dc ON b.idDiaChi = dc.id
    JOIN xa x ON dc.idXa = x.maGHN
    JOIN huyen h ON x.idHuyen = h.maGHN
    JOIN tinh t ON h.idTinh = t.maGHN
    WHERE a.idHoaDon = ?", [$idHoaDon]);

        // Nếu không có đơn hàng => dừng
        if (empty($chiTietHD)) {
            return;
        }

        $first = $chiTietHD[0]; // Dữ liệu chung (người nhận, phí ship, ...)
        $addressOrders = $first["chiTietDiaChi"] . ', ' . $first["tenXa"] . ', ' . $first["tenHuyen"] . ', ' . $first["tenTinh"];
        $methodPay = $first["phuongThucTT"];
        // Khởi tạo email
        $subject = "Đặt hàng thành công - FashionShop";

        $body = '
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Xác nhận đơn hàng</title>
</head>
<body style="font-family: Arial, sans-serif;">

  <h2>Cảm ơn bạn đã đặt hàng tại FashionShop</h2>
  <p>Xin chào <strong>' . htmlspecialchars($first["tenNguoiNhan"]) . '</strong>,</p>
  <p>Đơn hàng của bạn với mã <strong>' . $first["ma"] . '</strong> đã được ghi nhận.</p>

  <h3>Thông tin đơn hàng</h3>
  <ul>
    <li><strong>Ngày đặt:</strong> ' . date("Y-m-d H:i:A", strtotime($first["thoiGianMua"])) . '</li>
    <li><strong>SĐT:</strong> ' . $first["soDienThoai"] . '</li>
    <li><strong>Địa chỉ:</strong> ' . htmlspecialchars($addressOrders) . '</li>
    <li><strong>Phương thức thanh toán:</strong> ' . strtoupper($methodPay) . '</li>
  </ul>

  <h3>Chi tiết đơn hàng</h3>
  <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
      <tr>
        <th>Hình</th>
        <th>Sản phẩm</th>
        <th>Màu</th>
        <th>Size</th>
        <th>Số lượng</th>
        <th>Đơn giá</th>
        <th>Thành tiền</th>
      </tr>
    </thead>
    <tbody>';
        foreach ($chiTietHD as $item) {
            $body .= '
      <tr>
        <td style="text-align:center;">
            <img src="' . 'http://localhost/FashionShop/uploads/' . htmlspecialchars($item["hinh"]) . htmlspecialchars($item["hinh"]) . '" alt="ảnh" width="60" height="60" style="object-fit:cover;">
        </td>
        <td>' . $item["tenSanPham"] . '</td>
        <td>' . $item["mau"] . '</td>
        <td>' . $item["size"] . '</td>
        <td>' . $item["soLuong"] . '</td>
        <td>' . number_format($item["gia"]) . '₫</td>
        <td>' . number_format($item["tongTien"]) . '₫</td>
      </tr>';
        }

        // Tổng kết phí ship, giảm, tổng cộng
        $body .= '
      <tr>
        <td colspan="5" style="text-align:right;"><strong>Phí ship</strong></td>
        <td>' . number_format($first["phiShip"]) . '₫</td>
      </tr>
      <tr>
        <td colspan="5" style="text-align:right;"><strong>Giảm giá voucher</strong></td>
        <td>- ' . number_format($first["giam"]) . '₫</td>
      </tr>
      <tr>
        <td colspan="5" style="text-align:right;"><strong>Tổng cộng</strong></td>
        <td><strong>' . number_format($first["tongHD"]) . '₫</strong></td>
      </tr>
    </tbody>
  </table>

  <p>Cảm ơn bạn đã mua sắm cùng chúng tôi!</p>
</body>
</html>';

        MailService::send($email["email"], USERNAME_EMAIL, $subject, $body);
        header("Location: dathangTC.php?ma={$ma}");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng</title>
    <style>
        .cart-table img.cart-product-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .cart-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .cart-table td {
            vertical-align: middle;
        }

        .cart-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        #cart-empty {
            font-size: 18px;
            font-weight: 500;
            color: #6c757d;
        }

        .cart-remove svg {
            transition: 0.2s;
        }

        .cart-remove:hover svg {
            stroke: #dc3545;
            transform: scale(1.2);
        }

        #checkout-btn {
            font-size: 16px;
            padding: 10px;
            border-radius: 8px;
        }

        .cart-table img.cart-product-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <?php require_once "../include/header.php"; ?>
    <div class="container my-5">
        <h4 class="mb-4" style="font-family: 'Playfair Display', serif;">
            <i class="bi bi-receipt-cutoff me-2"></i>Xác nhận đơn hàng
        </h4>
        <form action="" method="post">
            <div class="row g-4">
                <!-- Cột trái -->
                <div class="col-lg-8">

                    <!-- 1. Thông tin nhận hàng -->
                    <div class="card mb-4 shadow-sm border-0 rounded-3">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Thông tin nhận hàng
                        </div>
                        <div class="card-body">
                            <!-- Tên và số điện thoại cùng dòng -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="form-label fw-semibold mb-1">Người nhận</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" name="hoTen" value="<?= $thongTin["ten"] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1">Số điện thoại</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="text" class="form-control" name="soDienThoai" value="<?= $thongTin["soDienThoai"] ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Chọn địa chỉ giao hàng -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">Địa chỉ giao hàng</label>
                                <select onchange="this.form.submit()" name="diaChi" class="form-select" required>
                                    <option value="">Chọn địa chỉ</option>
                                    <?php foreach ($diaChi as $item) {
                                        $selected = '';
                                        if (isset($_POST["diaChi"]) && $_POST["diaChi"] == $item["id"]) {
                                            $selected = "selected";
                                        } elseif (!isset($_POST["diaChi"]) && $item["macDinh"] == "1") {
                                            $selected = "selected";
                                        }
                                    ?>
                                        <option <?= $selected ?> value="<?= $item["id"] ?>">
                                            <?= "{$item["chiTiet"]}, {$item["tenXa"]}, {$item["tenHuyen"]}, {$item["tenTinh"]}" ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- Thêm địa chỉ -->
                            <div class="text-end">
                                <a href="<?= $dirMenu ?>user/diachi.php" class="text-decoration-none small text-primary">
                                    <i class="bi bi-plus-circle"></i> Thêm địa chỉ mới
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Sản phẩm trong đơn hàng -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-box-seam me-2"></i>Sản phẩm
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-3 g-3">
                                <?php foreach ($cart as $item): ?>
                                    <div class="col">
                                        <div class="card h-100 border rounded-3">
                                            <div class="row g-0">
                                                <div class="col-4">
                                                    <img src="<?= $dir . $item["hinh"] ?>" class="img-fluid rounded-start" alt="<?= $item["ten"] ?>" style="height: 100%; object-fit: cover;">
                                                </div>
                                                <div class="col-8">
                                                    <div class="card-body py-2 px-3">
                                                        <h6 class="card-title mb-1"><?= $item["ten"] ?></h6>
                                                        <p class="card-text small text-muted mb-2"><?= $item["tenDM"] ?> • Màu: <?= $item["mau"] ?> • Size: <?= $item["size"] ?></p>
                                                        <p class="mb-1">Số lượng: <?= $item["soLuong"] ?></p>
                                                        <div class="fw-semibold text-danger"><?= number_format($item["gia"]) ?>₫</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if (!empty($thongBaoGHN)): ?>
                                <div class="alert alert-warning">
                                    <?= $thongBaoGHN ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="voucher" class="form-label fw-bold">
                                    <i class="bi bi-ticket-detailed me-1"></i>Chọn mã giảm giá
                                </label>
                                <?php if (empty($voucher)) { ?>
                                    <div class="alert alert-warning mt-2">
                                        Bạn hiện không có mã giảm giá nào.
                                    </div>
                                <?php } else { ?>
                                    <select onchange="this.form.submit()" class="form-select" id="voucher" name="voucher">
                                        <option value="" <?= (isset($_POST["voucher"]) && $_POST["voucher"] === "") ? "selected" : "" ?>>
                                            Không áp dụng mã giảm giá
                                        </option>

                                        <?php foreach ($voucher as $item):
                                            $selected = (isset($_POST["voucher"]) && $_POST["voucher"] == $item["idVoucher"]) ||
                                                (!isset($_POST["voucher"]) && $voucherID_applied == $item["idVoucher"]) ? "selected" : "";

                                            $disabled = $item["trangThai"] == 1 ? "disabled" : "";

                                            // Xây dựng note
                                            $note = "";
                                            if ($item["trangThai"] == 1) {
                                                $note = " (Đơn tối thiểu " . number_format($item["donToiDa"]) . "đ)";
                                            } elseif (!isset($_POST["voucher"]) && $voucherID_applied == $item["idVoucher"]) {
                                                // Chưa chọn mã thủ công và đây là mã tốt nhất
                                                $note = " (Lựa chọn tốt nhất - Giảm " . number_format($item["discount"]) . "đ)";
                                            } elseif (isset($_POST["voucher"]) && $_POST["voucher"] == $item["idVoucher"]) {
                                                // Người dùng chọn thủ công mã này
                                                $note = " (Giảm " . number_format($item["discount"]) . "đ)";
                                            } elseif ($item["discount"] > 0) {
                                                // Mã hợp lệ nhưng không phải đang được chọn
                                                $note = " (Giảm " . number_format($item["discount"]) . "đ)";
                                            }
                                        ?>
                                            <option value="<?= $item["idVoucher"] ?>" <?= $selected ?> <?= $disabled ?>>
                                                <?= $item["ma"] . $note ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>



                                <?php } ?>

                                <!-- Phí vận chuyển -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-truck me-1"></i>Phí vận chuyển
                                    </label>
                                    <input name="phiShip" type="text" readonly class="form-control"
                                        value="<?= number_format($phiShip) ?>₫<?= $ghiChuShip ? " ({$ghiChuShip})" : "" ?>"
                                        id="shippingFee">
                                </div>

                                <!-- Phương thức thanh toán -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-credit-card-2-front me-1"></i>Phương thức thanh toán
                                    </label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment" id="cod" value="cod" checked>
                                        <label class="form-check-label" for="cod">
                                            <i class="bi bi-cash-coin me-1"></i> Thanh toán khi nhận hàng (COD)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment" id="vnpay" value="vnpay">
                                        <label class="form-check-label" for="vnpay">
                                            <i class="bi bi-bank me-1"></i> Thanh toán qua VNPAY
                                        </label>
                                    </div>
                                </div>

                                <!-- Tổng tiền -->
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tạm tính:</span>
                                        <span><?= number_format($tong) ?>₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Voucher:</span>
                                        <span>-<?= number_format($gg) ?>₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phí ship:</span>
                                        <span><?= number_format($phiShip) ?>₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-3">
                                        <span>Tổng cộng:</span>
                                        <span id="cart-total"><?= number_format($tongTien) ?>₫</span>
                                    </div>
                                    <button name="dathang" type="submit" class="btn btn-danger w-100 mt-4">
                                        <i class="bi bi-bag-check me-1"></i> Đặt hàng
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
        </form>
    </div>
    <?php require_once "../include/footer.php"; ?>
</body>

</html>