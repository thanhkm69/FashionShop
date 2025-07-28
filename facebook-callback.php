<?php
ob_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
require_once "./core/db_utils.php";

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

const FB_APP_ID = "4142587962620027";
const FB_APP_SECRET = "af93ed3bfb5c123de591504ea0e24743";
const FB_REDIRECT_URI = "http://localhost/FashionShop/facebook-callback.php";
const SOCIAL_PROVIDER_FACEBOOK = 'facebook';

if (isset($_SESSION["nguoiDung"])) {
    header("location: index.php");
    exit();
}

$fb = new Facebook([
    'app_id'                => FB_APP_ID,
    'app_secret'            => FB_APP_SECRET,
    'default_graph_version' => 'v19.0',
]);

$helper = $fb->getRedirectLoginHelper();

if (isset($_GET['state'])) {
    $helper->getPersistentDataHandler()->set('state', $_GET['state']);
}

try {
    $accessToken = $helper->getAccessToken(FB_REDIRECT_URI);
} catch (FacebookResponseException $e) {
    die('Graph returned an error: ' . $e->getMessage());
} catch (FacebookSDKException $e) {
    die('Facebook SDK returned an error: ' . $e->getMessage());
}

if (!isset($accessToken)) {
    die('Không thể đăng nhập bằng Facebook! (missing access token)');
}

$oAuth2Client = $fb->getOAuth2Client();
if (!$accessToken->isLongLived()) {
    try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
    } catch (FacebookSDKException $e) { /* ignore */ }
}

$fb->setDefaultAccessToken($accessToken);

try {
    $response = $fb->get('/me?fields=id,name,email,picture.type(large)');
    $fbUser   = $response->getGraphUser();

    $facebookId = $fbUser->getId();
    $ten        = $fbUser->getName();
    $email      = $fbUser->getEmail() ?: "fb_{$facebookId}@no-email.local";
    $avatar     = $fbUser->getPicture()->getUrl() ?? 'user.jpg';

    $user = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);

    if ($user) {
        if (empty($user['provider']) || $user['provider'] === 'local') {
            $db->execute(
                "UPDATE nguoidung 
                 SET provider = ?, provider_id = ?, hinh = ?
                 WHERE id = ?",
                [SOCIAL_PROVIDER_FACEBOOK, $facebookId, $avatar, $user['id']]
            );
            $user = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$user['id']]);
        }

        if (isset($user['trangThai']) && (int)$user['trangThai'] === 0) {
            $_SESSION["thongBao"] = "Tài khoản của bạn đã bị khóa.";
            header("Location: dangnhap.php");
            exit();
        }
    } else {
        $randomPassword = bin2hex(random_bytes(16));
        $matKhauHash    = password_hash($randomPassword, PASSWORD_DEFAULT);

        $db->execute(
            "INSERT INTO nguoidung (hinh, ten, email, matKhau, provider, provider_id) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$avatar, $ten, $email, $matKhauHash, SOCIAL_PROVIDER_FACEBOOK, $facebookId]
        );

        $user = $db->getOne("SELECT * FROM nguoidung WHERE email = ?", [$email]);
    }

    session_regenerate_id(true);
    $_SESSION["nguoiDung"] = $user;

    header("Location: index.php");
    exit;

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
