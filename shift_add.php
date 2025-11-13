<?php
/*
 * ไฟล์: /shift_add.php
 * (เวอร์ชัน "ทำใหม่" - คลีน)
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (Role 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit;
}

// 2. เช็ก (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit;
}

// 3. รับค่า
$job_id = $_POST['job_id'];
$day_of_week = $_POST['day_of_week'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$user_id = $_SESSION['user_id'];

// 4. (เช็กความปลอดภัย) เช็กว่า "งาน" (Job) นี้ เป็นของเราจริง
try {
    $stmt_check = $pdo->prepare("
        SELECT J.job_id
        FROM JOBS AS J
        JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE J.job_id = ? AND S.user_id = ?
    ");
    $stmt_check->execute([$job_id, $user_id]);
    $job = $stmt_check->fetch();

    if (!$job) {
        $_SESSION['error'] = "สิทธิ์ไม่ถูกต้อง (ไม่ใช่เจ้าของงาน)!";
        header("Location: edit_job.php?job_id=" . $job_id);
        exit;
    }

    // 5. (ถ้าผ่าน) INSERT
    $sql = "INSERT INTO JOB_REQUIRED_SHIFTS (job_id, day_of_week, start_time, end_time) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_id, $day_of_week, $start_time, $end_time]);

    $_SESSION['success'] = "เพิ่มกะสำเร็จ!";

} catch (Exception $e) {
    $_SESSION['error'] = "เพิ่มกะล้มเหลว: " . $e->getMessage();
}

// 6. ส่งกลับไปหน้า "แก้ไขงาน" (ที่เดิม)
header("Location: edit_job.php?job_id=" . $job_id);
exit;
?>