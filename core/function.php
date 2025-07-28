<?php
function test_input($data)
{
    $data = trim($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmpty(&$err, $data, $name)
{
    if (trim($data) == "") {
        $err[$name] = "Vui lòng nhập $name";
    }
}

function validateEmail(&$err, $email)
{
    if (trim($email) == "") {
        $err["email"] = "Vui lòng nhập email";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err["email"] = "Email không hợp lệ";
    }
}

function validatePhone(&$err, $phone)
{
    if (trim($phone) == "") {
        $err["soDienThoai"] = "Vui lòng nhập phone";
    } else {
        if ($phone[0] != "0") {
            $err["soDienThoai"] = "Phone bắt đầu từ số 0";
        } else if (strlen($phone) != 10) {
            $err["soDienThoai"] = "Phone gồm 10 số";
        }
    }
}

function validatePassword(&$err, $password)
{
    if (trim($password) == "") {
        $err["matKhau"] = "Vui lòng nhập password";
    } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,20}$/', $password)) {
        $err["matKhau"] = "Password gồm chữ thường, số, chữ hoa, kí tự đặc biệt, từ 8-20 kí tự";
    }
}

function validateImg(&$err, $dir, $img)
{
    $file = $dir . basename($img["name"]);
    $typeFile = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $check = getimagesize($img["tmp_name"]);

    if ($check === false) {
        $err["img"] = "Đây không phải là ảnh";
        return null;
    }

    if (!in_array($typeFile, ["jpg", "jpeg", "png", "gif", "webp"])) {
        $err["img"] = "Chỉ cho phép file jpg, jpeg, png, gif, webp";
        return null;
    }

    if ($img["size"] > 2 * 1024 * 1024) {
        $err["img"] = "Ảnh không được vượt quá 2MB";
        return null;
    }

    if (file_exists($file)) {
        $err["img"] = "Ảnh đã tồn tại";
        return null;
    }

    if (empty($err)) {
        if (move_uploaded_file($img["tmp_name"], $file)) {
            return basename($img["name"]);
        } else {
            $err["img"] = "Upload ảnh thất bại";
            return null;
        }
    }
}


function generateOTP($length = 6)
{
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= rand(0, 9);
    }
    return $otp;
}

function insertAllTinh($db)
{
    $url = "https://online-gateway.ghn.vn/shiip/public-api/master-data/province";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Token: " . GHN_TOKEN],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    if (!isset($data['data']) || $data['code'] != 200) {
        echo "❌ Lỗi khi gọi API tỉnh<br>";
        return;
    }

    foreach ($data['data'] as $province) {
        $db->execute(
            "INSERT IGNORE INTO tinh (id, ten) VALUES (?, ?)",
            [$province['ProvinceID'], $province['ProvinceName']]
        );
    }

    echo "✔️ Đã lưu " . count($data['data']) . " tỉnh vào DB.<br>";
}

function insertAllHuyen($db)
{
    $tinhList = $db->getAll("SELECT * FROM tinh");

    foreach ($tinhList as $tinh) {
        $provinceId = $tinh['id'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://online-gateway.ghn.vn/shiip/public-api/master-data/district",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(["province_id" => $provinceId]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Token: " . GHN_TOKEN
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);
        $districts = $data['data'] ?? [];

        if (!is_array($districts)) {
            echo "❌ Không lấy được huyện cho tỉnh $provinceId<br>";
            continue;
        }

        foreach ($districts as $district) {
            $db->execute(
                "INSERT IGNORE INTO huyen (id, ten, idTinh) VALUES (?, ?, ?)",
                [$district['DistrictID'], $district['DistrictName'], $provinceId]
            );
        }

        usleep(200000); // nghỉ nhẹ
    }

    echo "✔️ Đã lưu tất cả huyện vào DB.<br>";
}

function insertAllXa($db)
{
    $huyenList = $db->getAll("SELECT * FROM huyen");
    $token = GHN_TOKEN;

    foreach ($huyenList as $index => $huyen) {
        $districtId = $huyen['id'];
        echo "🔄 Đang xử lý huyện: {$districtId} - {$huyen['ten']}<br>";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id=$districtId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Token: $token"],
            CURLOPT_TIMEOUT => 10
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);
        $wards = $data['data'] ?? [];

        if (!$wards || !is_array($wards)) {
            echo "❌ Lỗi khi lấy xã cho huyện $districtId<br>";
            continue;
        }

        $values = [];
        $params = [];
        foreach ($wards as $ward) {
            if (!$ward['WardCode']) continue;
            $values[] = "(?, ?, ?)";
            $params[] = $ward['WardCode'];
            $params[] = $ward['WardName'];
            $params[] = $districtId;
        }

        if (!empty($values)) {
            $sql = "INSERT IGNORE INTO xa (id, ten, idHuyen) VALUES " . implode(', ', $values);
            $db->execute($sql, $params);
            echo "✅ Đã thêm " . count($wards) . " xã cho huyện {$huyen['ten']}<br>";
        }

        usleep(200000);
    }

    echo "<br>🎉 Đã lưu tất cả xã vào DB thành công.<br>";
}

function tinhPhiGHN($db, $fromDistrict, $tongTienDonHang, $loaiGHN, $idDC) {
    $phiShip = 30000; // phí mặc định
    $ghiChu = "Phí mặc định";
    $thongBao = "";

    // Kiểm tra địa chỉ
    if (empty($idDC)) {
        return [
            "phiShip" => $phiShip,
            "ghiChu"  => $ghiChu,
            "thongBao"=> "<div class='alert alert-info'>💬 Bạn chưa chọn địa chỉ giao hàng.</div>"
        ];
    }

    $dc = $db->getOne("SELECT * FROM diachi WHERE id = ?", [$idDC]);
    if (!$dc || empty($dc["idXa"])) {
        return [
            "phiShip" => $phiShip,
            "ghiChu"  => $ghiChu,
            "thongBao"=> "<div class='alert alert-danger'>❌ Không tìm thấy địa chỉ giao hàng.</div>"
        ];
    }

    $idXa = $dc["idXa"];

    // Lấy mã GHN từ DB
    $xa = $db->getOne("
        SELECT b.maGHN AS wardCode, c.maGHN AS districtId 
        FROM xa b
        JOIN huyen c ON b.idHuyen = c.maGHN
        WHERE b.maGHN = ?", 
        [$idXa]
    );

    if (!$xa || empty($xa["wardCode"]) || empty($xa["districtId"])) {
        return [
            "phiShip" => $phiShip,
            "ghiChu"  => $ghiChu,
            "thongBao"=> "<div class='alert alert-danger'>❌ Khu vực của bạn chưa được GHN hỗ trợ. Vui lòng chọn địa chỉ khác.</div>"
        ];
    }

    // Dữ liệu gửi tới GHN
    $postData = [
        "service_type_id"   => (int)$loaiGHN,
        "insurance_value"   => (int)$tongTienDonHang,
        "coupon"            => null,
        "from_district_id"  => (int)$fromDistrict,
        "to_district_id"    => (int)$xa["districtId"],
        "to_ward_code"      => (string)$xa["wardCode"],
        "weight"            => 500,
        "length"            => 20,
        "width"             => 20,
        "height"            => 10
    ];

    // Gọi API GHN
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
        $ghiChu = "Phí GHN tính toán";
    } else {
        $thongBao = "<div class='alert alert-warning'>⚠ Không thể tính phí giao hàng cho địa chỉ này. Hệ thống sẽ dùng phí mặc định.</div>";
    }

    return [
        "phiShip" => $phiShip,
        "ghiChu"  => $ghiChu,
        "thongBao"=> $thongBao
    ];
}

