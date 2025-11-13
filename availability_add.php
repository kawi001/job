<?php
/*
 * ไฟล์: /availability_add.php
 * หน้าที่: "สมอง" (ไม่มีหน้าตา) รับ "เวลาว่าง" มา INSERT
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (ต้องล็อกอิน และเป็น Role 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

// 2. เช็กว่าส่งมาแบบ POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: profile.php");
    exit;
}

// 3. รับค่า
$seeker_id = $_POST['seeker_id'];
$day_of_week = $_POST['day_of_week'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

// (เช็กความปลอดภัยเบื้องต้น: seeker_id ที่ส่งมา ตรงกับ user_id ที่ล็อกอินไหม)
$stmt_check = $pdo->prepare("SELECT user_id FROM JOB_SEEKER_PROFILES WHERE seeker_id = ?");
$stmt_check->execute([$seeker_id]);
$owner = $stmt_check->fetch();

if (!$owner || $owner['user_id'] != $_SESSION['user_id']) {
    // (ถ้าพยายามยิง seeker_id ของคนอื่น)
    $_SESSION['error'] = "สิทธิ์ไม่ถูกต้อง!";
    header("Location: profile.php");
    exit;
}

// 4. (ขั้นสุดท้าย) INSERT ลง DB
try {
    $sql = "INSERT INTO SEEKER_AVAILABILITY (seeker_id, day_of_week, start_time, end_time) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seeker_id, $day_of_week, $start_time, $end_time]);

    $_SESSION['success'] = "เพิ่มเวลาว่างสำเร็จ!";

} catch (Exception $e) {
    $_SESSION['error'] = "เพิ่มเวลาล้มเหลว: " . $e->getMessage();
}

// 5. ส่งกลับไปหน้าโปรไฟล์ (ที่เดิม)
header("Location: profile.php");
exit;
?>