<?php
/*
 * ไฟล์: /includes/header.php
 * (เวอร์ชัน "ขั้นสุด": โหลด Leaflet + ปลั๊กอิน GeoSearch)
 */

session_start();
require_once 'config.php'; // (Path ถูกต้อง)

// "ยาม" (ต้องล็อกอิน)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
    header("Location: login.php"); 
    exit;
}

// (ดึงข้อมูล Session)
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$email = $_SESSION['email'];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Match</title>
    <style>
        nav { background: #333; color: white; padding: 10px; display: flex; justify-content: space-between; }
        nav a { color: white; padding: 5px 10px; text-decoration: none; }
        .user-info { padding: 5px 10px; }
    </style>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@^3/dist/geosearch.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet-geosearch@^3/dist/geosearch.umd.js" crossorigin=""></script>
    </head>
<body>
    <nav>
        <div class="links">
            <a href="dashboard.php">หน้าหลัก</a>
            <?php if ($role_id == 1): ?>
                <a href="search.php">ค้นหางาน</a>
                <a href="my_applications.php">การสมัครของฉัน</a>
            <?php elseif ($role_id == 2): ?>
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