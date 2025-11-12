<?php
/*
 * ไฟล์: /my_applications.php
 * หน้าที่: (Role 1) ดูประวัติการสมัครงานของตัวเอง
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. (สำคัญ!) "ยาม" เฉพาะทาง (Role 1 เท่านั้น)
if ($role_id != 1) {
    $_SESSION['error'] = "หน้านี้สำหรับผู้หางานเท่านั้น";
    header("Location: dashboard.php");
    exit;
}

// 3. หา "seeker_id" (เหมือนเดิม)
try {
    $stmt_seeker = $pdo->prepare("SELECT seeker_id FROM JOB_SEEKER_PROFILES WHERE user_id = ?");
    $stmt_seeker->execute([$user_id]);
    $seeker = $stmt_seeker->fetch();
    $seeker_id = $seeker['seeker_id'];
} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการหา seeker_id");
}

// 4. (สำคัญ!) ดึงประวัติการสมัครของ seeker คนนี้
// (เราต้อง JOIN ไปตาราง JOBS และ SHOP_PROFILES เพื่อเอาชื่อมาโชว์)
try {
    $sql = "SELECT 
                A.status, 
                A.applied_at,
                J.job_title,
                S.shop_name
            FROM APPLICATIONS AS A
            JOIN JOBS AS J ON A.job_id = J.job_id
            JOIN SHOP_PROFILES AS S ON J.shop_id = S.shop_id
            WHERE A.seeker_id = ?
            ORDER BY A.applied_at DESC"; // (เอาอันใหม่สุดขึ้นก่อน)
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seeker_id]);
    $applications = $stmt->fetchAll();

} catch (Exception $e) {
    $applications = [];
    echo "<p style='color:red;'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}

?>

<h1>การสมัครงานของฉัน</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e0ffe0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .status-pending { color: orange; font-weight: bold; }
    .status-approved { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }
</style>

<table>
    <thead>
        <tr>
            <th>วันที่สมัคร</th>
            <th>ชื่องาน</th>
            <th>ชื่อร้าน</th>
            <th>สถานะ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($applications)): ?>
            <tr>
                <td colspan="4">คุณยังไม่ได้สมัครงานใดๆ</td>
            </tr>
        <?php else: ?>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?php echo $app['applied_at']; ?></td>
                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                    <td><?php echo htmlspecialchars($app['shop_name']); ?></td>
                    <td>
                        <span class="status-<?php echo strtolower($app['status']); ?>">
                            <?php echo htmlspecialchars($app['status']); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>


<?php
// 5. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>