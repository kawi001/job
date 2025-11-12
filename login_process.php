<?php
/*
 * ไฟล์: /job_match/login_process.php (เวอร์ชันอัปเกรด OTP)
 * หน้าที่: ตรวจสอบรหัสผ่าน "และ" เช็กว่ายืนยัน OTP แล้ว (is_verified)
 */
    
session_start();
    
//
// VVVVV (แก้ Path - เหมือนเดิม) VVVVV
//
require 'includes/config.php'; // ($pdo)
//
// ^^^^^ (จบการแก้ Path) ^^^^^
//
    
// (เช็กว่าส่งมาแบบ POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit;
}
    
// (รับค่า)
$email = trim($_POST['email']);
$password = $_POST['password'];
    
try {
    //
    // VVVVV (จุดที่ 1: ผ่าตัด SQL) VVVVV
    //
    // เราต้อง "JOIN" เพื่อเอา "is_verified" มาเช็กด้วย
    $sql_check = "SELECT 
                    U.user_id, 
                    U.role_id, 
                    U.email, 
                    U.password_hash,
                    V.is_verified 
                  FROM USERS AS U
                  JOIN ACCOUNT_VERIFICATIONS AS V ON U.user_id = V.user_id
                  WHERE U.email = ?";
    
    $stmt = $pdo->prepare($sql_check);
    //
    // ^^^^^ (จบจุดที่ 1) ^^^^^
    //

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); 
    
    // 5. ตรวจสอบ "รหัสผ่าน"
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // (ถ้า "รหัสผ่านถูกต้อง")
        //
        // VVVVV (จุดที่ 2: ผ่าตัด Logic) VVVVV
        //
        // เช็กต่อว่า "ยืนยัน OTP หรือยัง" (is_verified == 1)
        if ($user['is_verified'] == 1) {
            
            // (ถ้า "ยืนยันแล้ว" -> ล็อกอินสำเร็จ)
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id']; 
            $_SESSION['email'] = $user['email'];
            
            header("Location: dashboard.php");
            exit;

        } else {
            
            // (ถ้า "ยังไม่ยืนยัน")
            $_SESSION['error'] = "รหัสผ่านถูกต้อง! แต่คุณยังไม่ยืนยัน OTP (กรุณาเช็กอีเมล)";
            // (ส่งกลับไปหน้ากรอก OTP แทน)
            header("Location: verify_otp.php?email=" . urlencode($email));
            exit;
        }
        //
        // ^^^^^ (จบจุดที่ 2) ^^^^^
        //

    } else {
        // (ถ้า "รหัสผ่านผิด" หรือ "อีเมลผิด")
        $_SESSION['error'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
        header("Location: login.php");
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด (DB): " . $e->getMessage();
    header("Location: login.php");
    exit;
}
?>