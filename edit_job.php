<?php
/*
 * ไฟล์: /edit_job.php
 * (หน้าที่จัดการ: รายละเอียดงาน, เพิ่ม/ลบกะ)
 */

// 1. เรียก "ส่วนหัว"
require 'includes/header.php'; 

// 2. "ยาม" (ต้องเป็น Role 2)
if ($role_id != 2) {
    die("สิทธิ์ไม่ถูกต้อง (ต้องเป็น Role 2)");
}

// 3. รับ Job ID
$job_id = $_GET['job_id'] ?? die("ไม่ระบุ Job ID");
$current_user_id = $user_id;

// 4. ดึงข้อมูลงานและตรวจสอบสิทธิ์
try {
    $stmt_job = $pdo->prepare("
        SELECT 
            J.*, S.shop_id, S.shop_name 
        FROM 
            JOBS AS J
        JOIN 
            SHOP_PROFILES AS S ON J.shop_id = S.shop_id
        WHERE 
            J.job_id = ? AND S.user_id = ?
    ");
    $stmt_job->execute([$job_id, $current_user_id]);
    $job_data = $stmt_job->fetch();

    if (!$job_data) {
        die("ไม่พบงานนี้ หรือคุณไม่ใช่เจ้าของงาน");
    }

    // 5. ดึงข้อมูลกะงาน (Shifts) ที่มีอยู่
    $stmt_shifts = $pdo->prepare("
        SELECT 
            * FROM 
            JOB_REQUIRED_SHIFTS 
        WHERE 
            job_id = ? 
        ORDER BY 
            day_of_week, start_time
    ");
    $stmt_shifts->execute([$job_id]);
    $shifts_data = $stmt_shifts->fetchAll();

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

// ข้อมูลวันที่สำหรับแสดงผล
$days_of_week = [
    1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี',
    5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์'
];
?>

<h1>จัดการงาน: <?php echo htmlspecialchars($job_data['job_title']); ?></h1>
<p>ร้าน: **<?php echo htmlspecialchars($job_data['shop_name']); ?>**</p>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <h3>รายละเอียดงาน</h3>
    <p><strong>คำอธิบาย:</strong> <?php echo nl2br(htmlspecialchars($job_data['description'])); ?></p>
    <p><strong>ค่าจ้าง/ชั่วโมง:</strong> <?php echo htmlspecialchars($job_data['wage_per_hour'] ?? 'N/A'); ?> บาท</p>
    <p><strong>สถานะ:</strong> <?php echo ($job_data['status'] == 'open') ? 'เปิดรับ' : 'ปิดรับ'; ?></p>
    </div>


<hr style="margin: 30px 0;">


<h3>ตารางกะที่ต้องการ (Job Required Shifts)</h3>

<?php if (empty($shifts_data)): ?>
    <div style="background: #fff8e1; padding: 10px; border-radius: 4px;">
        <p>ยังไม่มีการกำหนดกะงานที่ต้องการสำหรับงานนี้</p>
    </div>
<?php else: ?>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead> 
            <tr> 
                <th>วัน</th> 
                <th>เวลาเริ่ม</th> 
                <th>เวลาสิ้นสุด</th> 
                <th>จัดการ</th> 
            </tr> 
        </thead>
        <tbody>
            <?php foreach ($shifts_data as $shift): ?>
                <tr>
                    <td><?php echo $days_of_week[$shift['day_of_week']]; ?></td>
                    <td><?php echo htmlspecialchars($shift['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($shift['end_time']); ?></td>
                    <td>
                        <a href="shift_delete.php?shift_id=<?php echo $shift['shift_id']; ?>" 
                            style="color:red;" onclick="return confirm('ยืนยันลบกะนี้?');">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<form action="shift_add.php" method="POST" style="margin-top: 20px; background: #e6f7ff; padding: 15px; border-radius: 8px;">
    <h4>เพิ่มกะใหม่:</h4>
    <select name="day_of_week" required>
        <option value="">-- เลือกวัน --</option>
        <?php foreach ($days_of_week as $num => $name): ?>
            <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="time" name="start_time" required>
    <input type="time" name="end_time" required>
    
    <input type="hidden" name="job_id" value="<?php echo $job_data['job_id']; ?>">
    <button type="submit">เพิ่มกะงาน</button>
</form>

<?php
// 6. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>