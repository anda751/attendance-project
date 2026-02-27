<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

$subjects = $conn->query("SELECT * FROM subjects");

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $date = date('Y-m-d');
    
    // ใช้ Prepared Statement เพื่อความปลอดภัย
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, attend_date, status) VALUES (?, ?, ?, ?)");
    
    foreach ($_POST['status'] as $sid => $st) {
        $stmt->bind_param("iiss", $sid, $subject_id, $date, $st);
        $stmt->execute();
    }
    $message = "บันทึกข้อมูลการเช็กชื่อเรียบร้อยแล้ว!";
}
$students = $conn->query("SELECT * FROM students ORDER BY student_code ASC");
?>

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { font-family: 'Kanit', sans-serif; background-color: #f0f2f5; }
    .card { border-radius: 20px; border: none; overflow: hidden; }
    .table thead { background-color: #f8f9fc; }
    
    /* ตกแต่ง Radio Button ให้เป็นปุ่มกด */
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
    .btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: #212529; }
    .btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; }
    
    .status-group .btn {
        padding: 5px 12px;
        font-size: 0.85rem;
        border-radius: 10px;
    }

    .student-row:hover { background-color: rgba(78, 115, 223, 0.05); }
    .sticky-header { position: sticky; top: 0; z-index: 1000; background: white; }
</style>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="border-radius: 15px;">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?= $message ?></div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">บันทึกการเข้าเรียน</h3>
            <p class="text-muted small">ประจำวันที่ <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <form method="post">
        <div class="card shadow-lg mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">
                            <i class="bi bi-book me-1"></i> เลือกรายวิชาที่สอน
                        </label>
                        <select name="subject_id" class="form-select form-select-lg border-2 border-primary" style="border-radius: 12px;" required>
                            <option value="">-- กรุณาเลือกวิชา --</option>
                            <?php while($sj = $subjects->fetch_assoc()): ?>
                                <option value="<?= $sj['subject_id'] ?>">
                                    <?= htmlspecialchars($sj['subject_code']) ?> - <?= htmlspecialchars($sj['subject_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="p-3 bg-light rounded-3 d-inline-block border">
                            <i class="bi bi-people-fill text-primary me-2"></i>
                            จำนวนนักศึกษาทั้งหมด: <strong><?= $students->num_rows ?></strong> คน
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="sticky-header shadow-sm">
                        <tr class="text-muted">
                            <th class="ps-4 py-3">รหัสนักศึกษา</th>
                            <th class="py-3">ชื่อ-นามสกุล</th>
                            <th class="py-3 text-center" width="300">สถานะการเข้าเรียน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <tr class="student-row">
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($s['student_code']) ?></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td class="text-center py-3">
                                <div class="btn-group status-group" role="group">
                                    <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" 
                                        id="present_<?= $s['student_id'] ?>" value="มา" checked autocomplete="off">
                                    <label class="btn btn-outline-success border-2" for="present_<?= $s['student_id'] ?>">มา</label>

                                    <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" 
                                        id="late_<?= $s['student_id'] ?>" value="สาย" autocomplete="off">
                                    <label class="btn btn-outline-warning border-2" for="late_<?= $s['student_id'] ?>">สาย</label>

                                    <input type="radio" class="btn-check" name="status[<?= $s['student_id'] ?>]" 
                                        id="absent_<?= $s['student_id'] ?>" value="ขาด" autocomplete="off">
                                    <label class="btn btn-outline-danger border-2" for="absent_<?= $s['student_id'] ?>">ขาด</label>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow" style="border-radius: 15px;">
                    <i class="bi bi-save2 me-2"></i> บันทึกการเช็กชื่อทั้งหมด
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../layout/footer.php'; ?>