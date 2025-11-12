<?php
/*
 * ไฟล์: /profile.php
 * หน้าที่: หน้าแก้ไขโปรไฟล์ (ฉลาด: แยกฟอร์มตาม Role)
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. (สำคัญ!) ดึงข้อมูลโปรไฟล์ "ปัจจุบัน" มาเตรียมไว้
//    เราจะใช้ $user_id และ $role_id ที่ได้มาจาก header.php
$profile_data = null; // สร้างตัวแปรว่างไว้

if ($role_id == 1) {
    // ถ้าเป็น 'Job Seeker' -> ไปดึงจาก JOB_SEEKER_PROFILES
    $stmt = $pdo->prepare("SELECT * FROM JOB_SEEKER_PROFILES WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile_data = $stmt->fetch();

} elseif ($role_id == 2) {
    // ถ้าเป็น 'Employer' -> ไปดึงจาก SHOP_PROFILES
    $stmt = $pdo->prepare("SELECT * FROM SHOP_PROFILES WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile_data = $stmt->fetch();
}

// (ถ้าหาไม่เจอจริงๆ - ซึ่งไม่ควรเกิด)
if (!$profile_data) {
    die("เกิดข้อผิดพลาด: ไม่พบข้อมูลโปรไฟล์");
}

?>

<h1>แก้ไขโปรไฟล์</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e0ffe0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form action="profile_update.php" method="POST">

    <?php if ($role_id == 1): // ------------------- ฟอร์มสำหรับ 'Job Seeker' (Role 1) ?>
    
        <input type="hidden" name="role_type" value="seeker">
        
        <div>
            <label>ชื่อ-สกุล:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($profile_data['name']); ?>">
        </div>
        <div>
            <label>เบอร์โทร:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>">
        </div>
        <div>
            <label>ที่อยู่ (สำหรับคำนวณระยะทาง):</label>
            <textarea name="address"><?php echo htmlspecialchars($profile_data['address']); ?></textarea>
        </div>
        <div>
            <label>ทักษะ (เช่น เสิร์ฟ, ชงกาแฟ, ฯลฯ):</label>
            <input type="text" name="skills" value="<?php echo htmlspecialchars($profile_data['skills']); ?>">
        </div>
        <div>
            <label>ประสบการณ์:</label>
            <textarea name="experience"><?php echo htmlspecialchars($profile_data['experience']); ?></textarea>
        </div>
        <div>
            <label>Latitude (ตำแหน่งบ้าน):</label>
            <input type="text" name="latitude" value="<?php echo htmlspecialchars($profile_data['latitude']); ?>">
        </div>
        <div>
            <label>Longitude (ตำแหน่งบ้าน):</label>
            <input type="text" name="longitude" value="<?php echo htmlspecialchars($profile_data['longitude']); ?>">
        </div>

    <?php elseif ($role_id == 2): // ------------------- ฟอร์มสำหรับ 'Employer' (Role 2) ?>
    
        <input type="hidden" name="role_type" value="employer">
        
        <div>
            <label>ชื่อร้านค้า:</label>
            <input type="text" name="shop_name" value="<?php echo htmlspecialchars($profile_data['shop_name']); ?>">
        </div>
        <div>
            <label>เบอร์โทรร้าน:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>">
        </div>
        <div>
            <label>ที่อยู่ร้าน (สำหรับคำนวณระยะทาง):</label>
            <textarea name="address"><?php echo htmlspecialchars($profile_data['address']); ?></textarea>
        </div>
        <div>
            <label>คำอธิบายร้าน:</label>
            <textarea name="description"><?php echo htmlspecialchars($profile_data['description']); ?></textarea>
        </div>
        <div>
            <label>Latitude (ตำแหน่งร้าน):</label>
            <input type="text" name="latitude" value="<?php echo htmlspecialchars($profile_data['latitude']); ?>">
        </div>
        <div>
            <label>Longitude (ตำแหน่งร้าน):</label>
            <input type="text" name="longitude" value="<?php echo htmlspecialchars($profile_data['longitude']); ?>">
        </div>

    <?php endif; ?>
    
    <br>
    <button type="submit">บันทึกการเปลี่ยนแปลง</button>

</form>


<?php
// 3. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>