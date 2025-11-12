<?php
/*
 * ไฟล์: /search.php
 * หน้าที่: หน้ารวมงานทั้งหมดสำหรับ 'Job Seeker' (Role 1)
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. (สำคัญ!) "ยาม" เฉพาะทาง
// หน้านี้ควรสำหรับ Role 1 (Seeker)
if ($role_id != 1) {
    $_SESSION['error'] = "หน้านี้สำหรับผู้หางานเท่านั้น";
    header("Location: dashboard.php");
    exit;
}

// 3. (สำคัญ!) ดึงข้อมูลงานทั้งหมด
// เราต้อง "JOIN" ตารางเพื่อเอาชื่อร้าน + ชื่อหมวดหมู่
try {
    $sql = "SELECT 
                J.job_id, 
                J.job_title, 
                J.wage_per_hour,
                S.shop_name, 
                S.address AS shop_address,
                C.category_name
            FROM JOBS AS J
            JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
            JOIN CATEGORIES AS C ON J.category_id = C.category_id
            WHERE J.status = 'open' -- (เอาเฉพาะงานที่ยัง 'เปิด' รับ)
            ORDER BY J.created_at DESC"; // (เอางานใหม่สุดขึ้นก่อน)
            
    $stmt = $pdo->query($sql);
    $jobs = $stmt->fetchAll();

} catch (Exception $e) {
    $jobs = []; // ถ้าพัง ก็ให้เป็นค่าว่าง
    echo "<p style='color:red;'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}

?>

<h1>ค้นหางานพาร์ทไทม์</h1>
<p>พบงานทั้งหมด <?php echo count($jobs); ?> รายการ</p>

<style>
    .job-card { 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 15px; 
        margin-bottom: 15px; 
        background: #f9f9f9;
    }
    .job-card h3 { margin-top: 0; }
    .job-card .wage { color: green; font-weight: bold; font-size: 1.1em; }
    .job-card .details { color: #555; font-size: 0.9em; }
</style>


<div class="job-list">
    <?php if (empty($jobs)): ?>
        <p>ยังไม่มีงานที่เปิดรับในขณะนี้</p>
    <?php else: ?>
        <?php foreach ($jobs as $job): ?>
            
            <div class="job-card">
                <h3>
                    <a href="job_view.php?id=<?php echo $job['job_id']; ?>">
                        <?php echo htmlspecialchars($job['job_title']); ?>
                    </a>
                </h3>
                
                <div class="wage">
                    ฿<?php echo htmlspecialchars($job['wage_per_hour']); ?> / ชั่วโมง
                </div>
                
                <div class="details">
                    <strong>ร้าน:</strong> <?php echo htmlspecialchars($job['shop_name']); ?><br>
                    <strong>ที่อยู่:</strong> <?php echo htmlspecialchars($job['shop_address']); ?><br>
                    <strong>หมวดหมู่:</strong> <?php echo htmlspecialchars($job['category_name']); ?>
                </div>
            </div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php
// 5. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>