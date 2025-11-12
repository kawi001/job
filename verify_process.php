<?php
/*
 * ไฟล์: /job_match/verify_process.php
 * หน้าที่: "สมอง" ตรวจสอบ OTP (ฉบับแก้บั๊ก Timezone แล้ว)
 */
    
session_start();
    
// (Path ที่ถูกต้อง)
require 'includes/config.php'; // ($pdo)
    
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit;
}
    
$email = trim($_POST['email']);
$otp_code = trim($_POST['otp_code']);
    
try {
    // 1. หา User ID (เหมือนเดิม)
    $stmt_user = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
    $stmt_user->execute([$email]);
    $user = $stmt_user->fetch();

    if (!$user) {
        $_SESSION['error'] = "ไม่พบอีเมลนี้ในระบบ";
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit;
    }
    $user_id = $user['user_id'];

    //
    // VVVVV (นี่คือ "จุดที่ซ่อม") VVVVV
    //
    // 2. (เอาแฮ็กออก!) คืนชีพโค้ดเช็กเวลา
    //    ตอนนี้มันจะทำงานได้ เพราะ PHP (ที่ตั้งเวลา) กับ MySQL (NOW())
    //    จะใช้เวลา Asia/Bangkok เหมือนกันแล้ว
    $sql_check = "SELECT acc_id FROM ACCOUNT_VERIFICATIONS 
                  WHERE user_id = ? 
                  AND otp_code = ? 
                  AND otp_expiry > NOW()";  // <-- คืนชีพแล้ว!
    //
    // ^^^^^ (จบจุดที่ซ่อม) ^^^^^
    //
        
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$user_id, $otp_code]);
    $verification = $stmt_check->fetch();

    if ($verification) {
        // (ถ้าเจอ = OTP ถูกต้อง และ ไม่หมดอายุ)
            
        // 3. (ขั้นสุดท้าย) อัปเดตให้ "ยืนยันแล้ว"
        $sql_update = "UPDATE ACCOUNT_VERIFICATIONS 
                       SET is_verified = 1, 
                           otp_code = NULL, 
                           otp_expiry = NULL 
                       WHERE user_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$user_id]);

        // 4. (สำเร็จ!)
        $_SESSION['success'] = "ยืนยันบัญชีสำเร็จ! กรุณาเข้าสู่ระบบ";
        header("Location: login.php");
        exit;

    } else {
        // (ถ้าไม่เจอ = OTP ผิด หรือ "หมดอายุ" (จริงๆ))
        $_SESSION['error'] = "รหัส OTP ไม่ถูกต้อง หรือหมดอายุแล้ว";
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด (DB): " . $e->getMessage();
    header("Location: verify_otp.php?email=" . urlencode($email));
    exit;
}
?>