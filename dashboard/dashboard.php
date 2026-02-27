<?php 
include '../auth/check_login.php'; 
include '../config/db.php';
include '../layout/header.php';

// 1. รับค่าการกรองจาก URL
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date']   ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$year_level = $_GET['year_level'] ?? '';

// เช็กสถานะการกรอง
$is_filtered = ($start_date != '' || $end_date != '' || $subject_id != '' || $year_level != '');

// 2. ดึงรายวิชาสำหรับ Dropdown
$subjects_list = $conn->query("SELECT * FROM subjects ORDER BY subject_code ASC");

// 3. เตรียมตัวแปร
$stats = ['p' => 0, 'l' => 0, 'a' => 0];
$recent_list = null;
$total_records = 0;

if ($is_filtered) {
    $where = " WHERE 1=1 ";
    if ($start_date && $end_date) $where .= " AND a.attend_date BETWEEN '$start_date' AND '$end_date' ";
    if ($subject_id) $where .= " AND a.subject_id = '$subject_id' ";
    if ($year_level) $where .= " AND s.year_level = '$year_level' ";

    // Query สถิติรวม
    $stat_sql = "SELECT 
                    SUM(a.status='มา') as p, 
                    SUM(a.status='สาย') as l, 
                    SUM(a.status='ขาด') as a 
                 FROM attendance a
                 JOIN students s ON a.student_id = s.student_id
                 $where";
    $stats = $conn->query($stat_sql)->fetch_assoc();
    $total_records = (int)$stats['p'] + (int)$stats['l'] + (int)$stats['a'];

    // Query รายชื่อล่าสุด
    $list_sql = "SELECT a.*, s.full_name, s.student_code, s.year_level, sub.subject_name 
                 FROM attendance a 
                 JOIN students s ON a.student_id = s.student_id 
                 JOIN subjects sub ON a.subject_id = sub.subject_id 
                 $where 
                 ORDER BY a.attend_date DESC, a.attendance_id DESC LIMIT 50";
    $recent_list = $conn->query($list_sql);
}
?>

<style>
    .stat-card { transition: transform 0.3s; border-radius: 20px; }
    .stat-card:hover { transform: translateY(-5px); }
    .filter-section { background: #fff; border-radius: 20px; border: 1px solid #edf2f9; }
    .welcome-text { color: #1e3c72; font-weight: 600; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="welcome-text mb-0">สวัสดีครับ, อาจารย์ <?= $_SESSION['teacher'] ?> 👋</h4>
            <p class="text-muted small mb-0">นี่คือภาพรวมข้อมูลการเข้าเรียนในระบบของคุณ</p>
        </div>
        <div class="d-none d-md-flex gap-2">
            <a href="../attendance/attendance.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-calendar-check me-1"></i> เช็กชื่อตอนนี้
            </a>
            <a href="../student/list_students.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-people me-1"></i> รายชื่อนักศึกษา
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 filter-section">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-bold text-secondary">จากวันที่</label>
                    <input type="date" name="start_date" class="form-control border-0 bg-light" value="<?= $start_date ?>">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-bold text-secondary">ถึงวันที่</label>
                    <input type="date" name="end_date" class="form-control border-0 bg-light" value="<?= $end_date ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">รายวิชา</label>
                    <select name="subject_id" class="form-select border-0 bg-light">
                        <option value="">-- ทุกรายวิชา --</option>
                        <?php $subjects_list->data_seek(0); while($sj = $subjects_list->fetch_assoc()): ?>
                            <option value="<?= $sj['subject_id'] ?>" <?= ($subject_id == $sj['subject_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sj['subject_code']) ?> - <?= htmlspecialchars($sj['subject_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">ชั้นปี</label>
                    <select name="year_level" class="form-select border-0 bg-light">
                        <option value="">-- ทุกชั้นปี --</option>
                        <?php for($i=1; $i<=4; $i++): ?>
                            <option value="<?= $i ?>" <?= $year_level == $i ? 'selected' : '' ?>>ปีที่ <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm rounded-3">ค้นหาข้อมูล</button>
                    <a href="dashboard.php" class="btn btn-light border rounded-3"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($is_filtered): ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white p-3 stat-card">
                    <div class="small opacity-75">บันทึกทั้งหมด</div>
                    <div class="h3 fw-bold mb-0"><?= $total_records ?> รายการ</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white p-3 stat-card">
                    <div class="small opacity-75 text-white-50">มาเรียน</div>
                    <div class="h3 fw-bold mb-0"><?= (int)$stats['p'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark p-3 stat-card">
                    <div class="small opacity-75">มาสาย</div>
                    <div class="h3 fw-bold mb-0"><?= (int)$stats['l'] ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white p-3 stat-card">
                    <div class="small opacity-75 text-white-50">ขาดเรียน</div>
                    <div class="h3 fw-bold mb-0"><?= (int)$stats['a'] ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">สัดส่วนการเข้าเรียน</h6>
                        <i class="bi bi-pie-chart text-primary"></i>
                    </div>
                    <?php if($total_records > 0): ?>
                        <div style="height: 250px;"><canvas id="statChart"></canvas></div>
                        <div class="mt-4 text-center">
                            <?php 
                                $percent = round(((int)$stats['p'] / $total_records) * 100, 1);
                                $color = ($percent > 80) ? 'success' : (($percent > 50) ? 'warning' : 'danger');
                            ?>
                            <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> px-3 py-2 rounded-pill">
                                มาเรียนคิดเป็น <?= $percent ?>% ของทั้งหมด
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">ไม่มีข้อมูลแสดงผล</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">ประวัติการเช็กชื่อล่าสุด (50 รายการ)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted">
                                    <th class="ps-4">นักศึกษา</th>
                                    <th>วิชา</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="pe-4 text-end">วันที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($recent_list && $recent_list->num_rows > 0): ?>
                                    <?php while($r = $recent_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= $r['student_code'] ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($r['full_name']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold"><?= htmlspecialchars($r['subject_name']) ?></div>
                                            <div class="small text-muted">ชั้นปีที่ <?= $r['year_level'] ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                $badge_class = match($r['status']) {
                                                    'มา' => 'success',
                                                    'สาย' => 'warning text-dark',
                                                    'ขาด' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>
                                            <span class="badge bg-<?= $badge_class ?> rounded-pill px-3">
                                                <?= $r['status'] ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end small text-muted"><?= date('d/m/Y', strtotime($r['attend_date'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">ไม่พบข้อมูลตามเงื่อนไขที่ระบุ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('statChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['มา', 'สาย', 'ขาด'],
                        datasets: [{
                            data: [<?= (int)$stats['p'] ?>, <?= (int)$stats['l'] ?>, <?= (int)$stats['a'] ?>],
                            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                            hoverOffset: 10,
                            borderWidth: 0
                        }]
                    },
                    options: { 
                        maintainAspectRatio: false, 
                        cutout: '80%', 
                        plugins: { 
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } 
                        } 
                    }
                });
            }
        </script>
    <?php else: ?>
        <div class="text-center py-5 my-5 bg-white shadow-sm rounded-4 border">
            <div class="mb-3">
                <i class="bi bi-funnel display-1 text-primary opacity-25"></i>
            </div>
            <h5 class="fw-bold">เริ่มต้นใช้งาน Dashboard</h5>
            <p class="text-muted">กรุณาเลือก <b>รายวิชา</b> หรือ <b>ช่วงวันที่</b> ด้านบน เพื่อเรียกดูสถิติการเข้าเรียน</p>
            <div class="mt-4">
                <span class="badge bg-light text-dark border p-2 px-3">
                    <i class="bi bi-info-circle me-1"></i> ระบบรองรับการดูข้อมูลย้อนหลังทุกเทอม
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>