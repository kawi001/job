<?php
/*
 * ไฟล์: /manage_application.php
 * (หน้าที่สำหรับนายจ้างจัดการสถานะใบสมัคร - แก้ไขชื่อไฟล์ปลายทาง)
 */

require 'includes/config.php'; 
require 'includes/header.php'; // ไฟล์นี้ต้องมี session_start() และกำหนด $user_id, $role_id

// 1. "ยาม" (Role 2 และต้องมี app_id)
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (ต้องเป็น Role 2)");
}

$app_id = $_GET['app_id'] ?? die("ไม่ระบุ Application ID");
$current_user_id = $user_id;

// 2. ดึงข้อมูลใบสมัครและเช็กสิทธิ์
try {
    $stmt = $pdo->prepare("
        SELECT 
            A.application_id, A.status, A.applied_at,
            J.job_title, J.job_id,
            U.email, /* ใช้ email */
            JSP.name, /* ใช้ name จาก job_seeker_profiles */
            JSP.seeker_id
        FROM
            APPLICATIONS AS A
        JOIN
            JOBS AS J ON A.job_id = J.job_id
        JOIN
            JOB_SEEKER_PROFILES AS JSP ON A.seeker_id = JSP.seeker_id
        JOIN
            USERS AS U ON JSP.user_id = U.user_id
        JOIN
            SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE
            A.application_id = ? AND S.user_id = ?
    ");
    $stmt->execute([$app_id, $current_user_id]);
    $application = $stmt->fetch();

    if (!$application) {
        die("ไม่พบใบสมัครนี้ หรือคุณไม่ใช่เจ้าของงานที่เกี่ยวข้อง");
    }

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

?>

<h1>จัดการใบสมัครงาน: <?php echo htmlspecialchars($application['job_title']); ?></h1>
<p><a href="applications.php?job_id=<?php echo $application['job_id']; ?>">&lt; กลับไปหน้ารายชื่อผู้สมัคร</a></p>

---

## รายละเอียดใบสมัคร

<div style="background:#f0f0f0; padding: 15px; border-radius: 8px;">
    <p><strong>ผู้สมัคร:</strong> 
        <?php echo htmlspecialchars($application['name']); ?> 
        (<?php echo htmlspecialchars($application['email']); ?>)
    </p>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
    <p><strong>สมัครเมื่อ:</strong> <?php echo date('d/m/Y H:i', strtotime($application['applied_at'])); ?></p>
    <p>
        <a href="view_seeker_profile.php?seeker_id=<?php echo $application['seeker_id']; ?>">
            **ดูโปรไฟล์ผู้สมัครทั้งหมด &gt;**
        </a>
    </p>
</div>

<h2 style="margin-top: 20px;">สถานะปัจจุบัน: 
    <span style="color: <?php echo ($application['status'] == 'approved' ? 'green' : ($application['status'] == 'rejected' ? 'red' : 'orange')); ?>">
        <?php echo htmlspecialchars(ucfirst($application['status'])); ?>
    </span>
</h2>

---

<h2>เปลี่ยนสถานะ</h2>

<p>เลือกสถานะที่ต้องการเปลี่ยนสำหรับใบสมัครนี้:</p>

<form action="app_status_update.php" method="POST"> <input type="hidden" name="application_id" value="<?php echo $app_id; ?>">
    <input type="hidden" name="job_id" value="<?php echo $application['job_id']; ?>">
    
    <select name="new_status" required style="padding: 8px; margin-right: 10px;">
        <option value="pending" <?php echo ($application['status'] == 'pending' ? 'selected' : ''); ?>>Pending (รอพิจารณา)</option>
        <option value="approved" <?php echo ($application['status'] == 'approved' ? 'selected' : ''); ?>>Approved (อนุมัติ/รับเข้าทำงาน)</option>
        <option value="rejected" <?php echo ($application['status'] == 'rejected' ? 'selected' : ''); ?>>Rejected (ปฏิเสธ)</option>
    </select>
    
    <button type="submit" onclick="return confirm('ยืนยันการเปลี่ยนสถานะ?');">บันทึกสถานะใหม่</button>
</form>

<?php
// 3. เรียก "ส่วนท้าย"
require 'includes/footer.php';
?>