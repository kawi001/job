<?php
/*
 * ไฟล์: /post_job.php
 * (เวอร์ชัน "ทำใหม่" - แก้ให้ตรง DB)
 */

// 1. เรียก "ส่วนหัว"
require 'includes/header.php'; 

// 2. "ยาม" (ต้องเป็น Role 2)
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (ต้องเป็น Role 2)");
}

// 3. ดึง shop_id
$stmt_shop = $pdo->prepare("SELECT shop_id FROM SHOP_PROFILES WHERE user_id = ?");
$stmt_shop->execute([$user_id]);
$shop = $stmt_shop->fetch();

if (!$shop) {
    die("ไม่พบโปรไฟล์ร้านค้าของคุณ");
}
$shop_id = $shop['shop_id'];

?>

<h1>โพสต์งานใหม่</h1>
<p>ขั้นตอนที่ 1: กรอกรายละเอียดงานหลัก (เดี๋ยวเราจะไป "เพิ่มกะงาน" ในขั้นตอนถัดไป)</p>

<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form action="post_job_process.php" method="POST" style="background:#f9f9f9; padding:15px; border-radius:8px;">
    
    <input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
    
    <div>
        <label>ชื่องาน (เช่น: พนักงานเสิร์ฟ):</label>
        <input type="text" name="title" required style="width: 100%;">
    </div>
    <div>
        <label>คำอธิบายงาน (เช่น: ทำอะไรบ้าง):</label>
        <textarea name="description" style="width: 100%; height: 100px;"></textarea>
    </div>
    <div>
        <label>ค่าจ้าง/ชั่วโมง (เช่น: 120):</label>
        <input type="text" name="wage_per_hour" style="width: 100%;">
    </div>
    
    <button type="submit">บันทึก และไปขั้นตอนถัดไป (เพิ่มกะ)</button>
</form>

<?php
// 4. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>