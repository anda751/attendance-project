<?php 
include '../auth/check_login.php'; 
include '../config/db.php';
include '../layout/header.php';

// 1. รับค่ารหัสนักศึกษาจากการค้นหา
$student_id = isset($_GET['student_id']) ? $conn->real_escape_string($_GET['student_id']) : '';

// 2. ดึงรายชื่อนักศึกษาทั้งหมดสำหรับตัวเลือก (Search/Select)
$students_list = $conn->query("SELECT * FROM students ORDER BY student_code ASC");

// 3. ถ้ามีการเลือกนักศึกษา ให้ดึงข้อมูลสถิติ
$student_info = null;
$stats_by_subject = [];

if ($student_id) {
    // ดึงข้อมูลพื้นฐานนักศึกษา
    $res_info = $conn->query("SELECT * FROM students WHERE student_id = '$student_id'");
    $student_info = $res_info->fetch_assoc();

    // ดึงสถิติแยกรายวิชา (นับ มา, สาย, ขาด)
    $sql_stats = "SELECT 
                    sub.subject_code, 
                    sub.subject_name,
                    sub.subject_id,
                    SUM(CASE WHEN a.status = 'มา' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN a.status = 'สาย' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN a.status = 'ขาด' THEN 1 ELSE 0 END) as absent,
                    COUNT(a.attendance_id) as total_days
                  FROM subjects sub
                  JOIN enrollments e ON sub.subject_id = e.subject_id
                  LEFT JOIN attendance a ON a.subject_id = sub.subject_id AND a.student_id = '$student_id'
                  WHERE e.student_id = '$student_id'
                  GROUP BY sub.subject_id";
    $stats_by_subject = $conn->query($sql_stats);
}
?>

<style>
    body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; }
    .profile-card { border-radius: 20px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; }
    .stat-box { border-radius: 15px; border: none; transition: transform 0.2s; }
    .stat-box:hover { transform: translateY(-5px); }
    .progress { height: 10px; border-radius: 5px; }
</style>

<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-search me-2"></i>ค้นหาประวัตินักศึกษา</h5>
            <form method="GET" class="row g-2">
                <div class="col-md-9">
                    <select name="student_id" class="form-select border-2" style="border-radius: 12px;">
                        <option value="">-- เลือกนักศึกษาเพื่อดูรายงาน --</option>
                        <?php while($s = $students_list->fetch_assoc()): ?>
                            <option value="<?= $s['student_id'] ?>" <?= $student_id == $s['student_id'] ? 'selected' : '' ?>>
                                [<?= $s['student_code'] ?>] <?= $s['full_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="border-radius: 12px;">
                        <i class="bi bi-file-earmark-bar-graph"></i> ดูรายงาน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($student_info): ?>
        <div class="card profile-card shadow-sm mb-4">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                </div>
                <div class="ms-4">
                    <h3 class="mb-0 fw-bold"><?= $student_info['full_name'] ?></h3>
                    <p class="mb-0 opacity-75">รหัส: <?= $student_info['student_code'] ?> | <?= $student_info['major'] ?> (<?= $student_info['faculty'] ?>)</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($stats_by_subject->num_rows > 0): ?>
                <?php while($st = $stats_by_subject->fetch_assoc()): 
                    $total = $st['total_days'];
                    $percent = $total > 0 ? round(($st['present'] / $total) * 100) : 0;
                    $color = ($percent >= 80) ? 'success' : (($percent >= 50) ? 'warning' : 'danger');
                ?>
                <div class="col-md-6">
                    <div class="card stat-box shadow-sm h-100 p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-light text-primary mb-1"><?= $st['subject_code'] ?></span>
                                <h5 class="fw-bold text-dark mb-0"><?= $st['subject_name'] ?></h5>
                            </div>
                            <div class="text-end">
                                <h4 class="fw-bold text-<?= $color ?> mb-0"><?= $percent ?>%</h4>
                                <small class="text-muted">มาเรียน</small>
                            </div>
                        </div>

                        <div class="progress mb-3 bg-light">
                            <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= $percent ?>%"></div>
                        </div>

                        <div class="row text-center g-2">
                            <div class="col-4">
                                <div class="bg-light p-2 rounded">
                                    <small class="d-block text-muted">มา</small>
                                    <span class="fw-bold text-success"><?= $st['present'] ?></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-2 rounded">
                                    <small class="d-block text-muted">สาย</small>
                                    <span class="fw-bold text-warning"><?= $st['late'] ?></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-2 rounded">
                                    <small class="d-block text-muted">ขาด</small>
                                    <span class="fw-bold text-danger"><?= $st['absent'] ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="subject_detail.php?student_id=<?= $student_id ?>&subject_id=<?= $st['subject_id'] ?>" class="btn btn-link btn-sm text-decoration-none text-muted">
                                <i class="bi bi-clock-history"></i> ดูประวัติละเอียด
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">นักศึกษาคนนี้ยังไม่ได้ลงทะเบียนในวิชาใดๆ</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <button onclick="window.print()" class="btn btn-outline-dark px-4 py-2 border-2 fw-bold">
                <i class="bi bi-printer me-2"></i> พิมพ์รายงานสรุปผล
            </button>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209242.png" width="120" class="opacity-25 mb-3">
            <h5 class="text-muted">กรุณาเลือกนักศึกษาเพื่อดูข้อมูลประวัติการเข้าเรียน</h5>
        </div>
    <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>