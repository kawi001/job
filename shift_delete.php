<?php
/*
 * ไฟล์: /shift_delete.php
 * (เวอร์ชัน "แก้ไข" - แก้บั๊กชื่อคอลัมน์)
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (Role 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit;
}

// 2. รับ ID กะ (Shift) ที่จะลบ
if (!isset($_GET['shift_id'])) {
    header("Location: dashboard.php");
    exit;
}
$shift_id = $_GET['shift_id'];
$user_id = $_SESSION['user_id'];
$job_id_redirect = null; 

// 3. (เช็กความปลอดภัย)
try {
    
    // 3.1 หาว่า "กะ" นี้ เป็นของเราจริงไหม
    $stmt_check = $pdo->prepare("
        SELECT T.job_id
        FROM JOB_REQUIRED_SHIFTS AS T
        JOIN JOBS AS J ON T.job_id = J.job_id
        JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE T.shift_id = ? AND S.user_id = ? /* <--- แก้ไขจุดที่ 1: เปลี่ยนเป็น T.shift_id */
    ");
    $stmt_check->execute([$shift_id, $user_id]);
    $shift_owner = $stmt_check->fetch();

    if ($shift_owner) {
        // (ถ้าเจอ = เราเป็นเจ้าของจริง)
        $job_id_redirect = $shift_owner['job_id']; // (เก็บ Job ID ไว้ "เด้งกลับ")

        // 3.2 ยิง DELETE
        $sql = "DELETE FROM JOB_REQUIRED_SHIFTS WHERE shift_id = ?"; /* <--- แก้ไขจุดที่ 2: เปลี่ยนเป็น shift_id */
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$shift_id]);

        $_SESSION['success'] = "ลบกะสำเร็จ!";
        
    } else {
        // (ถ้าไม่เจอ = เราไม่ใช่เจ้าของ)
        $_SESSION['error'] = "ไม่พบกะที่ต้องการลบ หรือคุณไม่ใช่เจ้าของ";
        $job_id_redirect = 0; 
    }

} catch (Exception $e) {
    $_SESSION['error'] = "ลบกะล้มเหลว: " . $e->getMessage();
    $job_id_redirect = 0;
}

// 4. ส่งกลับไปหน้า "แก้ไขงาน"
if ($job_id_redirect) {
    header("Location: edit_job.php?job_id=" . $job_id_redirect);
} else {
    header("Location: dashboard.php"); // (ถ้าพังมาก ก็กลับ Dashboard)
}
exit;
?>