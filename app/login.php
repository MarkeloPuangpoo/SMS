<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// เริ่ม session ที่นี่ด้วยเพื่อความแน่นอน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($password)) $errors[] = "กรุณากรอกรหัสผ่าน";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                // ตั้งค่า session อย่างระมัดระวัง
                $_SESSION = []; // ล้าง session เก่าก่อน
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['last_active'] = time();
                
                // สร้าง session ID ใหม่เพื่อป้องกัน session fixation
                session_regenerate_id(true);
                
                redirect('dashboard.php');
            } else {
                $errors[] = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $errors[] = "ไม่พบชื่อผู้ใช้";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">เข้าสู่ระบบ</h3>
                        <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                        </button>
                        <div class="text-center">
                            <p class="mb-0">ยังไม่มีบัญชี? <a href="register.php" class="text-primary fw-bold">สมัครสมาชิกที่นี่</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>