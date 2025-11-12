<?php
/*
 * ไฟล์: /apply_process.php
 * หน้าที่: "สมอง" (ไม่มีหน้าตา) รับการสมัครงาน
 */

// 1. เรียก "ยาม" และ "สะพาน"
session_start();
require 'includes/config.php';

// 2. (สำคัญ!) "ยาม"
// เช็กว่าล็อกอินหรือยัง และต้องเป็น Role 1 (Seeker) เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    $_SESSION['error'] = "คุณต้องเป็นผู้หางานเพื่อสมัครงาน";
    header("Location: login.php");
    exit;
}

// 3. (สำคัญ!) รับ ID งานจาก URL
if (!isset($_GET['job_id'])) {
    $_SESSION['error'] = "ไม่พบ ID งาน";
    header("Location: search.php");
    exit;
}
$job_id = $_GET['job_id'];
$user_id = $_SESSION['user_id']; // user_id จาก Session

// 4. (สำคัญมาก!) หา "seeker_id"
//    ตาราง APPLICATIONS ต้องการ "seeker_id"
//    แต่เรามีแค่ "user_id" (จาก Session)
try {
    $stmt_seeker = $pdo->prepare("SELECT seeker_id FROM JOB_SEEKER_PROFILES WHERE user_id = ?");
    $stmt_seeker->execute([$user_id]);
    $seeker = $stmt_seeker->fetch();

    if (!$seeker) {
        // (กรณีโปรไฟล์หายาก - ซึ่งไม่ควรเกิด)
        throw new Exception("ไม่พบโปรไฟล์ผู้หางานของคุณ");
    }
    
    $seeker_id = $seeker['seeker_id']; // นี่คือสิ่งที่เราต้องการ!

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    header("Location: search.php");
    exit;
}

// 5. (สำคัญ!) เช็กว่า "เคยสมัครงานนี้ไปหรือยัง"
try {
    $stmt_check = $pdo->prepare("SELECT application_id FROM APPLICATIONS WHERE seeker_id = ? AND job_id = ?");
    $stmt_check->execute([$seeker_id, $job_id]);
    
    if ($stmt_check->fetch()) {
        // ถ้า fetch แล้วเจอ = เคยสมัครแล้ว
        $_SESSION['error'] = "คุณได้สมัครงานนี้ไปแล้ว";
        header("Location: search.php"); // ส่งกลับไปหน้าค้นหา
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการตรวจสอบ: " . $e->getMessage();
    header("Location: search.php");
    exit;
}


// 6. (ขั้นสุดท้าย) ถ้าไม่เคยสมัคร -> INSERT ลงตาราง APPLICATIONS
try {
    $sql = "INSERT INTO APPLICATIONS (seeker_id, job_id, status) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $seeker_id,
        $job_id,
        'pending' // (สถานะเริ่มต้นคือ 'pending')
    ]);

    // (ถ้าสำเร็จ)
    $_SESSION['success'] = "สมัครงานสำเร็จ! สถานะของคุณคือ 'รอพิจารณา'";
    
    // (เราจะสร้างไฟล์ my_applications.php ต่อไป)
    header("Location: my_applications.php"); 
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "สมัครงานล้มเหลว: " . $e->getMessage();
    header("Location: job_view.php?id=" . $job_id); // ส่งกลับไปหน้าเดิม (พร้อม Error)
    exit;
}
?>