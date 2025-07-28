<?php
session_start();
unset($_SESSION["nguoiDung"]);
header("location: index.php");
exit();
?>