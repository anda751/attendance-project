<?php 
include '../auth/check_login.php';
include '../config/db.php';
include '../layout/header.php';

// --- Logic การลบวิชา ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE subject_id = $id");
    echo "<script>window.location='manage_subjects.php';</script>";
}

// --- Logic การเพิ่มวิชา ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $code = $_POST['subject_code'];
    $name = $_POST['subject_name'];
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $code, $name);
    $stmt->execute();
}

$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_code ASC");
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มรายวิชาใหม่</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label small">รหัสวิชา</label>
                            <input type="text" name="subject_code" class="form-control" placeholder="เช่น CS101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">ชื่อรายวิชา</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="เช่น พื้นฐานโปรแกรมมิ่ง" required>
                        </div>
                        <button type="submit" name="add_subject" class="btn btn-primary w-100 shadow-sm">บันทึกวิชา</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">รหัสวิชา</th>
                            <th>ชื่อรายวิชา</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($sj = $subjects->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?= $sj['subject_code'] ?></td>
                            <td><?= htmlspecialchars($sj['subject_name']) ?></td>
                            <td class="text-end pe-4">
                                <a href="?delete=<?= $sj['subject_id'] ?>" class="btn btn-outline-danger btn-sm border-2" onclick="return confirm('ยืนยันการลบวิชานี้?')">
                                    <i class="bi bi-trash"></i>
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
<?php include '../layout/footer.php'; ?>