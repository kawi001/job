<?php
/*
 * ไฟล์: /reset_process.php
 * หน้าที่: "สมอง" รับรหัสใหม่ + Token มาอัปเดตลง DB จริง
 */
    
session_start();
require 'includes/config.php'; // ($pdo)

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php"); exit;
}

// 1. รับค่า
$token = $_POST['token'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// 2. เช็กรหัสผ่าน
if ($password !== $confirm_password) {
    $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
    // (ส่ง Token กลับไปด้วย)
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

// 3. (สำคัญ!) เช็ก Token รอบสุดท้าย
try {
    $sql_check = "SELECT user_id FROM ACCOUNT_VERIFICATIONS 
                  WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$token]);
    $verification = $stmt_check->fetch();

    if (!$verification) {
        die("Token ไม่ถูกต้อง หรือหมดอายุ (รอบ 2)");
    }
    
    $user_id = $verification['user_id'];

    // 4. (ถ้า Token ถูก) Hash รหัสใหม่
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // 5. (Transaction) อัปเดต DB
    $pdo->beginTransaction();
    
    // 5.1: อัปเดตรหัสใหม่ลง USERS
    $stmt_user = $pdo->prepare("UPDATE USERS SET password_hash = ? WHERE user_id = ?");
    $stmt_user->execute([$hashed_password, $user_id]);

    // 5.2: (สำคัญ!) "ล้าง" Token ทิ้ง (กันใช้ซ้ำ)
    $stmt_clear = $pdo->prepare("UPDATE ACCOUNT_VERIFICATIONS 
                                SET reset_token = NULL, reset_token_expiry = NULL 
                                WHERE user_id = ?");
    $stmt_clear->execute([$user_id]);
    
    $pdo->commit();
    
    // 6. (สำเร็จ!)
    $_SESSION['success'] = "เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบด้วยรหัสใหม่";
    header("Location: login.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("เกิดข้อผิดพลาดร้ายแรง: " . $e->getMessage());
}
?>