<?php
require_once "../../core/db_utils.php";

if (!isset($_SESSION["nguoiDung"]) || $_SESSION["nguoiDung"]["phanQuyen"] != "Admin") {
    header("location: ../../index.php");
    exit;
} else {
    $id = $_SESSION["nguoiDung"]["id"];
    $nguoiDung = $db->getOne("SELECT * FROM nguoidung WHERE id = ?", [$id]);
}
$dir = "../../uploads/";
// 7 câu truy vấn
$tongDM = $db->getAll("SELECT e.ten,SUM(a.tongTien) as tong 
    FROM chitiethoadon a 
    JOIN bienthesize b on a.idSize = b.id 
    JOIN mau c ON b.idMau = c.id 
    JOIN sanpham d ON c.idSanPham = d.id 
    JOIN danhmuc e ON d.idDanhMuc = e.id 
    GROUP BY e.id ORDER BY SUM(a.tongTien) DESC");

$tongSP = $db->getAll("SELECT d.ten,SUM(a.tongTien) as tong 
    FROM chitiethoadon a 
    JOIN bienthesize b on a.idSize = b.id 
    JOIN mau c ON b.idMau = c.id 
    JOIN sanpham d ON c.idSanPham = d.id 
    GROUP BY d.id ORDER BY SUM(a.tongTien) DESC");

$spyt = $db->getAll("SELECT b.*,COUNT(*) as soLuongYT 
    FROM yeuthich a 
    JOIN sanpham b ON a.idSanPham = b.id 
    GROUP BY a.idSanPham 
    ORDER BY COUNT(*) DESC LIMIT 10");

$spm =  $db->getAll("SELECT c.*,COUNT(*) AS soLuongBan 
    FROM bienthesize a 
    JOIN mau b ON a.idMau = b.id 
    JOIN sanpham c ON b.idSanPham = c.id 
    GROUP BY c.id 
    ORDER BY ngayTao DESC LIMIT 10");

$snbc = $db->getAll("SELECT d.*,SUM(a.soLuong) as soLuongBC 
    FROM chitiethoadon a 
    JOIN bienthesize b ON a.idSize = b.id 
    JOIN mau c ON b.idMau = c.id 
    JOIN sanpham d ON c.idSanPham = d.id 
    GROUP BY d.id ORDER BY SUM(a.soLuong) DESC LIMIT 10");

$spnb = $db->getAll("SELECT c.*,SUM(soLuong) as soLuong 
    FROM bienthesize a 
    JOIN mau b ON a.idMau = b.id 
    JOIN sanpham c ON b.idSanPham = c.id 
    GROUP BY c.id 
    ORDER BY SUM(soLuong) DESC LIMIT 10");

$spfl = $db->getAll("SELECT *, (giaGoc - giaKhuyenMai) / giaGoc * 100 AS phantram 
    FROM sanpham 
    ORDER BY phantram DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thống kê</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>
            <main class="p-4 flex-grow-1">
                <div class="action-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam fs-4"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Thống kê</span>
                    </div>
                </div>
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1">Tổng DM</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2">Tổng SP</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3">Yêu thích</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4">SP mới</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab5">Bán chạy</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab6">Nổi bật</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab7">Giảm giá</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="tab1">
                        <canvas id="chartTongDM" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab2">
                        <canvas id="chartTongSP" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab3">
                        <canvas id="chartSPYT" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab4">
                        <canvas id="chartSPM" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab5">
                        <canvas id="chartSNBC" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab6">
                        <canvas id="chartSPNB" height="100"></canvas>
                    </div>
                    <div class="tab-pane fade" id="tab7">
                        <canvas id="chartSPFL" height="100"></canvas>
                    </div>
                </div>

            </main>
            <?php include '../include/footer.php'; ?>
        </div>
    </div>

    <script>
        const tongDM = <?php echo json_encode($tongDM, JSON_UNESCAPED_UNICODE); ?>;
        const tongSP = <?php echo json_encode($tongSP, JSON_UNESCAPED_UNICODE); ?>;
        const spyt = <?php echo json_encode($spyt, JSON_UNESCAPED_UNICODE); ?>;
        const spm = <?php echo json_encode($spm, JSON_UNESCAPED_UNICODE); ?>;
        const snbc = <?php echo json_encode($snbc, JSON_UNESCAPED_UNICODE); ?>;
        const spnb = <?php echo json_encode($spnb, JSON_UNESCAPED_UNICODE); ?>;
        const spfl = <?php echo json_encode($spfl, JSON_UNESCAPED_UNICODE); ?>;

        function createPieChart(ctx, labels, data, labelText) {
            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelText,
                        data: data,
                        backgroundColor: labels.map(() =>
                            `hsl(${Math.random() * 360}, 70%, 60%)`
                        ),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Tab1: Tổng DM
        createPieChart(
            document.getElementById('chartTongDM'),
            tongDM.map(i => i.ten),
            tongDM.map(i => i.tong),
            'Doanh thu theo danh mục'
        );

        // Tab2: Tổng SP
        createPieChart(
            document.getElementById('chartTongSP'),
            tongSP.map(i => i.ten),
            tongSP.map(i => i.tong),
            'Doanh thu theo sản phẩm'
        );

        // Tab3: Yêu thích
        createPieChart(
            document.getElementById('chartSPYT'),
            spyt.map(i => i.ten),
            spyt.map(i => i.soLuongYT),
            'Số lượt yêu thích'
        );

        // Tab4: SP mới
        createPieChart(
            document.getElementById('chartSPM'),
            spm.map(i => i.ten),
            spm.map(i => i.soLuongBan),
            'Số lượng bán (SP mới)'
        );

        // Tab5: Bán chạy
        createPieChart(
            document.getElementById('chartSNBC'),
            snbc.map(i => i.ten),
            snbc.map(i => i.soLuongBC),
            'Số lượng bán'
        );

        // Tab6: Nổi bật
        createPieChart(
            document.getElementById('chartSPNB'),
            spnb.map(i => i.ten),
            spnb.map(i => i.soLuong),
            'Số lượng biến thể'
        );

        // Tab7: Giảm giá
        createPieChart(
            document.getElementById('chartSPFL'),
            spfl.map(i => i.ten),
            spfl.map(i => i.phantram),
            '% giảm giá'
        );
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>