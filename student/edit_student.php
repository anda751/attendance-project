<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

$id = $conn->real_escape_string($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $conn->prepare("UPDATE students SET student_code=?, full_name=?, year_level=?, major=?, faculty=? WHERE student_id=?");
    $update->bind_param("ssissi", $_POST['student_code'], $_POST['full_name'], $_POST['year_level'], $_POST['major'], $_POST['faculty'], $id);
    if ($update->execute()) {
        echo "<script>window.location='list_students.php?status=updated';</script>";
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { font-family: 'Kanit', sans-serif; background-color: #f0f2f5; }
    .edit-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }
    .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
        padding: 0.6rem 1rem;
    }
    .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
        background-color: #fff;
    }
    .btn-save {
        border-radius: 12px;
        padding: 12px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    .page-title {
        color: #2d3436;
        font-weight: 600;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            
            <div class="mb-4">
                <a href="list_students.php" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับไปยังรายชื่อ
                </a>
            </div>

            <div class="card edit-card shadow-lg">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning-subtle p-2 rounded-3 me-3">
                            <i class="bi bi-person-gear text-warning fs-4"></i>
                        </div>
                        <h5 class="mb-0 page-title">แก้ไขข้อมูลนักศึกษา</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label text-primary">รหัสนักศึกษา</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                                <input name="student_code" class="form-control" value="<?= htmlspecialchars($student['student_code']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-primary">ชื่อ-นามสกุล</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-primary">ชั้นปี</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                                    <input type="number" name="year_level" class="form-control" min="1" max="8" value="<?= htmlspecialchars($student['year_level']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-primary">สาขาวิชา</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-book"></i></span>
                                    <input name="major" class="form-control" value="<?= htmlspecialchars($student['major']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-primary">คณะ</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input name="faculty" class="form-control" value="<?= htmlspecialchars($student['faculty']) ?>" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 pt-2">
                            <button type="submit" class="btn btn-primary btn-save">
                                <i class="bi bi-check-circle me-2"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                            <a href="list_students.php" class="btn btn-light btn-sm text-muted mt-2">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center text-muted small mt-4">ID อ้างอิงระบบ: #<?= $id ?></p>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>