<?php
/*
 * ไฟล์: /profile_update.php
 * (เวอร์ชัน "ยาม" เช็ก Lat/Lon ว่า "ว่าง" หรือไม่)
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (ต้องล็อกอิน)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: profile.php");
    exit;
}

// 2. ดึง ID และ Role จาก Session
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

//
// VVVVV (นี่คือ "ยาม" ที่สำคัญ) VVVVV
//
// 3. ดึง Lat/Lon ที่ส่งมาจากฟอร์ม (แผนที่)
$latitude = trim($_POST['latitude']);
$longitude = trim($_POST['longitude']);

// 4. (สำคัญ!) เช็กว่ามัน "ว่าง" หรือไม่
// (เราจะ "ไม่ยอม" ให้ค่า "ว่าง" บันทึก)
if (empty($latitude) || empty($longitude)) {
    
    // (ถ้ามันว่าง -> แปลว่า "ไม่ได้ปัก")
    $_SESSION['error'] = "บันทึกล้มเหลว: กรุณา 'คลิก/ค้นหา/ลากหมุด' ปักตำแหน่ง (บ้าน/ร้าน) ของคุณบนแผนที่ก่อน!";
    
    // "ถีบ" กลับไปหน้าเดิม
    header("Location: profile.php");
    exit;
}
//
// ^^^^^ (จบ "ยาม") ^^^^^
//


// 5. (ถ้าผ่านยามมาได้) แยก Logic การ UPDATE (เหมือนเดิม)
try {

    if ($role_id == 1 && $_POST['role_type'] == 'seeker') {
        // อัปเดต 'Job Seeker' (Role 1)
        $sql = "UPDATE JOB_SEEKER_PROFILES SET
                    name = ?, phone = ?, address = ?, skills = ?, 
                    experience = ?, latitude = ?, longitude = ?
                WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['name']), trim($_POST['phone']), trim($_POST['address']),
            trim($_POST['skills']), trim($_POST['experience']),
            $latitude, $longitude, // (ใช้ค่าที่ "ผ่านยาม" มา)
            $user_id 
        ]);

    } elseif ($role_id == 2 && $_POST['role_type'] == 'employer') {
        // อัปเดต 'Employer' (Role 2)
        $sql = "UPDATE SHOP_PROFILES SET
                    shop_name = ?, phone = ?, address = ?, 
                    description = ?, latitude = ?, longitude = ?
                WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['shop_name']), trim($_POST['phone']), trim($_POST['address']),
            trim($_POST['description']),
            $latitude, $longitude, // (ใช้ค่าที่ "ผ่านยาม" มา)
            $user_id
        ]);
        
    } else {
        throw new Exception("Role ไม่ตรงกัน");
    }

    $_SESSION['success'] = "อัปเดตโปรไฟล์ (และตำแหน่ง) สำเร็จ!";

} catch (Exception $e) {
    $_SESSION['error'] = "อัปเดตล้มเหลว: " . $e->getMessage();
}

// 6. ส่งกลับไปหน้า profile.php
header("Location: profile.php");
exit;
?>