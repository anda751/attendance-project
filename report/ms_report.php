<?php 
include '../auth/check_login.php'; 
include '../config/db.php';
include '../layout/header.php';

// ดึงข้อมูลคณะและสาขาเพื่อใช้ในตัวกรอง
$faculties = $conn->query("SELECT DISTINCT faculty FROM students");
$subjects = $conn->query("SELECT DISTINCT subject_id, subject_code, subject_name FROM subjects");

// รับค่าตัวกรอง
$f_faculty = $_GET['faculty'] ?? '';
$f_subject = $_GET['subject_id'] ?? '';

// สร้าง Query สำหรับดึงรายชื่อ มส. (ขาด > 4 ครั้ง)
$sql = "SELECT 
            s.student_code, 
            s.full_name, 
            s.major, 
            s.faculty,
            sub.subject_code, 
            sub.subject_name,
            COUNT(a.attendance_id) as absent_count
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN subjects sub ON e.subject_id = sub.subject_id
        JOIN attendance a ON s.student_id = a.student_id AND sub.subject_id = a.subject_id
        WHERE a.status = 'ขาด' ";

if ($f_faculty) $sql .= " AND s.faculty = '$f_faculty' ";
if ($f_subject) $sql .= " AND sub.subject_id = '$f_subject' ";

$sql .= " GROUP BY s.student_id, sub.subject_id
          HAVING COUNT(a.attendance_id) > 4
          ORDER BY s.faculty ASC, s.student_code ASC";

$result = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-danger">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>รายงานนักศึกษาหมดสิทธิ์สอบ (มส.)
        </h3>
        <button onclick="window.print()" class="btn btn-dark shadow-sm rounded-pill px-4">
            <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">กรองตามคณะ</label>
                    <select name="faculty" class="form-select border-0 bg-light">
                        <option value="">-- ทั้งหมด --</option>
                        <?php while($f = $faculties->fetch_assoc()): ?>
                            <option value="<?= $f['faculty'] ?>" <?= $f_faculty == $f['faculty'] ? 'selected' : '' ?>>
                                <?= $f['faculty'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">กรองตามรายวิชา</label>
                    <select name="subject_id" class="form-select border-0 bg-light">
                        <option value="">-- ทั้งหมด --</option>
                        <?php while($sj = $subjects->fetch_assoc()): ?>
                            <option value="<?= $sj['subject_id'] ?>" <?= $f_subject == $sj['subject_id'] ? 'selected' : '' ?>>
                                <?= $sj['subject_code'] ?> - <?= $sj['subject_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">ค้นหาข้อมูล</button>
                    <a href="ms_report.php" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-danger text-white">
                    <tr>
                        <th class="ps-4 py-3">รหัสนักศึกษา</th>
                        <th class="py-3">ชื่อ-นามสกุล</th>
                        <th class="py-3">คณะ / สาขาวิชา</th>
                        <th class="py-3">วิชาที่ติด มส.</th>
                        <th class="text-center py-3">จำนวนที่ขาด</th>
                        <th class="text-center py-3">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $row['student_code'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td class="small">
                                <span class="d-block fw-bold"><?= $row['faculty'] ?></span>
                                <span class="text-muted"><?= $row['major'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-dark-subtle text-dark border">
                                    <?= $row['subject_code'] ?> <?= htmlspecialchars($row['subject_name']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-danger fw-bold fs-5"><?= $row['absent_count'] ?></span> / ภาคเรียน
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">มส.</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-check-circle text-success display-4 d-block mb-3"></i>
                                <h5 class="text-muted">ยินดีด้วย! ยังไม่มีนักศึกษาคนใดติด มส. ในขณะนี้</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4 p-3 bg-light border rounded-4 small text-muted">
        <strong>หมายเหตุ:</strong> ระบบจะดึงรายชื่อนักศึกษาที่ติด มส. โดยอัตโนมัติเมื่อมีการบันทึกสถานะ <strong>"ขาด"</strong> ครบตั้งแต่ 5 ครั้งขึ้นไปในรายวิชานั้นๆ
    </div>
</div>

<style>
    @media print {
        .btn, .card-body form, .bi-arrow-counterclockwise { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-danger { background-color: #dc3545 !important; -webkit-print-color-adjust: exact; }
        body { background-color: white !important; }
    }
</style>

<?php include '../layout/footer.php'; ?>