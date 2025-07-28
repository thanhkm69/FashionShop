<?php
require_once "./core/db_utils.php"; // hoặc db.php nếu có

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["idMau"])) {
    $idMau = $_POST["idMau"];

    // Kiểm tra $idMau là số nguyên để tránh lỗi SQL
    if (!is_numeric($idMau)) {
        http_response_code(400);
        echo json_encode(["error" => "ID màu không hợp lệ"]);
        exit;
    }

    // Lấy danh sách size
    try {
        $sizes = $db->getAll("SELECT * FROM bienthesize WHERE idMau = ?", [$idMau]);
        echo json_encode($sizes);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Lỗi truy vấn CSDL"]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Yêu cầu không hợp lệ"]);
    exit;
}
