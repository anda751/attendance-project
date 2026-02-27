<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System 2025 | ระบบเช็กชื่อออนไลน์</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
        }

        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f0f2f5;
            color: #2d3436;
        }

        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        .nav-link {
            font-weight: 400;
            margin: 0 5px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff !important;
        }

        .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: #fff !important;
            font-weight: 500;
        }

        /* ตกแต่ง Dropdown ให้สวยงาม */
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 10px;
        }

        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
            padding-left: 25px;
        }

        .logout-btn {
            border-radius: 10px;
            padding: 6px 18px;
            font-weight: 500;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .logout-btn:hover {
            background: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="../dashboard/dashboard.php">
            <div class="bg-white rounded-circle p-1 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="bi bi-qr-code-scan text-primary"></i>
            </div>
            <span>ATTENDANCE <small class="fw-light opacity-75">2025</small></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'dashboard') ? 'active' : '' ?>" href="../dashboard/dashboard.php">
                    <i class="bi bi-speedometer2"></i> แผงควบคุม
                </a>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (strpos($_SERVER['PHP_SELF'], 'student') || strpos($_SERVER['PHP_SELF'], 'subject')) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-database-gear"></i> จัดการระบบ
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="../student/list_students.php"><i class="bi bi-people me-2"></i> จัดการนักศึกษา</a></li>
                        <li><a class="dropdown-item" href="../subject/manage_subjects.php"><i class="bi bi-book me-2"></i> จัดการรายวิชา</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../subject/enrollment.php"><i class="bi bi-person-plus me-2"></i> ลงทะเบียนเข้าวิชา</a></li>
                    </ul>
                </div>

                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'attendance') ? 'active' : '' ?>" href="../attendance/attendance.php">
                    <i class="bi bi-check2-square"></i> เช็กชื่อเข้าเรียน
                </a>

                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report') ? 'active' : '' ?>" href="../report/student_report.php">
                    <i class="bi bi-file-earmark-person"></i> รายงานรายคน
                </a>

                <?php if(isset($_SESSION['teacher'])): ?>
                    <div class="ms-lg-3 ps-lg-3 border-start border-white border-opacity-25 mt-3 mt-lg-0">
                        <a class="btn btn-outline-light btn-sm logout-btn" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 pb-5">