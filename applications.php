<?php
/*
 * ไฟล์: /applications.php
 * (ฉบับแก้ไข: แก้ปัญหา session ซ้ำซ้อน และใช้ U.email/JSP.name แทน U.username)
 */

// 1. เรียก config.php ก่อน header.php
require 'includes/config.php'; 
// 2. เรียก header.php (ไฟล์นี้ควรมี session_start() อยู่แล้ว)
require 'includes/header.php'; 

// 3. "ยาม" (ต้องเป็น Role 2: นายจ้าง)
// $role_id และ $user_id ถูกกำหนดใน includes/header.php
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (ต้องเป็น Role 2)");
}

// 4. รับ Job ID จาก URL
$job_id = $_GET['job_id'] ?? die("ไม่ระบุ Job ID");
$current_user_id = $user_id;

// 5. ดึงข้อมูลใบสมัครและโปรไฟล์ผู้สมัคร
try {
    // 5.1 เช็กสิทธิ์และดึงข้อมูลงาน
    $stmt_job = $pdo->prepare("
        SELECT J.job_title, S.user_id 
        FROM JOBS AS J
        JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE J.job_id = ? AND S.user_id = ?
    ");
    $stmt_job->execute([$job_id, $current_user_id]);
    $job_data = $stmt_job->fetch();

    if (!$job_data) {
        die("ไม่พบงานนี้ หรือคุณไม่ใช่เจ้าของงาน");
    }

    // 5.2 ดึงรายชื่อผู้สมัครทั้งหมด
    $stmt_applicants = $pdo->prepare("
        SELECT
            A.application_id, 
            A.applied_at, 
            A.status,
            U.email, /* <-- แก้จาก U.username เป็น U.email */
            JSP.name, /* <-- ใช้ JSP.name จากตาราง job_seeker_profiles */
            JSP.seeker_id
        FROM
            APPLICATIONS AS A
        JOIN
            JOB_SEEKER_PROFILES AS JSP ON A.seeker_id = JSP.seeker_id
        JOIN
            USERS AS U ON JSP.user_id = U.user_id
        WHERE
            A.job_id = ?
        ORDER BY
            A.applied_at DESC
    ");
    $stmt_applicants->execute([$job_id]);
    $applicants = $stmt_applicants->fetchAll();

} catch (Exception $e) {
    // แสดงข้อความ Error SQL ที่ชัดเจน
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: SQLSTATE[42S22]: " . $e->getMessage());
} 
?>

<h1>ผู้สมัครสำหรับงาน: <?php echo htmlspecialchars($job_data['job_title']); ?></h1>
<p><a href="edit_job.php?job_id=<?php echo $job_id; ?>">&lt; กลับไปหน้าจัดการงาน</a></p>

<?php if (empty($applicants)): ?>
    <div style="background: #fff8e1; padding: 15px; border-radius: 8px;">
        <h4>ยังไม่มีผู้สมัครงานนี้ในขณะนี้</h4>
        <p>คุณสามารถแชร์ลิงก์งานนี้เพิ่มเติมได้</p>
    </div>
<?php else: ?>
    
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead> 
            <tr> 
                <th>ชื่อผู้สมัคร (อีเมล)</th> 
                <th>วันที่สมัคร</th> 
                <th>สถานะ</th> 
                <th>ดำเนินการ</th> 
            </tr> 
        </thead>
        <tbody>
            <?php foreach ($applicants as $applicant): ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($applicant['name']); ?>
                        (<?php echo htmlspecialchars($applicant['email']); ?>)
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($applicant['applied_at'])); ?></td>
                    <td>**<?php echo htmlspecialchars(ucfirst($applicant['status'])); ?>**</td>
                    <td>
                        <a href="view_seeker_profile.php?seeker_id=<?php echo $applicant['seeker_id']; ?>">
                            ดูโปรไฟล์
                        </a>
                        |
                        <a href="manage_application.php?app_id=<?php echo $applicant['application_id']; ?>">
                            จัดการ
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<?php
// 6. เรียก "ส่วนท้าย"
require 'includes/footer.php';
?>