<?php
/*
 * ไฟล์: /my_jobs.php
 * (ศูนย์รวมงานทั้งหมดของนายจ้าง)
 */

// 1. เรียก "ส่วนหัว"
require 'includes/header.php'; 

// 2. "ยาม" (ต้องเป็น Role 2: นายจ้าง)
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (หน้านี้สำหรับนายจ้างเท่านั้น)");
}

$current_user_id = $user_id;

// 3. ดึงข้อมูลงานทั้งหมดที่เป็นของ User คนนี้
try {
    $stmt_jobs = $pdo->prepare("
        SELECT 
            J.job_id, 
            J.job_title, 
            J.status, 
            S.shop_name,
            (
                SELECT COUNT(*) FROM APPLICATIONS AS A WHERE A.job_id = J.job_id
            ) AS applicant_count
        FROM 
            JOBS AS J
        JOIN 
            SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE 
            S.user_id = ?
        ORDER BY 
            J.job_id DESC
    ");
    $stmt_jobs->execute([$current_user_id]);
    $my_jobs = $stmt_jobs->fetchAll();

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลงาน: " . $e->getMessage());
}
?>

<h1>งานที่ฉันโพสต์ทั้งหมด</h1>
<p>จัดการรายละเอียดงาน กะงาน และดูรายชื่อผู้สมัครได้ที่นี่</p>

<?php if (empty($my_jobs)): ?>
    
    <div style="background: #fff8e1; padding: 15px; border-radius: 8px;">
        <h4>คุณยังไม่ได้โพสต์งานใดๆ</h4>
        <p>กรุณาไปที่หน้า <a href="post_job.php">โพสต์งานใหม่</a> เพื่อสร้างประกาศงานแรกของคุณ</p>
    </div>

<?php else: ?>

    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead> 
            <tr style="background: #f0f0f0;"> 
                <th>ชื่องาน</th> 
                <th>ร้านค้า</th> 
                <th>สถานะ</th> 
                <th>ผู้สมัคร</th> 
                <th>เครื่องมือจัดการ</th> 
            </tr> 
        </thead>
        <tbody>
            <?php foreach ($my_jobs as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                    <td><?php echo htmlspecialchars($job['shop_name']); ?></td>
                    <td>
                        <span style="color: <?php echo ($job['status'] == 'open' ? 'green' : 'red'); ?>; font-weight: bold;">
                            <?php echo htmlspecialchars(ucfirst($job['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($job['applicant_count']); ?> คน</td>
                    <td>
                        <a href="edit_job.php?job_id=<?php echo $job['job_id']; ?>">จัดการกะงาน</a>
                        |
                        <a href="applications.php?job_id=<?php echo $job['job_id']; ?>">ดูผู้สมัคร</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>


<?php
// 4. เรียก "ส่วนท้าย"
require 'includes/footer.php';
?>