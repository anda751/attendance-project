<?php
session_start();
include '../config/db.php';

// ตรวจสอบว่าล็อกอินแล้วหรือยัง
if (isset($_SESSION['teacher'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'];
    $p = md5($_POST['password']); // เข้ารหัส MD5 ตามโครงสร้างเดิมของคุณ

    $stmt = $conn->prepare("SELECT username FROM teachers WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $u, $p);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $_SESSION['teacher'] = $u;
        header("Location: ../dashboard/dashboard.php");
        exit();
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            height: 100vh;
            display: flex; justify-content: center; align-items: center; margin: 0;
            overflow: hidden;
        }
        
        .login-box { width: 100%; max-width: 420px; padding: 15px; animation: fadeInUp 0.6s ease-out; }
        
        /* สไตล์กระจก (Glassmorphism) */
        .card { 
            border: none; 
            border-radius: 25px; 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .card-header { 
            background: #0062ff; 
            background: linear-gradient(to bottom right, #0062ff, #0046b8);
            padding: 40px 30px; 
            color: white; 
            text-align: center; 
            border: none;
        }

        .form-label { font-size: 0.8rem; letter-spacing: 1px; color: #6c757d; }
        
        .input-group-text {
            background: #f8f9fa;
            border: none;
            border-radius: 12px 0 0 12px;
            color: #0062ff;
        }
        
        .form-control { 
            border-radius: 0 12px 12px 0; 
            padding: 12px 15px; 
            background: #f8f9fa; 
            border: none;
        }
        
        .form-control:focus {
            background: #f1f3f5;
            box-shadow: none;
        }
        
        .btn-login { 
            background: #0062ff; 
            border: none; 
            border-radius: 12px; 
            padding: 12px; 
            font-weight: 600; 
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #0052d4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,98,255,0.4);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="card shadow-lg">
        <div class="card-header">
            <div class="mb-3">
                <i class="fas fa-user-shield fa-3x"></i>
            </div>
            <h4 class="mb-0 fw-bold">STAFF LOGIN</h4>
            <small class="opacity-75">เข้าสู่ระบบจัดการข้อมูลและเช็กชื่อ</small>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <?php if($error): ?>
                <div class="alert alert-danger border-0 small text-center mb-4" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold uppercase">ชื่อผู้ใช้งาน</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input name="username" class="form-control" placeholder="ระบุชื่อผู้ใช้งาน" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold uppercase">รหัสผ่าน</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="ระบุรหัสผ่าน" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 mb-3 shadow">
                    เข้าสู่ระบบ <i class="fas fa-arrow-right ms-2"></i>
                </button>
                
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none text-muted small">
                        <i class="fas fa-home me-1"></i> กลับหน้าหลัก
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-4 text-white-50 small">
        &copy; 2025 Attendance Tracking System
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>