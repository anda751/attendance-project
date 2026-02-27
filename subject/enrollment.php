<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';

// --- Logic บันทึกการลงทะเบียน ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_enroll'])) {
    $current_sub = $_POST['subject_id'];
    // 1. ล้างข้อมูลเก่าของวิชานี้ก่อน (เพื่ออัปเดตใหม่)
    $conn->query("DELETE FROM enrollments WHERE subject_id = $current_sub");
    
    // 2. เพิ่มคนใหม่เข้าไป
    if (isset($_POST['students'])) {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
        foreach ($_POST['students'] as $s_id) {
            $stmt->bind_param("ii", $s_id, $current_sub);
            $stmt->execute();
        }
    }
    echo "<script>alert('บันทึกการลงทะเบียนเรียบร้อยแล้ว'); window.location='enrollment.php?subject_id=$current_sub';</script>";
}

$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_code ASC");
$students = $conn->query("SELECT * FROM students ORDER BY student_code ASC");

// ดึงข้อมูลคณะ/สาขา เพื่อทำ Dropdown กรองข้อมูล
$faculties = $conn->query("SELECT DISTINCT faculty FROM students WHERE faculty != ''");
$majors = $conn->query("SELECT DISTINCT major FROM students WHERE major != ''");

$enrolled_ids = [];
if ($subject_id) {
    $res = $conn->query("SELECT student_id FROM enrollments WHERE subject_id = $subject_id");
    while($row = $res->fetch_assoc()) { $enrolled_ids[] = $row['student_id']; }
}
?>

<style>
    .filter-box { background-color: #f8f9fc; border-radius: 12px; padding: 15px; border: 1px solid #e3e6f0; }
    .sticky-action { position: sticky; bottom: 20px; z-index: 100; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-plus-fill me-2 text-primary"></i>ระบบลงทะเบียนวิชาเรียน</h3>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <label class="form-label fw-bold text-muted small">ขั้นตอนที่ 1: เลือกรายวิชา</label>
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <select name="subject_id" class="form-select form-select-lg border-2" onchange="this.form.submit()">
                        <option value="">-- กรุณาเลือกวิชาที่จะจัดการรายชื่อ --</option>
                        <?php $subjects->data_seek(0); while($sj = $subjects->fetch_assoc()): ?>
                            <option value="<?= $sj['subject_id'] ?>" <?= $subject_id == $sj['subject_id'] ? 'selected' : '' ?>>
                                <?= $sj['subject_code'] ?> - <?= $sj['subject_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <?php if($subject_id): ?>
                        <span class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill">
                            <i class="bi bi-people-fill me-1"></i> ลงทะเบียนแล้ว <?= count($enrolled_ids) ?> คน
                        </span>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($subject_id): ?>
    <div class="filter-box mb-3 shadow-sm">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="small fw-bold text-muted">ค้นหารายชื่อ/รหัส</label>
                <input type="text" id="searchStudent" class="form-control form-control-sm" placeholder="พิมพ์ชื่อหรือรหัส...">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">ชั้นปี</label>
                <select id="filterYear" class="form-select form-select-sm" onchange="filterList()">
                    <option value="">ทั้งหมด</option>
                    <option value="1">ปี 1</option>
                    <option value="2">ปี 2</option>
                    <option value="3">ปี 3</option>
                    <option value="4">ปี 4</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted">คณะ</label>
                <select id="filterFaculty" class="form-select form-select-sm" onchange="filterList()">
                    <option value="">ทั้งหมด</option>
                    <?php while($f = $faculties->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($f['faculty']) ?>"><?= htmlspecialchars($f['faculty']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted">สาขาวิชา</label>
                <select id="filterMajor" class="form-select form-select-sm" onchange="filterList()">
                    <option value="">ทั้งหมด</option>
                    <?php while($m = $majors->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($m['major']) ?>"><?= htmlspecialchars($m['major']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="resetFilter()" title="รีเซ็ต">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <form method="post">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold">ขั้นตอนที่ 2: เลือกรายชื่อนักศึกษา</span>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-2" onclick="checkAll(true)">เลือกทั้งหมดที่เห็น</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="checkAll(false)">ยกเลิกทั้งหมดที่เห็น</button>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-hover align-middle mb-0" id="enrollTable">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="ps-4" width="80">เลือก</th>
                            <th width="150">รหัสนักศึกษา</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th class="text-center">ชั้นปี</th>
                            <th>คณะ / สาขา</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <tr class="student-row" 
                            data-year="<?= $s['year_level'] ?>" 
                            data-faculty="<?= htmlspecialchars($s['faculty']) ?>" 
                            data-major="<?= htmlspecialchars($s['major']) ?>">
                            <td class="ps-4 text-center">
                                <input class="form-check-input border-2 check-student" type="checkbox" name="students[]" value="<?= $s['student_id'] ?>" 
                                <?= in_array($s['student_id'], $enrolled_ids) ? 'checked' : '' ?>>
                            </td>
                            <td class="fw-bold text-primary"><?= $s['student_code'] ?></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border">ปี <?= $s['year_level'] ?></span></td>
                            <td class="small text-muted"><?= htmlspecialchars($s['faculty']) ?> / <?= htmlspecialchars($s['major']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white p-4 text-center border-0 sticky-action">
                <button type="submit" name="save_enroll" class="btn btn-success btn-lg px-5 shadow-lg rounded-pill fw-bold">
                    <i class="bi bi-cloud-check me-2"></i> บันทึกรายชื่อนักศึกษาที่เลือกลงทะเบียน
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
function filterList() {
    let search = document.getElementById('searchStudent').value.toUpperCase();
    let year = document.getElementById('filterYear').value;
    let faculty = document.getElementById('filterFaculty').value.toUpperCase();
    let major = document.getElementById('filterMajor').value.toUpperCase();
    
    let rows = document.querySelectorAll("#enrollTable tbody tr");
    
    rows.forEach(row => {
        let text = row.innerText.toUpperCase();
        let rYear = row.getAttribute('data-year');
        let rFaculty = row.getAttribute('data-faculty').toUpperCase();
        let rMajor = row.getAttribute('data-major').toUpperCase();
        
        let matchSearch = text.indexOf(search) > -1;
        let matchYear = year === "" || rYear === year;
        let matchFaculty = faculty === "" || rFaculty === faculty;
        let matchMajor = major === "" || rMajor === major;
        
        if(matchSearch && matchYear && matchFaculty && matchMajor) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

function checkAll(status) {
    let rows = document.querySelectorAll("#enrollTable tbody tr");
    rows.forEach(row => {
        if(row.style.display !== "none") {
            row.querySelector('.check-student').checked = status;
        }
    });
}

function resetFilter() {
    document.getElementById('searchStudent').value = "";
    document.getElementById('filterYear').value = "";
    document.getElementById('filterFaculty').value = "";
    document.getElementById('filterMajor').value = "";
    filterList();
}

document.getElementById('searchStudent').addEventListener('keyup', filterList);
</script>

<?php include '../layout/footer.php'; ?>