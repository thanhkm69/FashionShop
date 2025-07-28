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

// Lấy dữ liệu thống kê tổng quan
$tongDT = $db->getValue("SELECT SUM(tongTien) FROM hoadon") ?? 0;
$tongND = $db->getValue("SELECT COUNT(*) FROM nguoidung WHERE phanQuyen = ?", ["User"]) ?? 0;
$tongDM = $db->getValue("SELECT COUNT(*) FROM danhmuc") ?? 0;
$tongSP = $db->getValue("SELECT COUNT(*) FROM sanpham") ?? 0;
$tongHD = $db->getValue("SELECT COUNT(*) FROM hoadon") ?? 0;

// Số lượng sản phẩm theo danh mục
$soLuongSP = $db->getAll("SELECT a.ten, COUNT(*) AS soSP 
                          FROM danhmuc a 
                          JOIN sanpham b ON a.id = b.idDanhMuc 
                          GROUP BY idDanhMuc");
$labelsSP = [];
$valuesSP = [];
foreach ($soLuongSP as $row) {
    $labelsSP[] = $row['ten'];
    $valuesSP[] = $row['soSP'];
}

// Lấy loại thống kê doanh thu
$loaiThongKe = $_GET["loaiThongKe"] ?? "ngay";
switch ($loaiThongKe) {
    case "nam":
        $doanhThu = $db->getAll("SELECT YEAR(thoiGianMua) AS label, SUM(tongTien) AS tongTien 
                                 FROM hoadon 
                                 GROUP BY label 
                                 ORDER BY label ASC");
        break;
    case "thang":
        $doanhThu = $db->getAll("SELECT MONTH(thoiGianMua) AS label, SUM(tongTien) AS tongTien 
                                 FROM hoadon 
                                 GROUP BY label 
                                 ORDER BY label ASC");
        break;
    default:
        $doanhThu = $db->getAll("SELECT DATE(thoiGianMua) AS label, SUM(tongTien) AS tongTien 
                                 FROM hoadon 
                                 GROUP BY label 
                                 ORDER BY label ASC");
        break;
}
$labels = [];
$values = [];
foreach ($doanhThu as $row) {
    $labels[] = $row["label"];
    $values[] = $row["tongTien"];
}

// Top khách hàng mua nhiều nhất
$khMua = $db->getAll("SELECT b.ten, COUNT(*) as soLanMua 
                      FROM hoadon a 
                      JOIN nguoidung b ON a.idNguoiDung = b.id 
                      GROUP BY a.idNguoiDung 
                      ORDER BY soLanMua DESC 
                      LIMIT 5");

$labelsKHMua = array_column($khMua, 'ten');
$valuesKHMua = array_column($khMua, 'soLanMua');


// 10 đơn hàng mới nhất
$hdMoi = $db->getAll("SELECT ma, ten, trangThaiDH, tongTien 
                      FROM hoadon 
                      ORDER BY thoiGianMua DESC 
                      LIMIT 5");

// Lấy số lượng từng trạng thái từ database
$tiLeTTDH = $db->getAll("
    SELECT trangThaiDH, COUNT(*) AS soLuong 
    FROM hoadon 
    GROUP BY trangThaiDH
");

// Mặc định 7 trạng thái với giá trị 0
$trangThaiList = [
    'Đang xác nhận' => 0,
    'Đã xác nhận' => 0,
    'Đang giao hàng' => 0,
    'Giao hàng thành công' => 0,
    'Trả hàng' => 0,
    'Hoàn thành' => 0,
    'Đã hủy' => 0
];

// Cập nhật số lượng theo kết quả query
foreach ($tiLeTTDH as $row) {
    $tt = $row['trangThaiDH'];
    if (isset($trangThaiList[$tt])) {
        $trangThaiList[$tt] = (int)$row['soLuong'];
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .badge.bg-primary {
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 12px;
        }

        .dashboard-card {
            border-radius: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease-in-out;
        }

        .dashboard-card:hover {
            transform: scale(1.02);
        }

        .card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        /* Căn chiều cao 2 cột bằng nhau */
        .row-eq-height {
            display: flex;
            flex-wrap: wrap;
        }

        .row-eq-height>[class*='col-'] {
            display: flex;
            flex-direction: column;
        }

        /* Bảng có viền và nét kẻ đẹp */
        .table-custom {
            border: 1px solid #dee2e6;
        }

        .table-custom th,
        .table-custom td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .table-custom thead {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        /* Icon trong tiêu đề */
        .card h5 i {
            margin-right: 6px;
            font-size: 1.1rem;
        }
    </style>
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
    <div class="d-flex flex-grow-1">
        <?php include '../include/sidebar.php'; ?>
        <div class="flex-grow-1 d-flex flex-column">
            <?php include '../include/header.php'; ?>

            <main class="container-fluid py-4">
                <div class="action-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 fs-4 text-dark"></i>
                        <span class="fs-5 fw-semibold align-text-bottom">Dashboard</span>
                    </div>
                </div>

                <!-- Thống kê tổng quan -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-icon text-primary"><i class="bi bi-cash"></i></div>
                            <h5>Doanh thu</h5>
                            <p class="fw-bold text-success"><?= number_format($tongDT, 0, ',', '.') ?> ₫</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-icon text-info"><i class="bi bi-people"></i></div>
                            <h5>Người dùng</h5>
                            <p class="fw-bold"><?= $tongND ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-icon text-warning"><i class="bi bi-grid"></i></div>
                            <h5>Danh mục</h5>
                            <p class="fw-bold"><?= $tongDM ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-icon text-danger"><i class="bi bi-box"></i></div>
                            <h5>Sản phẩm</h5>
                            <p class="fw-bold"><?= $tongSP ?></p>
                        </div>
                    </div>
                </div>
                <!-- Form chọn loại thống kê -->
                <form method="GET" onchange="this.submit()" class="mb-4 d-flex gap-3 align-items-center">
                    <label for="loaiThongKe" class="form-label m-0">
                        <i class="bi bi-funnel-fill text-info"></i> Theo:
                    </label>
                    <select name="loaiThongKe" id="loaiThongKe" class="form-select w-auto">
                        <option value="ngay" <?= ($loaiThongKe == 'ngay') ? 'selected' : '' ?>>Ngày</option>
                        <option value="thang" <?= ($loaiThongKe == 'thang') ? 'selected' : '' ?>>Tháng</option>
                        <option value="nam" <?= ($loaiThongKe == 'nam') ? 'selected' : '' ?>>Năm</option>
                    </select>
                </form>
                <div class="row g-4">
                    <!-- Cột bên trái: Doanh thu + Top khách hàng -->
                    <div class="col-lg-8 d-flex flex-column gap-4">
                        <div class="card p-3 flex-fill">
                            <h5 class="mb-3">
                                <i class="bi bi-cash-coin text-success"></i> Doanh thu
                            </h5>

                            <canvas id="doanhThuChart" style="height: 100%;"></canvas>
                        </div>
                        <div class="card p-3 flex-fill">
                            <h5 class="mb-3"><i class="bi bi-bar-chart-steps"></i> Top khách hàng mua nhiều nhất</h5>
                            <canvas id="topCustomerChart" style="height: 100%;"></canvas>
                        </div>
                    </div>

                    <!-- Cột bên phải: Sản phẩm theo danh mục -->
                    <div class="col-lg-4">
                        <div class="card p-3 h-100">
                            <h5 class="mb-3">
                                <i class="bi bi-diagram-3 text-warning"></i> Sản phẩm theo danh mục
                            </h5>
                            <canvas id="sanPhamChart" style="height: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1 row-eq-height">
                    <!-- Bảng Đơn Hàng -->
                    <div class="col-lg-6">
                        <div class="card p-3 shadow-sm h-100">
                            <h5 class="mb-3"><i class="bi bi-receipt text-primary"></i> Đơn hàng mới nhất</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle text-center table-custom">
                                    <thead class="table-light">
                                        <tr>
                                           <th><i class="bi bi-tag"></i> Mã</th>
                                            <th><i class="bi bi-person"></i> Khách hàng</th>
                                            <th><i class="bi bi-info-circle"></i> Trạng thái</th>
                                            <th><i class="bi bi-cash-coin"></i> Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hdMoi as $hd): ?>
                                            <tr>
                                                <td><?= $hd['ma'] ?></td>
                                                <td><?= $hd['ten'] ?></td>
                                                <td>
                                                    <?php if ($hd['trangThaiDH'] == 'Đang xác nhận'): ?>
                                                        <span class="badge text-white" style="background-color: #0d6efd">
                                                            <i class="bi bi-patch-question"></i> Đang xác nhận
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Đã xác nhận'): ?>
                                                        <span class="badge text-white" style="background-color: #6610f2">
                                                            <i class="bi bi-check2-circle"></i> Đã xác nhận
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Đang giao hàng'): ?>
                                                        <span class="badge text-white" style="background-color: #198754">
                                                            <i class="bi bi-truck"></i> Đang giao hàng
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Giao hàng thành công'): ?>
                                                        <span class="badge text-white" style="background-color: #20c997">
                                                            <i class="bi bi-bag-check"></i> Giao hàng thành công
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Trả hàng'): ?>
                                                        <span class="badge text-dark" style="background-color: #ffc107">
                                                            <i class="bi bi-arrow-return-left"></i> Trả hàng
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Hoàn thành'): ?>
                                                        <span class="badge text-white" style="background-color: #fd7e14">
                                                            <i class="bi bi-check-circle"></i> Hoàn thành
                                                        </span>
                                                    <?php elseif ($hd['trangThaiDH'] == 'Đã hủy'): ?>
                                                        <span class="badge text-white" style="background-color: #dc3545">
                                                            <i class="bi bi-x-circle"></i> Đã hủy
                                                        </span>
                                                    <?php endif; ?>


                                                </td>
                                                <td><?= number_format($hd['tongTien'], 0, ',', '.') ?> ₫</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ Tỷ lệ trạng thái -->
                    <div class="col-lg-6">
                        <div class="card p-3 shadow-sm h-100">
                            <h5 class="mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart text-success fs-4"></i>
                                <span>Tỷ lệ trạng thái đơn hàng</span>
                                <span class="badge bg-primary bg-gradient px-3 py-2 shadow-sm">
                                    <?= $tongHD ?> đơn
                                </span>
                            </h5>
                            <canvas id="tiLeBarChart" height="220"></canvas>
                        </div>
                    </div>
                </div>

            </main>

            <?php include '../include/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart Doanh thu
        const ctxDoanhThu = document.getElementById('doanhThuChart').getContext('2d');
        new Chart(ctxDoanhThu, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: <?= json_encode($values) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString("vi-VN") + " ₫";
                            }
                        }
                    }
                }
            }
        });

        // Chart Sản phẩm theo danh mục
        const ctxSP = document.getElementById('sanPhamChart').getContext('2d');
        new Chart(ctxSP, {
            type: 'pie',
            data: {
                labels: <?= json_encode($labelsSP) ?>,
                datasets: [{
                    data: <?= json_encode($valuesSP) ?>,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                }]
            },
            options: {
                responsive: true
            }
        });
        // Top khách hàng mua nhiều nhất
        const ctxTopCus = document.getElementById('topCustomerChart').getContext('2d');
        new Chart(ctxTopCus, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsKHMua, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Số lần mua',
                    data: <?= json_encode($valuesKHMua) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // cột ngang
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.parsed.x.toLocaleString('vi-VN') + ' lần mua';
                            }
                        }
                    }
                }
            }
        });

        const tiLeLabels = <?= json_encode(array_keys($trangThaiList)) ?>;
        const tiLeData = <?= json_encode(array_values($trangThaiList)) ?>;

        const ctx = document.getElementById('tiLeBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: tiLeLabels,
                datasets: [{
                    label: 'Số lượng đơn hàng',
                    data: tiLeData,
                    backgroundColor: [
                        '#0d6efd', // Đang xác nhận
                        '#6610f2', // Đã xác nhận
                        '#198754', // Đang giao hàng
                        '#20c997', // Giao hàng thành công
                        '#ffc107', // Trả hàng
                        '#fd7e14', // Hoàn thành
                        '#dc3545' // Đã hủy
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    </script>
</body>

</html>