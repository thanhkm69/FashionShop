<?php
require_once __DIR__ . '/database.php'; 
require_once __DIR__ . "/mailservice.php";

$err = [];

if (!isset($_SESSION["thongBao"])) {
    $_SESSION["thongBao"] = "";
}

class DB_UTILS
{
    public $connection;
    public function __construct()
    {
        $db = new Database();
        $this->connection = $db->connection();
    }

    function getAll($sql, $params = [])
    {
        $conn = $this->connection;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOne($sql, $params = [])
    {
        $conn = $this->connection;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function execute($sql, $params = [])
    {
        $conn = $this->connection;
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    }

    function getValue($sql, $params = [])
    {
        $conn = $this->connection;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        $stmt = null;
        return $value;
    }

    function getLastInsertId(){
        return $this->connection->lastInsertId();
    }
}

function addCartLogin($userID)
{
    if (!isset($_SESSION["cart"])) {
        return;
    }

    $db = new DB_UTILS();

    foreach ($_SESSION["cart"] as $item) {
        $productID = $item["productID"];
        $cartQtyFromSession = (int)$item["quantityCart"];

        // Lấy số lượng tồn kho từ bảng product
        $product = $db->getOne("SELECT quantityProduct FROM product WHERE productID = ?", [$productID]);
        if (!$product) continue;

        $stockQty = (int)$product["quantityProduct"];

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng của user hay chưa
        $cartItem = $db->getOne("SELECT * FROM cart WHERE productID = ? AND userID = ?", [$productID, $userID]);

        if (!$cartItem) {
            $insertQty = min($cartQtyFromSession, $stockQty);
            if ($insertQty > 0) {
                $db->execute(
                    "INSERT INTO cart(userID, productID, quantityCart) VALUES (?, ?, ?)",
                    [$userID, $productID, $insertQty]
                );
            }
        } else {
            $totalQty = min($cartItem["quantityCart"] + $cartQtyFromSession, $stockQty);
            if ($totalQty > $cartItem["quantityCart"]) {
                $db->execute(
                    "UPDATE cart SET quantityCart = ? WHERE cartID = ?",
                    [$totalQty, $cartItem["cartID"]]
                );
            }
        }
    }

    // Xóa session giỏ hàng sau khi đồng bộ
    unset($_SESSION["cart"]);
}



function addCart($id, $userID, $quantity_defaut = 1)
{
    $db = new DB_UTILS();
    $product = $db->getOne(
        "
        SELECT a.*, b.nameCategories
        FROM product a 
        JOIN categories b ON a.categoriesID = b.categoriesID
        WHERE productID = ?",
        [$id]
    );

    if (!$product || $product["quantityProduct"] == 0) {
        $_SESSION["mess"] = "Sản phẩm không tồn tại hoặc đã hết hàng";
        return;
    }


    $addQuantity = max(1, (int)$quantity_defaut);


    if (empty($userID)) {
        if (!isset($_SESSION["cart"])) {
            $_SESSION["cart"] = [];
        }


        $cartIndex = array_search($id, array_column($_SESSION["cart"], "productID"));

        if ($cartIndex === false) {

            $_SESSION["cart"][] = [
                "imgProduct" => $product["imgProduct"],
                "nameProduct" => $product["nameProduct"],
                "priceProduct" => $product["priceProduct"],
                "quantityProduct" => $product["quantityProduct"],
                "nameCategories" => $product["nameCategories"],
                "productID" => $id,
                "quantityCart" => $addQuantity
            ];
            $_SESSION["mess"] = "Thêm vào giỏ hàng thành công";
        } else {
            $currentQty = $_SESSION["cart"][$cartIndex]["quantityCart"];
            if ($currentQty + $addQuantity > $product["quantityProduct"]) {
                $_SESSION["mess"] = "Số lượng tồn kho không đủ";
                return;
            }
            $_SESSION["cart"][$cartIndex]["quantityCart"] += $addQuantity;
            $_SESSION["mess"] = "Cập nhật số lượng thành công";
        }
    } else {

        $existingCart = $db->getOne("SELECT * FROM cart WHERE productID = ? AND userID = ?", [$id, $userID]);

        if (!$existingCart) {
            $db->execute("INSERT INTO cart(userID, productID, quantityCart) VALUES (?, ?, ?)", [$userID, $id, $addQuantity]);
            $_SESSION["mess"] = "Thêm vào giỏ hàng thành công";
        } else {
            $newTotal = $existingCart["quantityCart"] + $addQuantity;
            if ($newTotal > $product["quantityProduct"]) {
                $_SESSION["mess"] = "Số lượng tồn kho không đủ";
                return;
            }
            $db->execute("UPDATE cart SET quantityCart = ? WHERE cartID = ?", [$newTotal, $existingCart["cartID"]]);
            $_SESSION["mess"] = "Cập nhật số lượng thành công";
        }
    }
}
require_once __DIR__ . "/function.php";
$db = new DB_UTILS();

?>