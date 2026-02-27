<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

// ระบบลบข้อมูลพร้อมแจ้งเตือน
$delete_msg = false;
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    if ($stmt->execute()) {
        $delete_msg = true;
    }
}

$status = isset($_GET['status']) ? $_GET['status'] : '';
$students = $conn->query("SELECT * FROM students ORDER BY student_code ASC");

// ดึงรายชื่อคณะและสาขาที่มีอยู่ในระบบมาทำตัวเลือก (Dropdown)
$faculties = $conn->query("SELECT DISTINCT faculty FROM students WHERE faculty != ''");
$majors = $conn->query("SELECT DISTINCT major FROM students WHERE major != ''");
?>

<style>
    body { font-family: 'Kanit', sans-serif; background-color: #f0f2f5; }
    .main-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; }
    .table thead th { background-color: #f8f9fc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600; color: #4e73df; border-bottom: none; padding: 15px; }
    .student-row:hover { background-color: rgba(78, 115, 223, 0.02); }
    .badge-year { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; font-weight: 500; padding: 5px 12px; border-radius: 8px; }
    .filter-label { font-size: 0.8rem; font-weight: 600; color: #6c757d; margin-bottom: 5px; display: block; }
    .form-select-sm { border-radius: 10px; border: 1px solid #e3e6f0; }
</style>

<div class="container-fluid py-4">
    
    <?php if ($delete_msg): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert" style="border-radius: 15px;">
            <i class="bi bi-trash-fill me-2"></i> ลบข้อมูลนักศึกษาเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">จัดการข้อมูลนักศึกษา</h3>
            <p class="text-muted small">ค้นหาและกรองรายชื่อนักศึกษาตามเงื่อนไข</p>
        </div>
        <a href="add_student.php" class="btn btn-warning px-4 py-2 text-dark fw-bold" style="border-radius: 12px;">
            <i class="bi bi-plus-lg me-2"></i> เพิ่มนักศึกษาใหม่
        </a>
    </div>

    <div class="card main-card overflow-hidden">
        <div class="card-header bg-white py-4 px-4 border-0">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="filter-label"><i class="bi bi-search me-1"></i> ค้นหาชื่อ/รหัส</label>
                    <input type="text" id="searchInput" class="form-control form-control-sm bg-light border-0" placeholder="พิมพ์เพื่อค้นหา...">
                </div>
                <div class="col-md-2">
                    <label class="filter-label"><i class="bi bi-calendar3 me-1"></i> ชั้นปี</label>
                    <select id="filterYear" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="1">ชั้นปีที่ 1</option>
                        <option value="2">ชั้นปีที่ 2</option>
                        <option value="3">ชั้นปีที่ 3</option>
                        <option value="4">ชั้นปีที่ 4</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="filter-label"><i class="bi bi-building me-1"></i> คณะ</label>
                    <select id="filterFaculty" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <?php while($f = $faculties->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($f['faculty']) ?>"><?= htmlspecialchars($f['faculty']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="filter-label"><i class="bi bi-book me-1"></i> สาขาวิชา</label>
                    <select id="filterMajor" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <?php while($m = $majors->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($m['major']) ?>"><?= htmlspecialchars($m['major']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-light btn-sm w-100" onclick="window.location.reload()" title="ล้างการกรอง">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="studentTable">
                    <thead>
                        <tr>
                            <th class="ps-4">รหัสนักศึกษา</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th class="text-center">ชั้นปี</th>
                            <th>คณะ / สาขาวิชา</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <tr class="student-row" 
                            data-year="<?= $s['year_level'] ?>" 
                            data-faculty="<?= htmlspecialchars($s['faculty']) ?>" 
                            data-major="<?= htmlspecialchars($s['major']) ?>">
                            <td class="ps-4">
                                <span class="fw-bold text-primary"><?= htmlspecialchars($s['student_code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['full_name']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge-year">ปี <?= htmlspecialchars($s['year_level']) ?></span>
                            </td>
                            <td>
                                <div class="small text-muted">คณะ<?= htmlspecialchars($s['faculty']) ?></div>
                                <div class="small fw-bold text-secondary">สาขา<?= htmlspecialchars($s['major']) ?></div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit_student.php?id=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </a>
                                <a href="?delete=<?= $s['student_id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                   onclick="return confirm('ยืนยันการลบ?')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    let searchTerm = document.getElementById('searchInput').value.toUpperCase();
    let yearTerm = document.getElementById('filterYear').value;
    let facultyTerm = document.getElementById('filterFaculty').value.toUpperCase();
    let majorTerm = document.getElementById('filterMajor').value.toUpperCase();
    
    let rows = document.querySelector("#studentTable tbody").rows;
    
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        if (row.cells.length < 5) continue; // ข้ามแถว "ไม่พบข้อมูล"
        
        let textContent = row.textContent.toUpperCase();
        let rowYear = row.getAttribute('data-year');
        let rowFaculty = row.getAttribute('data-faculty').toUpperCase();
        let rowMajor = row.getAttribute('data-major').toUpperCase();
        
        // เงื่อนไขการตรวจสอบ
        let matchSearch = textContent.indexOf(searchTerm) > -1;
        let matchYear = yearTerm === "" || rowYear === yearTerm;
        let matchFaculty = facultyTerm === "" || rowFaculty === facultyTerm;
        let matchMajor = majorTerm === "" || rowMajor === majorTerm;
        
        if (matchSearch && matchYear && matchFaculty && matchMajor) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    }
}

// ผูก Event ให้กับทุกตัวกรอง
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('filterYear').addEventListener('change', filterTable);
document.getElementById('filterFaculty').addEventListener('change', filterTable);
document.getElementById('filterMajor').addEventListener('change', filterTable);
</script>

<?php include '../layout/footer.php'; ?>