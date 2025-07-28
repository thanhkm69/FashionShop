<?php
require_once "./core/db_utils.php";


use Facebook\Facebook;

// Nếu đã đăng nhập thì thôi
if (isset($_SESSION["nguoiDung"])) {
    header("location: index.php");
    exit();
}

const FB_APP_ID = "4142587962620027";
const FB_APP_SECRET = "af93ed3bfb5c123de591504ea0e24743";
const FB_REDIRECT_URI = "http://localhost/FashionShop/facebook-callback.php";
$fb = new Facebook([
    'app_id'                => FB_APP_ID,
    'app_secret'            => FB_APP_SECRET,
    'default_graph_version' => 'v19.0', // hoặc phiên bản mới nhất
]);

$helper = $fb->getRedirectLoginHelper();

// Chống CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['FBOAUTH2STATE'] = $state;
$helper->getPersistentDataHandler()->set('state', $state);

// Quyền muốn lấy (email là quan trọng)
$permissions = ['email'];

$loginUrl = $helper->getLoginUrl(FB_REDIRECT_URI, $permissions);

// Chuyển hướng
header('Location: ' . $loginUrl);
exit;
?>