<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    redirect('login.php');
}

// ดึงข้อมูลนักเรียนทั้งหมด
$stmt = $conn->query("SELECT COUNT(*) as total FROM students");
$totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// ดึงข้อมูลแยกตามเพศ (ใช้ค่าจากฐานข้อมูลที่เป็น 'ชาย'/'หญิง')
$stmt = $conn->query("SELECT gender, COUNT(*) as count FROM students GROUP BY gender");
$genderStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// จัดรูปแบบข้อมูลสำหรับ Chart.js
$labels = [];
$data = [];
foreach ($genderStats as $stat) {
    // ใช้ label ตรงจากฐานข้อมูล (ชาย/หญิง)
    $labels[] = $stat['gender'];
    $data[] = (int) $stat['count'];
}

// Debug: แสดงข้อมูลใน console
echo "
<script>
console.log('Gender Stats:', " . json_encode($genderStats) . ");
console.log('Labels:', " . json_encode($labels) . ");
console.log('Data:', " . json_encode($data) . ");
</script>
";
?>

<!-- เพิ่ม Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
    }

    .card-body {
        padding: 2rem;
    }

    .stat-icon {
        background: rgba(255, 255, 255, 0.2);
        padding: 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .welcome-section {
        background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 600;
        margin: 0;
    }

    .stat-label {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    /* เพิ่ม Style สำหรับ Modal */
    .stats-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .stats-modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 15px;
        width: 80%;
        max-width: 800px;
        position: relative;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .close-modal {
        position: absolute;
        right: 1.5rem;
        top: 1rem;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
    }

    .close-modal:hover {
        color: #000;
    }

    .chart-container {
        position: relative;
        height: 400px;
        margin: 1rem 0;
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0"><i class="fas fa-user-circle me-2"></i>ยินดีต้อนรับ</h1>
                <p class="mb-0 fs-4 mt-2">คุณ <?php echo $_SESSION['username']; ?></p>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-0"><?php echo date('l, d F Y'); ?></p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Total Students Card -->
        <div class="col-md-4">
            <div class="dashboard-card card text-white h-100"
                style="background: linear-gradient(135deg, #FF6B6B 0%, #FF2D2D 100%)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">นักเรียนทั้งหมด</p>
                            <h2 class="stat-value"><?php echo number_format($totalStudents); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <a href="students/list.php" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Student Management Card -->
        <div class="col-md-4">
            <div class="dashboard-card card text-white h-100"
                style="background: linear-gradient(135deg, #36D1DC 0%, #5B86E5 100%)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">จัดการนักเรียน</p>
                            <h2 class="stat-value">เมนูหลัก</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                    <a href="students/list.php" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="col-md-4">
            <div class="dashboard-card card text-white h-100"
                style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">การตั้งค่าระบบ</p>
                            <h2 class="stat-value">ตั้งค่า</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-cogs fa-2x"></i>
                        </div>
                    </div>
                    <a href="#" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h4 class="card-title mb-4"><i class="fas fa-bolt me-2"></i>เมนูด่วน</h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="students/add.php" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>เพิ่มนักเรียนใหม่
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="students/list.php" class="btn btn-info text-white w-100 py-3">
                                <i class="fas fa-search me-2"></i>ค้นหานักเรียน
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-success w-100 py-3">
                                <i class="fas fa-file-export me-2"></i>ส่งออกรายงาน
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button onclick="openStatsModal()" class="btn btn-warning text-white w-100 py-3">
                                <i class="fas fa-chart-pie me-2"></i>สถิตินักเรียน
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับแสดงสถิติ -->
<div id="statsModal" class="stats-modal">
    <div class="stats-modal-content">
        <span class="close-modal" onclick="closeStatsModal()">&times;</span>
        <h3 class="mb-4"><i class="fas fa-chart-pie me-2"></i>สถิตินักเรียนแยกตามเพศ</h3>
        <div class="chart-container">
            <canvas id="genderChart" width="400" height="400"></canvas>
        </div>
        <!-- Debug: แสดงข้อมูลดิบ -->
        <div class="mt-3">
            <small class="text-muted">
                จำนวนนักเรียนชาย: <?php echo $data[0] ?? 0; ?> คน<br>
                จำนวนนักเรียนหญิง: <?php echo $data[1] ?? 0; ?> คน
            </small>
        </div>
    </div>
</div>

<!-- เรียก Chart.js ก่อนโค้ด JavaScript อื่นๆ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // สคริปต์สำหรับ Modal
    function openStatsModal() {
        document.getElementById('statsModal').style.display = 'block';
        initChart();
    }

    function closeStatsModal() {
        document.getElementById('statsModal').style.display = 'none';
    }

    window.onclick = function (event) {
        var modal = document.getElementById('statsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function initChart() {
        const ctx = document.getElementById('genderChart');
        if (!ctx) {
            console.error('Canvas element not found');
            return;
        }

        // ถ้ามีกราฟเดิมอยู่ ให้ทำลายก่อน
        if (window.genderChart instanceof Chart) {
            window.genderChart.destroy();
        }

        // ข้อมูลสำหรับกราฟ
        const chartData = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',  // สีฟ้าสำหรับชาย
                    'rgba(255, 99, 132, 0.8)'   // สีชมพูสำหรับหญิง
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Debug: แสดงข้อมูลใน console
        console.log('Creating chart with data:', chartData);

        window.genderChart = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Sarabun'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'สัดส่วนนักเรียนชาย-หญิง',
                        font: {
                            size: 16,
                            family: 'Sarabun'
                        }
                    }
                }
            }
        });
    }
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>