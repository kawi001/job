<?php
/*
 * ไฟล์: /job_match/forgot_process.php
 * (เวอร์ชันอัปเกรด: ถ้าส่งเมลสำเร็จ -> เด้งไปหน้า Login)
 */

session_start();

// (Path ที่ถูกต้อง สำหรับโครงสร้าง "แบน")
require 'includes/config.php'; 

// (เรียก PHPMailer แบบ Manual)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgot_password.php"); exit;
}

$email = trim($_POST['email']);

try {
    $stmt_user = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
    $stmt_user->execute([$email]);
    $user = $stmt_user->fetch();

    if ($user) {
        $user_id = $user['user_id'];
        $token = bin2hex(random_bytes(32)); 
        $expiry = date("Y-m-d H:i:s", time() + 3600); 

        $sql_update = "UPDATE ACCOUNT_VERIFICATIONS SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$token, $expiry, $user_id]);

        $mail = new PHPMailer(true); 

        try {
            // (ตั้งค่า Server - *ต้องใส่รหัสผ่านแอป 16 หลักของคุณ*)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kawi7575@gmail.com';     // (อีเมลคุณ)
            $mail->Password   = 'pzst jrpm nypj mypj';     // (ใส่ "รหัสผ่านแอป")
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('kawi7575@gmail.com', 'Job Match Admin');
            $mail->addAddress($email);

            // (Path ลิงก์ที่ถูกต้อง)
            $reset_link = "http://localhost/job_match/reset_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'คำขอรีเซ็ตรหัสผ่านสำหรับ Job Match';
            $mail->Body    = "คลิกลิงก์นี้เพื่อรีเซ็ต (มีอายุ 1 ชั่วโมง):<br><a href='$reset_link'>$reset_link</a>";

            $mail->send();
            
            //
            // VVVVV (นี่คือ "จุดที่แก้") VVVVV
            //
            $_SESSION['success'] = "ส่งลิงก์รีเซ็ตไปที่ $email แล้ว (กรุณาเช็ก Junk Mail)";
            header("Location: login.php"); // <--- เปลี่ยนจาก forgot_password.php
            exit;
            //
            // ^^^^^ (จบจุดที่แก้) ^^^^^
            //

        } catch (Exception $e) {
            // (ถ้าส่งเมล "พัง")
            $_SESSION['error'] = "ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}";
            header("Location: forgot_password.php"); // <--- ให้มันอยู่หน้าเดิม (เพื่อโชว์ Error)
            exit;
        }
        
    } else {
        // (ถ้า "ไม่เจอ" อีเมล... เราก็แกล้งๆ ส่งไปหน้า login เหมือนกัน)
        //
        // VVVVV (นี่คือ "จุดที่แก้") VVVVV
        //
        $_SESSION['success'] = "หากอีเมลนี้มีในระบบ เราได้ส่งลิงก์ไปให้แล้ว";
        header("Location: login.php"); // <--- เปลี่ยนจาก forgot_password.php
        exit;
        //
        // ^^^^^ (จบจุดที่แก้) ^^^^^
        //
    }

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    header("Location: forgot_password.php"); // <--- ถ้า DB พัง ให้มันอยู่หน้าเดิม
    exit;
}
?>