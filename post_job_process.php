<?php
/*
 * ไฟล์: /post_job_process.php
 * หน้าที่: "สมอง" รับค่าจาก post_job.php แล้ว INSERT ลง DB
 */

// 1. เรียก "ยาม" และ "สะพาน"
session_start();
require 'includes/config.php';

// 2. เช็กว่าล็อกอิน + เป็น Role 2 จริง
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์ดำเนินการ";
    header("Location: dashboard.php");
    exit;
}

// 3. เช็กว่าส่งมาแบบ POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: post_job.php");
    exit;
}

// 4. ดึง ID จาก Session
$user_id = $_SESSION['user_id'];

// 5. (สำคัญมาก!) หา "shop_id" ของร้านค้า
//    ตาราง JOBS ต้องการ "shop_id"
//    แต่เรามีแค่ "user_id" (จาก Session)
try {
    $stmt_shop = $pdo->prepare("SELECT shop_id FROM SHOP_PROFILES WHERE user_id = ?");
    $stmt_shop->execute([$user_id]);
    $shop = $stmt_shop->fetch();

    if (!$shop) {
        throw new Exception("ไม่พบโปรไฟล์ร้านค้าของคุณ");
    }
    
    $shop_id = $shop['shop_id']; // นี่คือสิ่งที่เราต้องการ!

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการค้นหา shop_id: " . $e->getMessage();
    header("Location: post_job.php");
    exit;
}

// 6. (สำเร็จ!) รับข้อมูลจากฟอร์ม
$job_title = trim($_POST['job_title']);
$category_id = $_POST['category_id'];
$description = trim($_POST['description']);
$wage_per_hour = $_POST['wage_per_hour'];
$num_positions = $_POST['num_positions'];

// 7. (ขั้นสุดท้าย) INSERT ข้อมูลลงตาราง JOBS
try {
    $sql = "INSERT INTO JOBS 
                (shop_id, category_id, job_title, description, wage_per_hour, num_positions) 
            VALUES 
                (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $shop_id,
        $category_id,
        $job_title,
        $description,
        $wage_per_hour,
        $num_positions
    ]);

    // (ถ้าสำเร็จ)
    $_SESSION['success'] = "โพสต์งานสำเร็จ!";
    // (เราจะสร้างไฟล์ my_jobs.php ทีหลัง)
    header("Location: dashboard.php"); // ส่งกลับไปหน้าหลักก่อน
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "โพสต์งานล้มเหลว: " . $e->getMessage();
    header("Location: post_job.php"); // ส่งกลับไปหน้าฟอร์ม (พร้อม Error)
    exit;
}
?>