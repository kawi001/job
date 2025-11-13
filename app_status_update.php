<?php
/*
 * ไฟล์: /app_status_update.php
 * (Process สำหรับเปลี่ยนสถานะใบสมัครงาน - แทนที่ update_application_status.php เดิม)
 */

session_start();
require 'includes/config.php'; // (Path ถูกต้อง)

// 1. "ยาม" (Role 2 และเช็ก POST)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit;
}

// 2. รับค่า
$app_id = $_POST['application_id'] ?? null;
$job_id = $_POST['job_id'] ?? 0; // เพื่อใช้ redirect
$new_status = $_POST['new_status'] ?? null;
$user_id = $_SESSION['user_id'];

// 3. เช็กความปลอดภัยและอัปเดต
if (!$app_id || !in_array($new_status, ['pending', 'approved', 'rejected'])) {
    $_SESSION['error'] = "ข้อมูลสถานะไม่ถูกต้อง!";
    header("Location: manage_application.php?app_id=" . $app_id);
    exit;
}

try {
    // 3.1 เช็กสิทธิ์ (ต้องเป็นเจ้าของงานนั้นจริงๆ)
    $stmt_check = $pdo->prepare("
        SELECT A.application_id
        FROM APPLICATIONS AS A
        JOIN JOBS AS J ON A.job_id = J.job_id
        JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE A.application_id = ? AND S.user_id = ?
    ");
    $stmt_check->execute([$app_id, $user_id]);
    
    if (!$stmt_check->fetch()) {
        $_SESSION['error'] = "สิทธิ์ไม่ถูกต้องในการจัดการใบสมัครนี้";
        header("Location: dashboard.php");
        exit;
    }
    
    // 3.2 ยิง UPDATE สถานะ
    $sql = "UPDATE APPLICATIONS SET status = ? WHERE application_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $app_id]);

    $_SESSION['success'] = "อัปเดตสถานะใบสมัครเป็น **" . ucfirst($new_status) . "** สำเร็จ!";

} catch (Exception $e) {
    $_SESSION['error'] = "อัปเดตสถานะล้มเหลว: " . $e->getMessage();
}

// 4. ส่งกลับไปหน้าจัดการ
header("Location: manage_application.php?app_id=" . $app_id);
exit;
?>