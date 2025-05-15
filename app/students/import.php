<?php
ob_start();
session_start();
require_once '../includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isLoggedIn()) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    redirect('../login.php');
}

require_once '../config/database.php';

// ฟังก์ชันตรวจสอบความถูกต้องของข้อมูล
function validateStudentData($data, $row_num) {
    $errors = [];
    
    // ตรวจสอบรหัสนักเรียน
    if (empty($data['student_id']) || !is_numeric($data['student_id']) || strlen($data['student_id']) != 5) {
        $errors[] = "แถวที่ {$row_num}: รหัสนักเรียนต้องเป็นตัวเลข 5 หลัก";
    }
    
    // ตรวจสอบคำนำหน้า
    $allowed_prefixes = ['นาย', 'นางสาว', 'เด็กชาย', 'เด็กหญิง'];
    if (empty($data['prefix']) || !in_array($data['prefix'], $allowed_prefixes)) {
        $errors[] = "แถวที่ {$row_num}: คำนำหน้าต้องเป็น 'นาย', 'นางสาว', 'เด็กชาย' หรือ 'เด็กหญิง' เท่านั้น";
    }
    
    // ตรวจสอบชื่อและนามสกุล
    if (empty($data['first_name']) || empty($data['last_name'])) {
        $errors[] = "แถวที่ {$row_num}: กรุณากรอกชื่อและนามสกุลให้ครบถ้วน";
    }
    
    // ตรวจสอบเพศ
    if (!in_array($data['gender'], ['ชาย', 'หญิง'])) {
        $errors[] = "แถวที่ {$row_num}: เพศต้องเป็น 'ชาย' หรือ 'หญิง' เท่านั้น";
    }
    
    // ตรวจสอบวันเกิด
    if (!empty($data['birthdate'])) {
        $date = DateTime::createFromFormat('Y-m-d', $data['birthdate']);
        if (!$date || $date->format('Y-m-d') !== $data['birthdate']) {
            $errors[] = "แถวที่ {$row_num}: รูปแบบวันเกิดไม่ถูกต้อง (ต้องเป็น YYYY-MM-DD)";
        }
    }
    
    // ตรวจสอบเลขบัตรประชาชน
    if (empty($data['citizen_id']) || strlen($data['citizen_id']) != 13 || !is_numeric($data['citizen_id'])) {
        $errors[] = "แถวที่ {$row_num}: เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก";
    }
    
    // ตรวจสอบที่อยู่
    if (empty($data['address'])) {
        $errors[] = "แถวที่ {$row_num}: กรุณากรอกที่อยู่";
    }
    
    // ตรวจสอบเบอร์โทรศัพท์
    if (!empty($data['phone']) && !preg_match('/^[0-9]{9,10}$/', $data['phone'])) {
        $errors[] = "แถวที่ {$row_num}: รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง";
    }
    
    // ตรวจสอบอีเมล
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "แถวที่ {$row_num}: รูปแบบอีเมลไม่ถูกต้อง";
    }

    // ตรวจสอบระดับชั้น
    if (!empty($data['grade_level']) && !preg_match('/^[มป]\.[1-6]$/', $data['grade_level'])) {
        $errors[] = "แถวที่ {$row_num}: ระดับชั้นต้องอยู่ในรูปแบบ ม.1-6 หรือ ป.1-6";
    }

    // ตรวจสอบห้อง
    if (!empty($data['class_room']) && !preg_match('/^[1-9]$/', $data['class_room'])) {
        $errors[] = "แถวที่ {$row_num}: ห้องเรียนต้องเป็นตัวเลข 1-9";
    }

    // ตรวจสอบสถานะ
    $allowed_statuses = ['กำลังศึกษา', 'จบการศึกษา', 'ลาออก'];
    if (!empty($data['status']) && !in_array($data['status'], $allowed_statuses)) {
        $errors[] = "แถวที่ {$row_num}: สถานะต้องเป็น 'กำลังศึกษา', 'จบการศึกษา' หรือ 'ลาออก' เท่านั้น";
    }
    
    return $errors;
}

$success = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $allowed = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    // ตรวจสอบประเภทไฟล์
    $mime_type = mime_content_type($file['tmp_name']);
    if (!in_array($mime_type, $allowed)) {
        $_SESSION['error'] = "กรุณาอัพโหลดไฟล์ Excel เท่านั้น (.xls หรือ .xlsx)";
        redirect('import.php');
    }

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // ข้าม header row
        array_shift($rows);
        
        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) continue; // ข้ามแถวว่าง
            
            $studentData = [
                'student_id' => trim($row[0]),
                'prefix' => trim($row[1]),
                'first_name' => trim($row[2]),
                'last_name' => trim($row[3]),
                'gender' => trim($row[4]),
                'birthdate' => trim($row[5]),
                'citizen_id' => trim($row[6]),
                'address' => trim($row[7]),
                'phone' => trim($row[8]),
                'email' => trim($row[9]),
                'grade_level' => trim($row[10]),
                'class_room' => trim($row[11]),
                'status' => trim($row[12] ?: 'กำลังศึกษา')
            ];
            
            // ตรวจสอบความถูกต้องของข้อมูล
            $validationErrors = validateStudentData($studentData, $index + 2);
            if (!empty($validationErrors)) {
                $errors = array_merge($errors, $validationErrors);
                continue;
            }

            try {
                $stmt = $conn->prepare("INSERT INTO students (student_id, prefix, first_name, last_name, gender, birthdate, 
                    citizen_id, address, phone, email, grade_level, class_room, status) 
                    VALUES (:student_id, :prefix, :first_name, :last_name, :gender, :birthdate, 
                    :citizen_id, :address, :phone, :email, :grade_level, :class_room, :status)");
                
                $birthdate = empty($studentData['birthdate']) ? null : $studentData['birthdate'];
                
                $stmt->execute([
                    ':student_id' => $studentData['student_id'],
                    ':prefix' => $studentData['prefix'],
                    ':first_name' => $studentData['first_name'],
                    ':last_name' => $studentData['last_name'],
                    ':gender' => $studentData['gender'],
                    ':birthdate' => $birthdate,
                    ':citizen_id' => $studentData['citizen_id'],
                    ':address' => $studentData['address'],
                    ':phone' => $studentData['phone'],
                    ':email' => $studentData['email'],
                    ':grade_level' => $studentData['grade_level'],
                    ':class_room' => $studentData['class_room'],
                    ':status' => $studentData['status']
                ]);
                
                $success++;
                
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $errors[] = "แถวที่ " . ($index + 2) . ": รหัสนักเรียน {$studentData['student_id']} มีอยู่ในระบบแล้ว";
                } else {
                    $errors[] = "แถวที่ " . ($index + 2) . ": " . $e->getMessage();
                }
            }
        }
        
        if ($success > 0) {
            $_SESSION['success'] = "นำเข้าข้อมูลสำเร็จ {$success} รายการ";
        }
        
        if (!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอ่านไฟล์: " . $e->getMessage();
    }
    
    redirect('import.php');
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title mb-4">
                        <i class="fas fa-file-import"></i> นำเข้าข้อมูลนักเรียน
                    </h1>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">พบข้อผิดพลาด:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success) && $success > 0): ?>
                        <div class="alert alert-success">
                            นำเข้าข้อมูลสำเร็จ <?php echo $success; ?> รายการ
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">อัพโหลดไฟล์</h5>
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="excel_file" class="form-label">เลือกไฟล์ Excel</label>
                                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                            <div class="form-text">รองรับไฟล์ .xlsx และ .xls เท่านั้น</div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="template/student_import_template.php" class="btn btn-outline-primary">
                                                <i class="fas fa-download"></i> ดาวน์โหลดเทมเพลต
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> อัพโหลดและนำเข้าข้อมูล
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">คำแนะนำ</h5>
                                    <ol>
                                        <li>ดาวน์โหลดไฟล์เทมเพลต Excel</li>
                                        <li>กรอกข้อมูลตามรูปแบบที่กำหนด:
                                            <ul>
                                                <li>รหัสนักเรียน: ตัวเลข 5 หลัก</li>
                                                <li>คำนำหน้า: นาย, นางสาว, เด็กชาย, เด็กหญิง</li>
                                                <li>ชื่อ และ นามสกุล</li>
                                                <li>เพศ: ชาย หรือ หญิง</li>
                                                <li>วันเกิด: YYYY-MM-DD</li>
                                                <li>เลขบัตรประชาชน: ตัวเลข 13 หลัก</li>
                                                <li>ที่อยู่</li>
                                                <li>เบอร์โทรศัพท์ (ถ้ามี)</li>
                                                <li>อีเมล (ถ้ามี)</li>
                                                <li>ระดับชั้น: ม.1-6 หรือ ป.1-6</li>
                                                <li>ห้อง: 1-9</li>
                                                <li>สถานะ: กำลังศึกษา, จบการศึกษา, หรือ ลาออก</li>
                                            </ul>
                                        </li>
                                        <li>บันทึกไฟล์ Excel</li>
                                        <li>อัพโหลดไฟล์และรอการประมวลผล</li>
                                    </ol>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> หมายเหตุ:
                                        <ul class="mb-0">
                                            <li>รหัสนักเรียนและเลขบัตรประชาชนต้องไม่ซ้ำกับที่มีอยู่ในระบบ</li>
                                            <li>หากไม่ระบุสถานะ จะกำหนดเป็น "กำลังศึกษา" โดยอัตโนมัติ</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php';
ob_end_flush();
?>
