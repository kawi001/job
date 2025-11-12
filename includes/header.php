<?php
/*
 * ไฟล์: /includes/header.php
 * หน้าที่: ส่วนหัว, เมนู (ที่แยก Role), และ "ยาม"
 */

// เริ่ม session ในทุกหน้าที่เรียกใช้ header นี้
session_start();

// (1) เรียก "สะพาน" (config)
// (ไฟล์นี้อยู่ใน /includes/ เหมือนกัน เลยเรียกตรงๆ ได้)
require_once 'config.php';

// (2) "ยาม"
// ถ้าไม่มี "บัตรพนักงาน" (Session)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    // (สำคัญ!) ต้อง "ถอยหลัง" 1 ก้าว เพื่อไปหา login.php
    header("Location: ../job_match/login.php"); 
    exit;
}

// (3) (ถ้าผ่านยามมาได้) ดึงข้อมูล Session
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$email = $_SESSION['email'];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Job Match</title>
    <style>
        nav { background: #333; color: white; padding: 10px; display: flex; justify-content: space-between; }
        nav a { color: white; padding: 5px 10px; text-decoration: none; }
        .user-info { padding: 5px 10px; }
    </style>
</head>
<body>
    <nav>
        <div class="links">
            <a href="dashboard.php">หน้าหลัก</a>
            
            <?php if ($role_id == 1): // Role 1 = 'Job Seeker' ?>
                <a href="search.php">ค้นหางาน</a>
                <a href="my_applications.php">การสมัครของฉัน</a>
            <?php elseif ($role_id == 2): // Role 2 = 'Employer' ?>
                <a href="post_job.php">โพสต์งานใหม่</a>
                <a href="my_jobs.php">งานของฉัน</a>
            <?php endif; ?>
            
            <a href="profile.php">โปรไฟล์</a>
        </div>
        
        <div class="user-info">
            <?php echo htmlspecialchars($email); ?>
            <a href="logout.php">(ออกจากระบบ)</a>
        </div>
    </nav>
    <div class="content" style="padding: 20px;">