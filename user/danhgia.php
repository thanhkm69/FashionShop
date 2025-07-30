<?php
require_once "../core/db_utils.php";
if (!isset($_SESSION["nguoiDung"])) {
    header("location: ../index.php");
    exit;
} else {
    $idND = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$idND]);
}
$dir = "../uploads/";
$dirMenu = "../";

$id = $_GET["id"];

$ct = $db->getAll("SELECT a.*,b.size,c.mau,c.hinh,d.ten FROM chitiethoadon a JOIN bienthesize b ON a.idSize = b.id JOIN mau c ON b.idMau = c.id JOIN sanpham d ON c.idSanPham = d. id WHERE idHoaDon = ?", [$id]);

if (isset($_POST["guiDanhGia"])) {
    $idSize = $_POST["idSize"];
    $idHDCT = $_POST["idHDCT"];
    $noiDung = empty($_POST["noidung"]) ? "" : trim($_POST["noidung"]);
    $sao = (int)$_POST["sao"];

    // Kiểm tra đã tồn tại đánh giá chưa
    $daDanhGia = $db->getOne("SELECT * FROM binhluansp WHERE idNguoiDung = ? AND idHDCT = ?", [$idND, $idHDCT]);

    if ($daDanhGia) {
        $_SESSION["thongBao"] = "Bạn đã đánh giá sản phẩm này rồi.";
    } else if ($noiDung !== "" && $sao >= 1 && $sao <= 5) {
        $db->execute("INSERT INTO binhluansp (idNguoiDung, idSize ,idHDCT, noiDung, sao) VALUES (?, ?, ?, ?, ?)", [
            $idND,
            $idSize,
            $idHDCT,
            $noiDung,
            $sao
        ]);
        $_SESSION["thongBao"] = "Đã gửi đánh giá thành công!";
    } else {
        $_SESSION["thongBao"] = "Vui lòng nhập nội dung và chọn số sao hợp lệ.";
    }

    header("location: danhgia.php?id=" . $_GET["id"]);
    exit;
}


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đánh giá sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .star {
            cursor: pointer;
            color: #ccc;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .star.hovered,
        .star.selected {
            color: #f0ad4e;
            transform: scale(1.2);
        }

        .card textarea {
            resize: none;
        }

        .card h6 {
            font-weight: 600;
        }

        .rating-stars i {
            transition: transform 0.2s;
        }
    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include './sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../admin/include/header.php'; ?>
            <div class="container py-4">
                <h4><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá sản phẩm</h4>
                <p class="text-muted">Các sản phẩm bạn đã mua</p>
                <hr>

                <?php if (!empty($_SESSION["thongBao"])): ?>
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <?= $_SESSION["thongBao"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php $_SESSION["thongBao"] = ""; ?>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($ct as $index => $sp): ?>
                        <?php
                        $danhGia = $db->getOne("SELECT * FROM binhluansp WHERE idNguoiDung = ? AND idHDCT = ?", [$idND, $sp['id']]);
                        ?>
                        <div class="col">
                            <form method="post" action="" class="card shadow-sm border-0 rounded-4 h-100 p-3">
                                <div class="d-flex gap-3">
                                    <!-- Hình -->
                                    <div class="flex-shrink-0">
                                        <img src="<?= $dir . $sp["hinh"] ?>" class="img-fluid rounded border" style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>

                                    <!-- Nội dung -->
                                    <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?= $sp["ten"] ?></h6>
                                            <p class="text-muted small mb-2">
                                                Màu: <?= $sp["mau"] ?> |
                                                Size: <?= $sp["size"] ?> |
                                                Giá: <?= number_format($sp["gia"]) ?>đ
                                            </p>

                                            <?php if ($danhGia): ?>
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="small text-muted">Bạn đã đánh giá:</div>
                                                    <div class="text-secondary mb-1"><?= htmlspecialchars($danhGia["noiDung"]) ?></div>
                                                    <div class="d-flex gap-1 fs-5">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi <?= $i <= $danhGia["sao"] ? 'bi-star-fill text-warning' : 'bi-star text-muted' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Nội dung đánh giá:</label>
                                                    <textarea name="noidung" class="form-control rounded-3" rows="2" required></textarea>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small d-block">Đánh giá sao:</label>
                                                    <div class="rating-stars d-flex gap-1 fs-4" data-index="<?= $index ?>">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi bi-star star text-muted" data-value="<?= $i ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <input type="hidden" name="sao" value="0" required>
                                                </div>

                                                <input type="hidden" name="idHDCT" value="<?= $sp['id'] ?>">
                                                <input type="hidden" name="idSize" value="<?= $sp['idSize'] ?>">
                                                <button type="submit" name="guiDanhGia" class="btn btn-sm btn-danger rounded-pill px-3">
                                                    <i class="bi bi-send"></i> Gửi
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-4">
                    <a href="donhang.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

            <?php include '../admin/include/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll(".rating-stars").forEach(function(starGroup) {
            const stars = starGroup.querySelectorAll(".star");
            const hiddenInput = starGroup.nextElementSibling;

            stars.forEach(function(star, index) {
                star.addEventListener("click", function() {
                    stars.forEach((s, i) => {
                        s.classList.toggle("bi-star-fill", i <= index);
                        s.classList.toggle("bi-star", i > index);
                        s.classList.toggle("text-warning", i <= index);
                        s.classList.toggle("text-muted", i > index);
                    });
                    hiddenInput.value = index + 1;
                });
            });
        });
    </script>

</body>

</html>