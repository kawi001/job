<?php
/*
 * ไฟล์: /profile_update.php
 * หน้าที่: "สมอง" รับค่าจาก profile.php แล้ว UPDATE ลง DB
 */

// 1. เรียก "ยาม" และ "สะพาน"
// (เราต้องเริ่ม session และ config เอง เพราะไฟล์นี้ไม่มีหน้าตา)
session_start();
require 'includes/config.php';

// 2. เช็กว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 3. เช็กว่าส่งมาแบบ POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: profile.php");
    exit;
}

// 4. ดึง ID และ Role จาก Session
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// 5. (สำคัญ!) แยก Logic การ UPDATE ตาม Role
try {

    if ($role_id == 1 && $_POST['role_type'] == 'seeker') {
        // อัปเดต 'Job Seeker' (Role 1)
        
        $sql = "UPDATE JOB_SEEKER_PROFILES SET
                    name = ?,
                    phone = ?,
                    address = ?,
                    skills = ?,
                    experience = ?,
                    latitude = ?,
                    longitude = ?
                WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['phone']),
            trim($_POST['address']),
            trim($_POST['skills']),
            trim($_POST['experience']),
            trim($_POST['latitude']),
            trim($_POST['longitude']),
            $user_id // (WHERE user_id = ?)
        ]);

    } elseif ($role_id == 2 && $_POST['role_type'] == 'employer') {
        // อัปเดต 'Employer' (Role 2)
        
        $sql = "UPDATE SHOP_PROFILES SET
                    shop_name = ?,
                    phone = ?,
                    address = ?,
                    description = ?,
                    latitude = ?,
                    longitude = ?
                WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['shop_name']),
            trim($_POST['phone']),
            trim($_POST['address']),
            trim($_POST['description']),
            trim($_POST['latitude']),
            trim($_POST['longitude']),
            $user_id // (WHERE user_id = ?)
        ]);
        
    } else {
        // (กันโดนยิงมั่ว)
        throw new Exception("Role ไม่ตรงกัน");
    }

    // 6. (สำเร็จ!) ตั้งข้อความ Success
    $_SESSION['success'] = "อัปเดตโปรไฟล์สำเร็จ!";

} catch (Exception $e) {
    // 7. (ล้มเหลว!) ตั้งข้อความ Error
    $_SESSION['error'] = "อัปเดตล้มเหลว: " . $e->getMessage();
}

// 8. ส่งกลับไปหน้า profile.php (ไม่ว่าจะสำเร็จหรือล้มเหลว)
header("Location: profile.php");
exit;

?>