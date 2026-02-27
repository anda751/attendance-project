<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

// รับค่า Subject ID จากการเลือกในฟอร์ม
$subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : (isset($_GET['subject_id']) ? $_GET['subject_id'] : '');

// ดึงรายวิชาทั้งหมดสำหรับ Dropdown
$subjects_list = $conn->query("SELECT * FROM subjects ORDER BY subject_code ASC");

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $current_subject = $_POST['subject_id'];
    $date = date('Y-m-d');
    
    if (!empty($_POST['selected_students'])) {
        // แก้ไขจุดที่ 1: เพิ่ม ON DUPLICATE KEY UPDATE เพื่อให้อัปเดตสถานะเดิมถ้ามีการบันทึกซ้ำในวันเดียวกัน
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, attend_date, status) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE status = VALUES(status)");
        
        foreach ($_POST['selected_students'] as $sid) {
            $status = $_POST['status'][$sid];
            $stmt->bind_param("iiss", $sid, $current_subject, $date, $status);
            $stmt->execute();
        }
        $message = "บันทึก/อัปเดตการเช็กชื่อเรียบร้อยแล้ว!";
    } else {
        $message = "ERROR: กรุณาเลือกนักศึกษาอย่างน้อย 1 คน";
    }
}

// Query ดึงเฉพาะนักศึกษาที่ "ลงทะเบียน" ในวิชาที่เลือกเท่านั้น
$students = null;
if ($subject_id) {
    // แก้ไขจุดที่ 2: ใช้ Prepared Statement เพื่อความปลอดภัยจาก SQL Injection
    $stmt_fetch = $conn->prepare("SELECT s.* FROM students s 
                                  JOIN enrollments e ON s.student_id = e.student_id 
                                  WHERE e.subject_id = ? 
                                  ORDER BY s.student_code ASC");
    $stmt_fetch->bind_param("s", $subject_id);
    $stmt_fetch->execute();
    $students = $stmt_fetch->get_result();
}
?>

<style>
    body { font-family: 'Kanit', sans-serif; background-color: #f0f2f5; }
    .card { border-radius: 20px; border: none; }
    .student-row { transition: all 0.2s; }
    .disabled-row { opacity: 0.4; background-color: #f8f9fa; filter: grayscale(1); }
    .status-group .btn { padding: 5px 15px; font-size: 0.9rem; border-radius: 10px; }
    .sticky-select { position: sticky; top: 80px; z-index: 1020; }
</style>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?= strpos($message, 'ERROR') !== false ? 'danger' : 'success' ?> border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post" id="attendanceForm">
        <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id) ?>">

        <div class="card shadow-sm mb-4 sticky-select">
            <div class="card-body p-4">
                <div class="row align-items-end">
                    <div class="col-md-7">
                        <label class="form-label fw-bold text-primary"><i class="bi bi-book-half me-1"></i> เลือกวิชาที่ต้องการเช็กชื่อ</label>
                        <select class="form-select border-2" style="border-radius: 12px;" onchange="location.href='?subject_id='+this.value" required>
                            <option value="">-- กรุณาเลือกวิชา --</option>
                            <?php while($sj = $subjects_list->fetch_assoc()): ?>
                                <option value="<?= $sj['subject_id'] ?>" <?= $subject_id == $sj['subject_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sj['subject_code']) ?> - <?= htmlspecialchars($sj['subject_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if ($subject_id): ?>
                    <div class="col-md-5 text-md-end mt-3">
                        <div class="form-check form-switch d-inline-block p-2 px-3 bg-light rounded-pill border">
                            <input class="form-check-input" type="checkbox" id="selectAll" checked>
                            <label class="form-check-label small fw-bold" for="selectAll">เลือกนักศึกษาทั้งหมด</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($subject_id): ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="attendanceTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" width="80">เลือก</th>
                            <th>รหัสนักศึกษา</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th class="text-center" width="300">สถานะการเข้าเรียน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($students && $students->num_rows > 0): ?>
                            <?php while($s = $students->fetch_assoc()): ?>
                            <tr class="student-row" id="row_<?= $s['student_id'] ?>">
                                <td class="ps-4">
                                    <input class="form-check-input student-checkbox" type="checkbox" 
                                           name="selected_students[]" 
                                           value="<?= $s['student_id'] ?>" 
                                           id="check_<?= $s['student_id'] ?>" checked>
                                </td>
                                <td><span class="fw-bold"><?= htmlspecialchars($s['student_code']) ?></span></td>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td class="text-center py-3">
                                    <div class="btn-group status-group" role="group">
                                        <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" id="p_<?= $s['student_id'] ?>" value="มา" checked>
                                        <label class="btn btn-outline-success border-2" for="p_<?= $s['student_id'] ?>">มา</label>

                                        <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" id="l_<?= $s['student_id'] ?>" value="สาย">
                                        <label class="btn btn-outline-warning border-2" for="l_<?= $s['student_id'] ?>">สาย</label>

                                        <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" id="a_<?= $s['student_id'] ?>" value="ขาด">
                                        <label class="btn btn-outline-danger border-2" for="a_<?= $s['student_id'] ?>">ขาด</label>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-exclamation fs-1 d-block mb-2"></i>
                                    วิชานี้ยังไม่มีนักศึกษาลงทะเบียนเรียน <br>
                                    <a href="../subject/enrollment.php?subject_id=<?= $subject_id ?>" class="btn btn-sm btn-primary mt-3">ไปหน้าลงทะเบียน</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($students && $students->num_rows > 0): ?>
            <div class="card-footer bg-white p-4 text-center border-0">
                <button type="submit" name="save_attendance" class="btn btn-primary btn-lg px-5 shadow-lg" style="border-radius: 15px;">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> ยืนยันการบันทึกข้อมูล
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <img src="https://cdn-icons-png.flaticon.com/512/2666/2666505.png" width="100" class="opacity-25 mb-3">
                <h5>กรุณาเลือกรายวิชาด้านบน เพื่อแสดงรายชื่อนักศึกษา</h5>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
function toggleRowStyle(checkbox) {
    const row = document.getElementById('row_' + checkbox.value);
    const radios = row.querySelectorAll('input[type="radio"]');
    if (checkbox.checked) {
        row.classList.remove('disabled-row');
        radios.forEach(r => r.disabled = false);
    } else {
        row.classList.add('disabled-row');
        radios.forEach(r => r.disabled = true);
    }
}

document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
        toggleRowStyle(cb);
    });
});

document.querySelectorAll('.student-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        toggleRowStyle(this);
    });
});
</script>

<?php include '../layout/footer.php'; ?>