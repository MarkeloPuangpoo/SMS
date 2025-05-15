<?php
ob_start();
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    redirect('../login.php');
}

// ตรวจสอบว่ามี id ถูกส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ไม่พบข้อมูลนักเรียน";
    redirect('list.php');
}

$id = $_GET['id'];

// ดึงข้อมูลนักเรียน
try {
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error'] = "ไม่พบข้อมูลนักเรียน";
        redirect('list.php');
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    redirect('list.php');
}

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Sanitize and validate inputs
        $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
        $title_prefix = filter_input(INPUT_POST, 'title_prefix', FILTER_SANITIZE_STRING);
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);
        $citizen_id = filter_input(INPUT_POST, 'citizen_id', FILTER_SANITIZE_NUMBER_INT);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $class_level = filter_input(INPUT_POST, 'class_level', FILTER_SANITIZE_STRING);
        $class_room = filter_input(INPUT_POST, 'class_room', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        // Validate required fields
        if (!$student_id || strlen($student_id) !== 5) {
            throw new Exception("รหัสนักเรียนต้องเป็นตัวเลข 5 หลัก");
        }

        if (!$citizen_id || strlen($citizen_id) !== 13) {
            throw new Exception("เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก");
        }

        if ($phone && strlen($phone) !== 10) {
            throw new Exception("เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก");
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("รูปแบบอีเมลไม่ถูกต้อง");
        }

        if (!$first_name || !$last_name || !$title_prefix || !$gender || !$address || !$status) {
            throw new Exception("กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน");
        }

        // ตรวจสอบว่ารหัสนักเรียนซ้ำหรือไม่
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ? AND id != ?");
        $stmt->execute([$student_id, $id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("รหัสนักเรียนนี้มีอยู่ในระบบแล้ว");
        }

        // ตรวจสอบว่าเลขประจำตัวประชาชนซ้ำหรือไม่
        $stmt = $conn->prepare("SELECT id FROM students WHERE citizen_id = ? AND id != ?");
        $stmt->execute([$citizen_id, $id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว");
        }

        // อัพเดทข้อมูล
        $sql = "UPDATE students SET 
                student_id = ?,
                title_prefix = ?,
                first_name = ?,
                last_name = ?,
                gender = ?,
                birth_date = ?,
                citizen_id = ?,
                address = ?,
                phone = ?,
                email = ?,
                class_level = ?,
                class_room = ?,
                status = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $student_id,
            $title_prefix,
            $first_name,
            $last_name,
            $gender,
            $birth_date,
            $citizen_id,
            $address,
            $phone,
            $email,
            $class_level,
            $class_room,
            $status,
            $id
        ]);

        $_SESSION['success'] = "แก้ไขข้อมูลนักเรียนเรียบร้อยแล้ว";
        redirect('list.php');

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">แก้ไขข้อมูลนักเรียน</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="list.php">รายชื่อนักเรียน</a></li>
                    <li class="breadcrumb-item active" aria-current="page">แก้ไขข้อมูลนักเรียน</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>    <?php endif; ?>    
    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="edit.php?id=<?php echo $id; ?>" id="studentForm" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="student_id" class="form-label">รหัสนักเรียน*</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required maxlength="5" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="title_prefix" class="form-label">คำนำหน้า*</label>
                        <select class="form-select" id="title_prefix" name="title_prefix" required>
                            <option value="">เลือกคำนำหน้า</option>
                            <?php
                            $prefixes = ['เด็กชาย', 'เด็กหญิง', 'นาย', 'นางสาว', 'นาง'];
                            foreach ($prefixes as $prefix) {
                                $selected = ($student['title_prefix'] == $prefix) ? 'selected' : '';
                                echo "<option value=\"$prefix\" $selected>$prefix</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="first_name" class="form-label">ชื่อ*</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($student['first_name']); ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="last_name" class="form-label">นามสกุล*</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($student['last_name']); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="gender" class="form-label">เพศ*</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">เลือกเพศ</option>
                            <option value="ชาย" <?php echo ($student['gender'] == 'ชาย') ? 'selected' : ''; ?>>ชาย</option>
                            <option value="หญิง" <?php echo ($student['gender'] == 'หญิง') ? 'selected' : ''; ?>>หญิง</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="birth_date" class="form-label">วันเกิด</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo $student['birth_date']; ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="citizen_id" class="form-label">เลขบัตรประชาชน*</label>
                        <input type="text" class="form-control" id="citizen_id" name="citizen_id" required maxlength="13" value="<?php echo htmlspecialchars($student['citizen_id']); ?>">
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">ที่อยู่*</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>

                    <div class="col-md-3">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" maxlength="10" value="<?php echo htmlspecialchars($student['phone']); ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="class_level" class="form-label">ระดับชั้น</label>
                        <select class="form-select" id="class_level" name="class_level">
                            <option value="">เลือกระดับชั้น</option>
                            <?php
                            $levels = ['ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
                            foreach ($levels as $level) {
                                $selected = ($student['class_level'] == $level) ? 'selected' : '';
                                echo "<option value=\"$level\" $selected>$level</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="class_room" class="form-label">ห้อง</label>
                        <select class="form-select" id="class_room" name="class_room">
                            <option value="">เลือกห้อง</option>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($student['class_room'] == $i) ? 'selected' : '';
                                echo "<option value=\"$i\" $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">สถานะ*</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php
                            $statuses = ['กำลังศึกษา', 'จบการศึกษา', 'ลาออก', 'พักการเรียน'];
                            foreach ($statuses as $status) {
                                $selected = ($student['status'] == $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>$status</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    <a href="list.php" class="btn btn-secondary ms-2">ยกเลิก</a>
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

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
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
        const form = document.querySelector('form');
        
        // Validate citizen ID
        const citizenIdInput = document.getElementById('citizen_id');
        citizenIdInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });

        // Validate phone number
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });

        // Validate student ID
        const studentIdInput = document.getElementById('student_id');
        studentIdInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 5) {
                this.value = this.value.slice(0, 5);
            }
        });

        form.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';

            // Validate student ID
            if (!/^\d{5}$/.test(studentIdInput.value)) {
                errorMessage += 'รหัสนักเรียนต้องเป็นตัวเลข 5 หลัก\\n';
                isValid = false;
            }

            // Validate citizen ID
            if (!/^\d{13}$/.test(citizenIdInput.value)) {
                errorMessage += 'เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก\\n';
                isValid = false;
            }

            // Validate phone if provided
            if (phoneInput.value && !/^\d{10}$/.test(phoneInput.value)) {
                errorMessage += 'เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก\\n';
                isValid = false;
            }

            // Validate email if provided
            const emailInput = document.getElementById('email');
            if (emailInput.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                errorMessage += 'รูปแบบอีเมลไม่ถูกต้อง\\n';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    });
</script>

<?php 
require_once '../includes/footer.php';
ob_end_flush();
?>
