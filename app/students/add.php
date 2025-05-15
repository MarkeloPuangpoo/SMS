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

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $citizen_id = $_POST['citizen_id'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];  

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($citizen_id) || empty($phone) || empty($gender) || empty($address)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header('Location: add.php');
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, citizen_id, phone, gender, address) 
                               VALUES (:student_id, :first_name, :last_name, :citizen_id, :phone, :gender, :address)");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':citizen_id', $citizen_id);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);

        $stmt->execute();
        $_SESSION['success'] = "เพิ่มข้อมูลนักเรียนสำเร็จ";
        header('Location: list.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . $e->getMessage();
        header('Location: add.php');
        exit();
    }
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">เพิ่มนักเรียนใหม่</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="list.php">รายชื่อนักเรียน</a></li>
                    <li class="breadcrumb-item active" aria-current="page">เพิ่มนักเรียนใหม่</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form method="POST" id="studentForm" class="needs-validation" novalidate>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="รหัสนักเรียน" required pattern="[0-9]{5}">
                            <label for="student_id">รหัสนักเรียน</label>
                            <div class="invalid-feedback">
                                กรุณากรอกรหัสนักเรียน 5 หลัก
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="citizen_id" name="citizen_id" placeholder="เลขบัตรประชาชน" required pattern="[0-9]{13}">
                            <label for="citizen_id">เลขบัตรประชาชน</label>
                            <div class="invalid-feedback">
                                กรุณากรอกเลขบัตรประชาชน 13 หลัก
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="ชื่อ" required>
                            <label for="first_name">ชื่อ</label>
                            <div class="invalid-feedback">
                                กรุณากรอกชื่อ
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="นามสกุล" required>
                            <label for="last_name">นามสกุล</label>
                            <div class="invalid-feedback">
                                กรุณากรอกนามสกุล
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="เบอร์โทรศัพท์" required pattern="[0-9]{10}">
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <div class="invalid-feedback">
                                กรุณากรอกเบอร์โทรศัพท์ 10 หลัก
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="" selected disabled>เลือกเพศ</option>
                                <option value="male">ชาย</option>
                                <option value="female">หญิง</option>
                            </select>
                            <label for="gender">เพศ</label>
                            <div class="invalid-feedback">
                                กรุณาเลือกเพศ
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="address" name="address" placeholder="ที่อยู่" style="height: 100px" required></textarea>
                            <label for="address">ที่อยู่</label>
                            <div class="invalid-feedback">
                                กรุณากรอกที่อยู่
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>บันทึกข้อมูล
                            </button>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>ยกเลิก
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.form-floating > label {
    padding-left: 20px;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 1rem;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}

.alert {
    border: none;
    border-radius: 10px;
}

.breadcrumb {
    margin-bottom: 0;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.was-validated .form-control:invalid,
.was-validated .form-select:invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.was-validated .form-control:valid,
.was-validated .form-select:valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('#studentForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

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

    // Format phone number while typing
    const phoneInput = document.querySelector('#phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        e.target.value = value;
    });

    // Format citizen ID while typing
    const citizenInput = document.querySelector('#citizen_id');
    citizenInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 13) value = value.slice(0, 13);
        e.target.value = value;
    });

    // Format student ID while typing
    const studentInput = document.querySelector('#student_id');
    studentInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) value = value.slice(0, 5);
        e.target.value = value;
    });
});
</script>

</body>
</html>

<?php ob_end_flush(); ?>
