<?php
/*
 * ไฟล์: /job_view.php
 * หน้าที่: แสดงรายละเอียดงาน 1 ชิ้น และปุ่ม "สมัครงาน"
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. (สำคัญ!) รับ ID งานจาก URL
// (เช่น job_view.php?id=3)
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ไม่พบ ID งาน";
    header("Location: search.php");
    exit;
}
$job_id = $_GET['id'];

// 3. (สำคัญ!) ดึงข้อมูลงานชิ้นนี้ (JOIN เหมือนเดิม)
try {
    $sql = "SELECT 
                J.*, -- (เอาทุกคอลัมน์จาก JOBS)
                S.shop_name, 
                S.address AS shop_address,
                S.description AS shop_description,
                C.category_name
            FROM JOBS AS J
            JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
            JOIN CATEGORIES AS C ON J.category_id = C.category_id
            WHERE J.job_id = ? AND J.status = 'open'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(); // ดึงมาแค่แถวเดียว

    if (!$job) {
        // (ถ้าไม่มี ID นี้ หรือ งานปิดไปแล้ว)
        $_SESSION['error'] = "ไม่พบข้อมูลงามที่ระบุ หรือ งานปิดรับแล้ว";
        header("Location: search.php");
        exit;
    }

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    header("Location: search.php");
    exit;
}

?>

<style>
    /* (CSS ชั่วคราว) */
    .job-header { background: #eee; padding: 20px; border-radius: 8px; }
    .wage-box { font-size: 1.5em; font-weight: bold; color: green; margin: 10px 0; }
    .apply-button { 
        background: #28a745; color: white; padding: 12px 20px; 
        text-decoration: none; border-radius: 5px; font-size: 1.1em;
    }
</style>

<div class="job-header">
    <h1><?php echo htmlspecialchars($job['job_title']); ?></h1>
    <h3>ร้าน: <?php echo htmlspecialchars($job['shop_name']); ?></h3>
    <p><strong>หมวดหมู่:</strong> <?php echo htmlspecialchars($job['category_name']); ?></p>
</div>

<div class="wage-box">
    ฿<?php echo htmlspecialchars($job['wage_per_hour']); ?> / ชั่วโมง
</div>

<h3>รายละเอียดงาน</h3>
<p><?php echo nl2br(htmlspecialchars($job['description'])); // (nl2br คือให้แสดงผลขึ้นบรรทัดใหม่) ?></p>

<h3>เกี่ยวกับร้าน</h3>
<p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($job['shop_address']); ?></p>
<p><?php echo nl2br(htmlspecialchars($job['shop_description'])); ?></p>

<hr>

<?php if ($role_id == 1): // (เช็กอีกครั้งว่าคนดูคือ Role 1) ?>
    
    <a href="apply_process.php?job_id=<?php echo $job['job_id']; ?>" 
       class="apply-button"
       onclick="return confirm('คุณต้องการยืนยันสมัครงานนี้หรือไม่?');">
       ยืนยันสมัครงานนี้
    </a>
    
<?php endif; ?>


<?php
// 4. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>