<?php
ob_start();
session_start();
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    redirect('../login.php');
}

require_once '../config/database.php';

// ดึงข้อมูลนักเรียนจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM students ORDER BY student_id ASC");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// นับจำนวนนักเรียนแยกตามเพศ
$maleCount = 0;
$femaleCount = 0;
$statusCounts = [
    'กำลังศึกษา' => 0,
    'จบการศึกษา' => 0,
    'ลาออก' => 0,
    'พักการเรียน' => 0
];

foreach ($students as $student) {
    if ($student['gender'] === 'ชาย') {
        $maleCount++;
    } else {
        $femaleCount++;
    }
    // นับจำนวนตามสถานะ
    $statusCounts[$student['status']]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อนักเรียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">        <div>
            <h2 class="mb-1">รายชื่อนักเรียน</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายชื่อนักเรียน</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>เพิ่มนักเรียนใหม่
            </a>
            <a href="import.php" class="btn btn-success">
                <i class="fas fa-file-import me-2"></i>นำเข้าจาก Excel
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">นักเรียนทั้งหมด</h6>
                        <h2 class="mb-0"><?php echo count($students); ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">นักเรียนชาย</h6>
                        <h2 class="mb-0"><?php echo $maleCount; ?></h2>
                    </div>
                    <i class="fas fa-male fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">นักเรียนหญิง</h6>
                        <h2 class="mb-0"><?php echo $femaleCount; ?></h2>
                    </div>
                    <i class="fas fa-female fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="ค้นหานักเรียน...">
                </div>
                <div class="col-md-2">
                    <select id="genderFilter" class="form-select">
                        <option value="">เพศทั้งหมด</option>
                        <option value="ชาย">ชาย</option>
                        <option value="หญิง">หญิง</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="">สถานะทั้งหมด</option>
                        <option value="กำลังศึกษา">กำลังศึกษา</option>
                        <option value="จบการศึกษา">จบการศึกษา</option>
                        <option value="ลาออก">ลาออก</option>
                        <option value="พักการเรียน">พักการเรียน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="classFilter" class="form-select">
                        <option value="">ชั้นเรียนทั้งหมด</option>
                        <option value="ม.1">ม.1</option>
                        <option value="ม.2">ม.2</option>
                        <option value="ม.3">ม.3</option>
                        <option value="ม.4">ม.4</option>
                        <option value="ม.5">ม.5</option>
                        <option value="ม.6">ม.6</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Students Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead class="table-light">
                        <tr>                            
                            <th>รหัสนักเรียน</th>
                            <th>คำนำหน้า</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>เพศ</th>
                            <th>ชั้น/ห้อง</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">ไม่พบข้อมูลนักเรียน</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['title_prefix']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['gender'] === 'ชาย' ? 'bg-primary' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($student['gender']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($student['class_level']) {
                                            echo htmlspecialchars($student['class_level']);
                                            if ($student['class_room']) {
                                                echo '/' . htmlspecialchars($student['class_room']);
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                        switch($student['status']) {
                                            case 'กำลังศึกษา': echo 'bg-success'; break;
                                            case 'จบการศึกษา': echo 'bg-primary'; break;
                                            case 'ลาออก': echo 'bg-danger'; break;
                                            case 'พักการเรียน': echo 'bg-warning'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                            <?php echo htmlspecialchars($student['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?php echo $student['id']; ?>" 
                                               class="btn btn-warning btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $student['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบนักเรียนคนนี้?')"
                                               data-bs-toggle="tooltip" 
                                               title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.table > :not(caption) > * > * {
    padding: 1rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.badge {
    padding: 0.5em 0.8em;
}

.alert {
    border: none;
    border-radius: 10px;
}

.breadcrumb {
    margin-bottom: 0;
}

#searchInput, #genderFilter {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 1rem;
}

#searchInput:focus, #genderFilter:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality    const searchInput = document.getElementById('searchInput');
    const genderFilter = document.getElementById('genderFilter');
    const statusFilter = document.getElementById('statusFilter');
    const classFilter = document.getElementById('classFilter');
    const table = document.getElementById('studentsTable');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const genderValue = genderFilter.value;
        const statusValue = statusFilter.value;
        const classValue = classFilter.value;

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            if (cells.length > 0) {
                const text = row.textContent.toLowerCase();
                const genderCell = cells[3].textContent.trim();
                const statusCell = cells[5].textContent.trim();
                const classCell = cells[4].textContent.trim();
                
                const matchesSearch = text.toLowerCase().includes(searchText);
                const matchesGender = !genderValue || genderCell === genderValue;
                const matchesStatus = !statusValue || statusCell === statusValue;
                const matchesClass = !classValue || classCell.startsWith(classValue);
                
                row.style.display = (matchesSearch && matchesGender && matchesStatus && matchesClass) ? '' : 'none';
            }
        }
    }    searchInput.addEventListener('keyup', filterTable);
    genderFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    classFilter.addEventListener('change', filterTable);

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
