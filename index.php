<?php 
session_start(); 
$isLoggedIn = isset($_SESSION['teacher']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System 2025 | ระบบเช็กชื่อออนไลน์</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Kanit', sans-serif;
            overflow: hidden; /* ป้องกันการเลื่อนหน้าจอในหน้า Landing */
        }

        .hero-container {
            /* พื้นหลังไล่เฉดสีร่วมกับรูปภาพ */
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.8) 0%, rgba(42, 82, 152, 0.8) 100%), 
                        url('https://images.unsplash.com/photo-1523050853064-dbad350c00b5?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* เอฟเฟกต์กระจกฟรุ้งฟริ้ง */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 4rem 3rem;
            color: white;
            max-width: 800px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: fadeInScale 0.8s ease-out;
        }

        .logo-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: white;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to bottom, #ffffff, #a1c4fd);
            display: inline-block;
        }

        h1 {
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        /* ปรับแต่งปุ่มให้ดูแพง */
        .btn-custom {
            padding: 12px 40px;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login {
            background: white;
            color: #1e3c72;
            border: none;
        }

        .btn-login:hover {
            background: #f0f2f5;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .btn-dashboard {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
        }

        .btn-dashboard:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-3px);
        }

        /* Animation ตอนเปิดหน้าเว็บ */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* ตกแต่งวงกลมแสงด้านหลัง (Decor) */
        .circle-decor {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }
    </style>
</head>
<body>

<div class="hero-container">
    <div class="circle-decor" style="width: 300px; height: 300px; top: -50px; left: -50px;"></div>
    <div class="circle-decor" style="width: 200px; height: 200px; bottom: 50px; right: 100px;"></div>

    <div class="glass-card shadow-2xl">
        <div class="logo-icon">
            <i class="bi bi-person-check-fill"></i>
        </div>
        <h1 class="display-3 fw-bold mb-3">Attendance System</h1>
        <p class="lead mb-5 opacity-75">ระบบบริหารจัดการข้อมูลนักศึกษาและการเช็กชื่อเข้าเรียนออนไลน์<br>รวดเร็ว แม่นยำ และตรวจสอบได้แบบ Real-time</p>
        
        <div class="d-grid gap-3 d-md-flex justify-content-center">
            <?php if ($isLoggedIn): ?>
                <a href="dashboard/dashboard.php" class="btn btn-dashboard btn-custom btn-lg">
                    <i class="bi bi-grid-1x2 me-2"></i> เข้าสู่หน้าจัดการระบบ
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-login btn-custom btn-lg shadow-lg">
                    <i class="bi bi-door-open me-2"></i> เข้าสู่ระบบสำหรับอาจารย์
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-5 small opacity-50">
            © 2025 Attendance Tracking System. All rights reserved.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>