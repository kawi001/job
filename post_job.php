<?php
/*
 * ไฟล์: /post_job.php
 * หน้าที่: ฟอร์มสำหรับ "ร้านค้า" (Role 2) โพสต์งาน
 */

// 1. เรียก "ส่วนหัว" (ยาม, เมนู, $user_id, $role_id)
require 'includes/header.php';

// 2. (สำคัญ!) "ยาม" เฉพาะทาง
// เช็กว่าใช่ Role 2 (Employer) หรือไม่
if ($role_id != 2) {
    // ถ้าไม่ใช่ (เช่น Role 1 พิมพ์ URL มาเอง)
    $_SESSION['error'] = "คุณไม่มีสิทธิ์เข้าถึงหน้านี้";
    header("Location: dashboard.php"); // ไล่กลับไปหน้าหลัก
    exit;
}

// 3. (สำคัญ!) ดึง "หมวดหมู่" ทั้งหมดจาก DB มาเตรียมไว้
try {
    $stmt_cat = $pdo->query("SELECT * FROM CATEGORIES ORDER BY category_name");
    $categories = $stmt_cat->fetchAll();
} catch (Exception $e) {
    $categories = []; // ถ้าพัง ก็ให้เป็นค่าว่างไปก่อน
}

?>

<h1>โพสต์ประกาศงานใหม่</h1>

<form action="post_job_process.php" method="POST">
    
    <div>
        <label for="job_title">ชื่องาน (เช่น: พนักงานเสิร์ฟ, ผู้ช่วยครัว):</label>
        <input type="text" id="job_title" name="job_title" required>
    </div>

    <div>
        <label for="category_id">หมวดหมู่งาน:</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- กรุณาเลือกหมวดหมู่ --</option>
            <?php
            // (เอา $categories ที่ดึงมา วนลูปสร้าง <option>)
            foreach ($categories as $cat) {
                echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <label for="description">รายละเอียดงาน (หน้าที่, คุณสมบัติ):</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </div>
    
    <div>
        <label for="wage_per_hour">ค่าจ้าง (ต่อชั่วโมง):</label>
        <input type="number" id="wage_per_hour" name="wage_per_hour" step="0.50" required>
    </div>

    <div>
        <label for="num_positions">จำนวนตำแหน่งที่รับ:</label>
        <input type="number" id="num_positions" name="num_positions" value="1" min="1" required>
    </div>

    <br>
    <button type="submit">โพสต์ประกาศงานนี้</button>

</form>

<?php
// 4. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>