<?php
/*
 * ไฟล์: /view_applicants.php
 * หน้าที่: (Role 2) ดูรายชื่อคนสมัครงาน 1 ชิ้น + กด Approve/Reject
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. "ยาม" เฉพาะทาง (Role 2 เท่านั้น)
if ($role_id != 2) {
    header("Location: dashboard.php"); exit;
}

// 3. รับ ID งานจาก URL (และเช็กว่าส่งมา)
if (!isset($_GET['job_id'])) {
    header("Location: my_jobs.php"); exit;
}
$job_id = $_GET['job_id'];

// 4. (สำคัญ!) ดึงข้อมูลผู้สมัคร (APPLICATIONS)
//    เราต้อง JOIN ไปหา JOB_SEEKER_PROFILES เพื่อเอา "ชื่อ" และ "ทักษะ"
try {
    $sql = "SELECT 
                A.application_id,
                A.status,
                A.applied_at,
                P.name AS seeker_name,
                P.phone AS seeker_phone,
                P.skills AS seeker_skills,
                P.experience AS seeker_experience
            FROM APPLICATIONS AS A
            JOIN JOB_SEEKER_PROFILES AS P ON A.seeker_id = P.seeker_id
            WHERE A.job_id = ?
            ORDER BY 
                CASE A.status
                    WHEN 'pending' THEN 1
                    WHEN 'approved' THEN 2
                    WHEN 'rejected' THEN 3
                END, A.applied_at DESC"; // (เอา 'pending' ขึ้นก่อน)
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_id]);
    $applicants = $stmt->fetchAll();

    // (ดึงชื่องานมาโชว์หัวกระดาษ)
    $stmt_job = $pdo->prepare("SELECT job_title FROM JOBS WHERE job_id = ?");
    $stmt_job->execute([$job_id]);
    $job = $stmt_job->fetch();
    $job_title = $job ? $job['job_title'] : "ไม่พบชื่องาน";

} catch (Exception $e) {
    $applicants = [];
    $job_title = "Error";
    echo "<p style='color:red;'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>

<h1>ใบสมัครสำหรับงาน: <?php echo htmlspecialchars($job_title); ?></h1>
<p><a href="my_jobs.php">&larr; กลับไปหน้ารวมงาน</a></p>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e0ffe0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<style>
    /* (ใช้ CSS จาก my_applications.php ได้) */
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .status-pending { color: orange; font-weight: bold; }
    .status-approved { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }
    .action-approve { color: green; text-decoration: none; font-weight: bold; }
    .action-reject { color: red; text-decoration: none; font-weight: bold; margin-left: 10px; }
</style>

<table>
    <thead>
        <tr>
            <th>ชื่อผู้สมัคร</th>
            <th>ทักษะ/ประสบการณ์</th>
            <th>เบอร์โทร</th>
            <th>วันที่สมัคร</th>
            <th>สถานะ</th>
            <th>ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($applicants)): ?>
            <tr>
                <td colspan="6">ยังไม่มีผู้สมัครสำหรับงานนี้</td>
            </tr>
        <?php else: ?>
            <?php foreach ($applicants as $app): ?>
                <tr>
                    <td><?php echo htmlspecialchars($app['seeker_name']); ?></td>
                    <td>
                        <strong>ทักษะ:</strong> <?php echo htmlspecialchars($app['seeker_skills']); ?><br>
                        <strong>ปสก.:</strong> <?php echo htmlspecialchars($app['seeker_experience']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($app['seeker_phone']); ?></td>
                    <td><?php echo $app['applied_at']; ?></td>
                    <td>
                        <span class="status-<?php echo strtolower($app['status']); ?>">
                            <?php echo htmlspecialchars($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($app['status'] == 'pending'): ?>
                            <a href="update_application_status.php?app_id=<?php echo $app['application_id']; ?>&status=approved"
                               class="action-approve"
                               onclick="return confirm('ยืนยันอนุมัติผู้สมัครคนนี้?');">
                               อนุมัติ
                            </a>
                            <a href="update_application_status.php?app_id=<?php echo $app['application_id']; ?>&status=rejected"
                               class="action-reject"
                               onclick="return confirm('ยืนยันปฏิเสธผู้สมัครคนนี้?');">
                               ปฏิเสธ
                            </a>
                        <?php else: ?>
                            (เรียบร้อยแล้ว)
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 5. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>
