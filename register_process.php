<?php
/*
 * ไฟล์: /job_match/register_process.php (เวอร์ชันอัปเกรด OTP)
 * หน้าที่: รับสมัคร, สร้าง OTP, "ส่งอีเมล" OTP, ส่งไปหน้ายืนยัน
 */

session_start();

// VVVVV (จุดที่ 1: เรียกเครื่องมือ) VVVVV

// (1.1) เรียก "สะพาน" (แก้ Path)
require 'includes/config.php'; // ($pdo)

// (1.2) เรียก Class ของ PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// (1.3) เรียกไฟล์ PHPMailer แบบ Manual (ตามโครงสร้างของคุณ)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// ^^^^^ (จบจุดที่ 1) ^^^^^


// (เช็กว่าส่งมาแบบ POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register.php"); 
    exit;
}

// (รับข้อมูล)
$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = $_POST['password']; 
$role_id  = $_POST['role_id'];

// (เช็ก Email ซ้ำ)
$stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "อีเมลนี้ถูกใช้งานแล้ว";
    header("Location: register.php");
    exit;
}

// (Hash รหัสผ่าน)
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// (สร้าง OTP)
$otp_code = rand(100000, 999999); // สุ่มเลข 6 หลัก
$otp_expiry = date("Y-m-d H:i:s", time() + 600); // หมดอายุใน 10 นาที


// (เริ่ม Transaction)
try {
    $pdo->beginTransaction();

    // 1: INSERT ลง USERS
    $stmt_user = $pdo->prepare("INSERT INTO USERS (email, password_hash, role_id) VALUES (?, ?, ?)");
    $stmt_user->execute([$email, $hashed_password, $role_id]);
    $user_id = $pdo->lastInsertId();

    // 2: (สำคัญ!) INSERT ลง ACCOUNT_VERIFICATIONS (พร้อม OTP)
    // (เราจะตั้ง is_verified = 0 (FALSE) โดยอัตโนมัติ)
    $stmt_ver = $pdo->prepare("INSERT INTO ACCOUNT_VERIFICATIONS (user_id, otp_code, otp_expiry) VALUES (?, ?, ?)");
    $stmt_ver->execute([$user_id, $otp_code, $otp_expiry]);

    // 3: "แยก Role" (เหมือนเดิม)
    if ($role_id == 1) {
        $stmt_profile = $pdo->prepare("INSERT INTO JOB_SEEKER_PROFILES (user_id, name) VALUES (?, ?)");
        $stmt_profile->execute([$user_id, $name]);
    } elseif ($role_id == 2) {
        $stmt_profile = $pdo->prepare("INSERT INTO SHOP_PROFILES (user_id, shop_name) VALUES (?, ?)");
        $stmt_profile->execute([$user_id, $name]);
    }

    // (สำเร็จ!) ยืนยัน DB
    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "การลงทะเบียนล้มเหลว (DB): " . $e->getMessage();
    header("Location: register.php");
    exit;
}

//
// VVVVV (จุดที่ 2: "ส่งอีเมล" OTP) VVVVV
//
$mail = new PHPMailer(true); 
try {
    // (ตั้งค่า Server - *ต้องใส่รหัสผ่านแอป 16 หลักของคุณ*)
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kawi7575@gmail.com';     // (ใส่อีเมล Gmail ของคุณ)
    $mail->Password   = 'pzst jrpm nypj mypj';     // (ใส่ "รหัสผ่านแอป")
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // (ผู้ส่ง / ผู้รับ)
    $mail->setFrom('kawi7575@gmail.com', 'Job Match Admin');
    $mail->addAddress($email);

    // (เนื้อหาอีเมล)
    $mail->isHTML(true);
    $mail->Subject = 'ยืนยันการสมัคร Job Match (OTP)';
    $mail->Body    = "สวัสดีครับ,<br><br>"
                   . "ขอบคุณที่สมัคร Job Match<br>"
                   . "รหัส OTP 6 หลักของคุณคือ: <h2>$otp_code</h2>"
                   . "รหัสนี้มีอายุ 10 นาที<br>";

    // (ยิง!)
    $mail->send();
    
    // (ถ้าส่งสำเร็จ)
    // VVVVV (จุดที่ 3: "เปลี่ยนที่ส่ง") VVVVV
    
    $_SESSION['success'] = "สมัครสำเร็จ! กรุณาเช็ก OTP (รหัส 6 หลัก) ที่อีเมล $email";
    // (เราจะส่ง user ไปหน้า "กรอก OTP" และส่ง email ไปด้วย)
    header("Location: verify_otp.php?email=" . urlencode($email));
    exit;
    
    // ^^^^^ (จบจุดที่ 3) ^^^^^

} catch (Exception $e) {
    // (ถ้า PHPMailer ส่งไม่ผ่าน)
    // (หมายเหตุ: User ถูกสร้างไปแล้ว แต่จะล็อกอินไม่ได้จนกว่าจะยืนยัน)
    $_SESSION['error'] = "สมัครสำเร็จ แต่ส่ง OTP ล้มเหลว: {$mail->ErrorInfo} (ลองติดต่อ Admin)";
    header("Location: login.php"); // ส่งไปหน้า login พร้อม error
    exit;
}
?>
```