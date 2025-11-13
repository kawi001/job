<?php
/*
 * ไฟล์: /view_seeker_profile.php
 * (หน้าที่แสดงโปรไฟล์ผู้หางานแบบเต็มสำหรับนายจ้าง)
 */

// 1. เรียก "ส่วนหัว"
require 'includes/header.php'; 

// 2. "ยาม" (ต้องเป็น Role 2: นายจ้าง)
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (หน้านี้สำหรับนายจ้างเท่านั้น)");
}

// 3. รับ Seeker ID จาก URL
$seeker_id = $_GET['seeker_id'] ?? die("ไม่ระบุ Seeker ID");

// 4. ดึงข้อมูลโปรไฟล์ผู้หางาน
try {
    // 4.1 ดึงข้อมูลโปรไฟล์หลัก (รวมถึงพิกัด)
    $stmt_profile = $pdo->prepare("
        SELECT 
            JSP.*, U.username, U.email 
        FROM 
            JOB_SEEKER_PROFILES AS JSP
        JOIN
            USERS AS U ON JSP.user_id = U.user_id
        WHERE 
            JSP.seeker_id = ?
    ");
    $stmt_profile->execute([$seeker_id]);
    $profile = $stmt_profile->fetch();

    if (!$profile) {
        die("ไม่พบโปรไฟล์ผู้หางาน");
    }

    // 4.2 ดึงข้อมูลเวลาว่าง (Availability)
    $stmt_avail = $pdo->prepare("
        SELECT 
            * FROM 
            SEEKER_AVAILABILITY 
        WHERE 
            seeker_id = ? 
        ORDER BY 
            day_of_week, start_time
    ");
    $stmt_avail->execute([$seeker_id]);
    $availability_data = $stmt_avail->fetchAll();

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

// ข้อมูลวันที่สำหรับแสดงผล
$days_of_week = [
    1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี',
    5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์'
];
?>

<h1>โปรไฟล์ผู้สมัคร: <?php echo htmlspecialchars($profile['name']); ?></h1>
<p><a href="javascript:history.back()"> &lt; กลับไปหน้ารายชื่อผู้สมัคร</a></p>

<div style="border: 1px solid #0056b3; padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #e0f7ff;">
    <h3>ข้อมูลส่วนตัวและการติดต่อ</h3>
    <p><strong>ชื่อ-สกุล:</strong> <?php echo htmlspecialchars($profile['name']); ?></p>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
    <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($profile['phone'] ?? '- ไม่ระบุ -'); ?></p>
    <p><strong>ที่อยู่ (อ้างอิง):</strong> <?php echo nl2br(htmlspecialchars($profile['address'] ?? '- ไม่ระบุ -')); ?></p>
    <p>
        <strong>พิกัด (Lat/Lon):</strong> 
        <?php 
            if (!empty($profile['latitude']) && !empty($profile['longitude'])) {
                echo "Latitude: " . htmlspecialchars($profile['latitude']) . ", Longitude: " . htmlspecialchars($profile['longitude']);
                echo " (<a href='https://www.google.com/maps/search/?api=1&query=" . urlencode($profile['latitude'] . ',' . $profile['longitude']) . "' target='_blank'>ดูบนแผนที่</a>)";
            } else {
                echo "- ไม่ได้ระบุพิกัด -";
            }
        ?>
    </p>
</div>

<div style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <h3>ทักษะและประสบการณ์</h3>
    <p><strong>ทักษะ (Skills):</strong> <?php echo htmlspecialchars($profile['skills'] ?? '- ไม่ระบุ -'); ?></p>
    <p><strong>ประสบการณ์:</strong> <?php echo nl2br(htmlspecialchars($profile['experience'] ?? '- ไม่ระบุ -')); ?></p>
</div>


<div style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <h3>ตารางเวลาว่าง (Availability)</h3>

    <?php if (empty($availability_data)): ?>
        <p style="color: #888;">ผู้สมัครรายนี้ยังไม่ได้กำหนดเวลาว่าง</p>
    <?php else: ?>
        <table border="1" style="width:100%; border-collapse: collapse;">
            <thead> 
                <tr style="background: #f0f0f0;"> 
                    <th>วัน</th> 
                    <th>เวลาเริ่ม</th> 
                    <th>เวลาสิ้นสุด</th> 
                </tr> 
            </thead>
            <tbody>
                <?php foreach ($availability_data as $avail): ?>
                    <tr>
                        <td><?php echo $days_of_week[$avail['day_of_week']]; ?></td>
                        <td><?php echo htmlspecialchars($avail['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($avail['end_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// 5. เรียก "ส่วนท้าย"
require 'includes/footer.php';
?>