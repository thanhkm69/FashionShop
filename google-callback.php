 <?php
    require_once "./core/db_utils.php";
    $dir = "./uploads/";
    $dirMenu = "./";
    if (isset($_SESSION["nguoiDung"])) {
        header("location: index.php");
        exit();
    }

    const GG_CLIENT_ID     = '1095508817214-km1c4hdeh9eau39dnljur20q7659e942.apps.googleusercontent.com';
    const GG_CLIENT_SECRET = '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';
    const GG_REDIRECT_URI  = 'http://localhost/FashionShop/google-callback.php';

    $client = new Google_Client();
    $client->setClientId(GG_CLIENT_ID);
    $client->setClientSecret(GG_CLIENT_SECRET);
    $client->setRedirectUri(GG_REDIRECT_URI);
    $client->addScope("email");
    $client->addScope("profile");

    // Kiểm tra state (nếu bạn bật ở login)
    if (isset($_GET['state']) && isset($_SESSION['oauth2state'])) {
        if ($_GET['state'] !== $_SESSION['oauth2state']) {
            unset($_SESSION['oauth2state']);
            die('Yêu cầu không hợp lệ (state mismatch).');
        }
    }

    if (!isset($_GET['code'])) {
        die('Không thể đăng nhập bằng Google! (missing code)');
    }

    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['error'])) {
            throw new Exception($token['error_description'] ?? 'Lỗi lấy token');
        }

        $client->setAccessToken($token);

        $googleService = new Google_Service_Oauth2($client);
        $gUser = $googleService->userinfo->get();

        // Thông tin trả về
        $email    = $gUser->email ?? null;
        $ten      = $gUser->name ?? '';
        $googleId = $gUser->id ?? null;
        $avatar   = $gUser->picture ?? 'user.jpg';

        if (!$email) {
            throw new Exception("Google không trả về email, không thể tạo tài khoản!");
        }

        // 1. Tìm user theo email
        $user = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);

        if ($user) {
            // Nếu user tồn tại nhưng chưa gắn provider -> cập nhật
            if (empty($user['provider']) || $user['provider'] === 'local') {
                $db->execute(
                    "UPDATE nguoidung 
                 SET provider = ?, provider_id = ?, hinh = ?
                 WHERE id = ?",
                    ['google', $googleId, $avatar, $user['id']]
                );
                $user = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$user['id']]);
            }

            // (Tùy chọn) chặn tài khoản bị khóa
            if (isset($user['trangThai']) && (int)$user['trangThai'] === 0) {
                $_SESSION["thongBao"] = "Tài khoản của bạn đã bị khóa.";
                header("Location: dangnhap.php");
                exit();
            }
        } else {
            // 2. Chưa có user -> tạo user mới
            // Vì cột matKhau NOT NULL => cần 1 mật khẩu random
            $randomPassword = bin2hex(random_bytes(16));
            $matKhauHash    = password_hash($randomPassword, PASSWORD_DEFAULT);

            $db->execute(
                "INSERT INTO nguoidung (hinh, ten, email, matKhau, provider, provider_id) 
             VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $avatar,
                    $ten,
                    $email,
                    $matKhauHash,
                    'google',
                    $googleId,
                ]
            );

            $user = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
        }

        // 3. Đăng nhập
        session_regenerate_id(true);
        $_SESSION["nguoiDung"] = $user;

        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        // Log nếu cần
        echo "Đăng nhập Google thất bại: " . $e->getMessage();
        exit;
    }
    ?>