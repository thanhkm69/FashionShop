<?php
require_once "./core/db_utils.php";
$dir = "./uploads/";
$dirMenu = "./";
if (isset($_SESSION["nguoiDung"])) {
  header("location: index.php");
  exit();
}

// ====== THAY BẰNG CLIENT ID / SECRET CỦA BẠN ======
const GG_CLIENT_ID     = '1095508817214-km1c4hdeh9eau39dnljur20q7659e942.apps.googleusercontent.com';
const GG_CLIENT_SECRET = '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';
const GG_REDIRECT_URI  = 'http://localhost/FashionShop/google-callback.php';
// ===================================================

$client = new Google_Client();
$client->setClientId(GG_CLIENT_ID);
$client->setClientSecret(GG_CLIENT_SECRET);
$client->setRedirectUri(GG_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// (Tùy chọn) chống CSRF bằng state
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;
$client->setState($state);

// Chuyển hướng sang Google
header('Location: ' . $client->createAuthUrl());
exit;
?>

