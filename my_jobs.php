<?php
/*
 * ไฟล์: /my_jobs.php
 * หน้าที่: (Role 2) ดูรายการงานที่ตัวเองโพสต์ + จำนวนคนสมัคร
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. "ยาม" เฉพาะทาง (Role 2 เท่านั้น)
if ($role_id != 2) {
    $_SESSION['error'] = "หน้านี้สำหรับผู้จ้างงานเท่านั้น";
    header("Location: dashboard.php");
    exit;
}

// 3. หา "shop_id" (เพราะเราจะใช้ shop_id ค้นหา JOBS)
try {
    $stmt_shop = $pdo->prepare("SELECT shop_id FROM SHOP_PROFILES WHERE user_id = ?");
    $stmt_shop->execute([$user_id]);
    $shop = $stmt_shop->fetch();
    $shop_id = $shop['shop_id'];
} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการหา shop_id");
}

// 4. (ไฮไลท์!) ดึงงานของร้านนี้ + "นับ" (COUNT) จำนวนใบสมัคร
try {
    $sql = "SELECT 
                J.job_id,
                J.job_title,
                J.status,
                J.created_at,
                (SELECT COUNT(A.application_id) 
                 FROM APPLICATIONS AS A 
                 WHERE A.job_id = J.job_id AND A.status = 'pending') AS pending_count
            FROM JOBS AS J
            WHERE J.shop_id = ?
            ORDER BY J.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$shop_id]);
    $jobs = $stmt->fetchAll();

} catch (Exception $e) {
    $jobs = [];
    echo "<p style='color:red;'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>

<h1>งานที่ฉันโพสต์</h1>

<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .pending-count { 
        background: #ffc107; color: #333; 
        padding: 2px 8px; border-radius: 10px; 
        font-weight: bold;
    }
    .status-closed { color: #888; text-decoration: line-through; }
</style>

<table>
    <thead>
        <tr>
            <th>ชื่องาน</th>
            <th>สถานะ</th>
            <th>วันที่โพสต์</th>
            <th>ใบสมัครใหม่ (รอพิจารณา)</th>
            <th>จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($jobs)): ?>
            <tr>
                <td colspan="5">คุณยังไม่ได้โพสต์งานใดๆ (ไปที่ <a href="post_job.php">โพสต์งานใหม่</a>)</td>
            </tr>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                    <td>
                        <span class="status-<?php echo strtolower($job['status']); ?>">
                            <?php echo htmlspecialchars($job['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $job['created_at']; ?></td>
                    <td>
                        <?php if ($job['pending_count'] > 0): ?>
                            <span class="pending-count">
                                <?php echo $job['pending_count']; ?> คน
                            </span>
                        <?php else: ?>
                            0 คน
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="view_applicants.php?job_id=<?php echo $job['job_id']; ?>">
                            ดูใบสมัคร
                        </a>
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