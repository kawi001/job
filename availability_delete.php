<?php
/*
 * ไฟล์: /availability_delete.php
 * หน้าที่: "สมอง" (ไม่มีหน้าตา) รับ "ID เวลา" มา DELETE
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (ต้องล็อกอิน และเป็น Role 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

// 2. รับ ID ที่จะลบ (จาก URL ...?id=...)
if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}
$availability_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. (สำคัญมาก!) "เช็กความปลอดภัย"
//    เราต้องเช็กว่า availability_id นี้ "เป็นของเราจริง"
try {
    // 3.1 หา seeker_id ของเรา
    $stmt_seeker = $pdo->prepare("SELECT seeker_id FROM JOB_SEEKER_PROFILES WHERE user_id = ?");
    $stmt_seeker->execute([$user_id]);
    $seeker = $stmt_seeker->fetch();
    
    if (!$seeker) {
        throw new Exception("ไม่พบโปรไฟล์ Seeker");
    }
    $my_seeker_id = $seeker['seeker_id'];

    // 3.2 (ขั้นสุดท้าย) ยิง DELETE
    // (เราจะ DELETE "ก็ต่อเมื่อ" ID และ seeker_id ของเราตรงกัน)
    $sql = "DELETE FROM SEEKER_AVAILABILITY 
            WHERE availability_id = ? AND seeker_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$availability_id, $my_seeker_id]);

    // (เช็กว่าลบสำเร็จไหม (rowCount > 0 คือลบสำเร็จ))
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "ลบเวลาว่างสำเร็จ!";
    } else {
        $_SESSION['error'] = "ไม่พบเวลาที่ต้องการลบ หรือคุณไม่ใช่เจ้าของ";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "ลบเวลาล้มเหลว: " . $e->getMessage();
}

// 4. ส่งกลับไปหน้าโปรไฟล์ (ที่เดิม)
header("Location: profile.php");
exit;
?>