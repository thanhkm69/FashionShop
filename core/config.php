<?php
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set("Asia/Ho_Chi_Minh");

const HOST = 'localhost';
const USERNAME = 'root';
const DBNAME = 'fashionshop';
const PASSWORD = '';

define('USERNAME_EMAIL', 'thanhnvpk04053@gmail.com'); 
define('PASSWORD_EMAIL', 'yqop rqvu iyfn bzlt');

// Cấu hình kết nối VNPAY (môi trường test sandbox)
$vnp_TmnCode = "3M0ROAN3"; // Mã website
$vnp_HashSecret = "VL1NH6URONLUKWBYSP7UTY4XVENFOOK6"; // Chuỗi bí mật
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán VNPAY
$vnp_Returnurl = "https://asmfinal.ct.ws/vnpay/vnpay_return.php";
//Cấu hình giao hàng nhanh
define('GHN_TOKEN', 'aca8eb35-5c9d-11f0-9ca3-d63c9cb9975d');
define('GHN_SHOP_ID', 5881673);
?>