<?php
require_once "../core/db_utils.php";

$idNguoiDung = $_SESSION["nguoiDung"]["id"];

$dir = "../uploads/";
$dirMenu = "../";

$cart = $db->getAll("SELECT a.id,a.idSize,a.gia,a.soLuong,a.idNguoiDung,b.size,b.soLuong AS soLuongTK,c.hinh,c.mau,c.idSanPham,d.ten,e.ten AS tenDM FROM giohang a join bienthesize b ON a.idSize = b.id JOIN mau c ON b.idMau = c.id JOIN sanpham d ON c.idSanPham = d.id JOIN danhmuc e ON d.idDanhMuc = e.id WHERE a.idNguoiDung = ?", [$idNguoiDung]);

// Xử lý xóa sản phẩm
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["deleteCart"])) {
    $id = $_POST["id"];

    $db->execute("DELETE FROM giohang WHERE id = ?", [$id]);

    $_SESSION["mess"] = "Xóa thành công";
    header("location: giohang.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
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
    <div class="container">
        <!-- Cart Section -->
        <section class="mb-5">
            <h2 class="mb-4" style="font-family: 'Playfair Display', serif;">Giỏ hàng</h2>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="table-responsive">

                        <?php if (empty($cart)) { ?>
                            <div id="cart-empty" class="text-center text-muted py-4">
                                Chưa có sản phẩm nào
                            </div>
                        <?php } else { ?>
                            <table class="table cart-table align-middle bg-white rounded shadow-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 72px;">Hình</th>
                                        <th>Sản phẩm</th>
                                        <th>Màu</th>
                                        <th>Size</th>
                                        <th style="width:110px;">Số lượng</th>
                                        <th>Giá</th>
                                        <th>Tổng</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-body">
                                    <?php foreach ($cart as $item) { ?>
                                        <tr data-id="1">
                                            <td>
                                                <img src="<?= $dir . $item["hinh"] ?>" alt="<?= $item["ten"] ?>" class="cart-product-img" />

                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= $item["ten"] ?></div>
                                                <div class="text-muted small"><?= $item["tenDM"] ?></div>
                                            </td>

                                            <td><?= $item["mau"] ?></td>
                                            <td><?= $item["size"] ?></td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="number"
                                                        class="form-control form-control-sm cart-qty"
                                                        min="1"
                                                        max="<?= $item["soLuongTK"] ?>"
                                                        value="<?= $item["soLuong"] ?>"
                                                        style="width: 50px;" />
                                                    <small class="text-muted">(<?= ($item["soLuongTK"]) ?>)</small>
                                                </div>
                                            </td>
                                            <td><?= number_format($item["gia"]) ?>₫</td>
                                            <td class="cart-item-total"><?= number_format($item["soLuong"] * $item["gia"]) ?>₫</td>
                                            <td>
                                                <form action="" method="post">
                                                    <input type="hidden" name="id" value="<?= $item["id"] ?>" id="">
                                                    <button type="submit" name="deleteCart" class="btn btn-link text-danger cart-remove px-1" title="Remove">
                                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path d="M6 6l12 12M6 18L18 6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                <?php
                $tong = $db->getValue("SELECT SUM(gia*soLuong) FROM gioHang WHERE idNguoiDung = ?", [$id]);
                if ($tong == null) {
                    $tong = 0;
                }
                ?>
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5 class="mb-3">Đơn hàng</h5>
                        <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2">
                            <span>Tổng cộng</span>
                            <span id="cart-total"><?= number_format($tong) ?>₫</span>
                        </div>
                        <a href="dathang.php" class="btn btn-primary w-100 mt-3" id="checkout-btn">Đặt hàng</a>
                    </div>
                </div>

            </div>
        </section>
    </div>
    <?php require_once "../include/footer.php"; ?>
</body>

</html>