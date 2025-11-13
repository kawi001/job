<?php
// 1. เรียก "ส่วนหัว" (includes/header.php ต้องมีการเชื่อมต่อ $pdo และมี $user_id, $role_id)
require 'includes/header.php';

// 2. ตรวจสอบ Role
if ($role_id != 1) {
    die("หน้านี้สำหรับผู้หางานเท่านั้น");
}

// 3. ดึงข้อมูล "ปัจจุบัน" ของผู้หางาน (ID, Lat, Lon)
try {
    // ดึงโปรไฟล์ Seeker
    $stmt_seeker = $pdo->prepare("SELECT seeker_id, latitude, longitude FROM job_seeker_profiles WHERE user_id = ?");
    $stmt_seeker->execute([$user_id]);
    $seeker_profile = $stmt_seeker->fetch();

    if (!$seeker_profile) {
        die("ไม่พบโปรไฟล์ผู้หางาน");
    }

    $current_seeker_id = $seeker_profile['seeker_id'];
    $seeker_lat = $seeker_profile['latitude'];
    $seeker_lon = $seeker_profile['longitude'];

    // ตรวจสอบว่ามีพิกัดหรือยัง
    if (empty($seeker_lat) || empty($seeker_lon)) {
        echo "<h3>กรุณาระบุตำแหน่ง (พิกัด) ในหน้าโปรไฟล์ของคุณก่อน</h3>";
        echo '<p>งานที่แนะนำจะปรากฏที่นี่หลังจากคุณปักหมุดตำแหน่งครับ <a href="profile.php">ไปหน้าโปรไฟล์</a></p>';
        require 'includes/footer.php';
        exit;
    }

    // 4. SQL Query: ค้นหางานที่ "เวลาตรงกัน" และ "อยู่ใกล้"
    // แก้ไขชื่อตัวแปรพิกัด (Lat, Lon) ใน Haversine Formula ให้เป็นชื่อเฉพาะ
    
    $sql_match = "
        SELECT
            j.job_id,
            j.job_title,
            j.wage_per_hour,
            s.shop_name,
            s.address,
            (
                6371 * acos(
                    cos(radians( :seeker_lat_cos )) /* ตัวแปรที่ 1: Lat สำหรับ cos() */
                    * cos(radians(s.latitude))
                    * cos(radians(s.longitude) - radians( :seeker_lon )) /* ตัวแปรที่ 2: Lon */
                    + sin(radians( :seeker_lat_sin )) /* ตัวแปรที่ 3: Lat สำหรับ sin() */
                    * sin(radians(s.latitude))
                )
            ) AS distance_km
        FROM
            jobs AS j
        JOIN
            shop_profiles AS s ON j.shop_id = s.shop_id
        WHERE
            j.status = 'open'
            
            /* เงื่อนไขการจับคู่เวลา (Time Overlap) */
            AND EXISTS (
                SELECT 1
                FROM
                    job_required_shifts AS jrs
                JOIN
                    seeker_availability AS sa
                        ON jrs.day_of_week = sa.day_of_week
                        AND sa.seeker_id = :seeker_id /* ตัวแปรที่ 4: Seeker ID */
                        AND jrs.start_time < sa.end_time
                        AND jrs.end_time > sa.start_time
                WHERE
                    jrs.job_id = j.job_id
            )
        
        /* เงื่อนไขการกรองระยะทาง */
        HAVING
            distance_km < 20 /* คัดกรองงานในรัศมี 20 กม. */
        ORDER BY
            distance_km ASC;
    ";

    // 5. รัน Query (ต้องส่งค่า 4 ตัวแปรให้ตรงกับชื่อใน SQL)
    $stmt_jobs = $pdo->prepare($sql_match);
    
    // ส่งค่า 4 ตัวแปรที่ใช้ใน SQL (แม้ว่า Lat จะใช้ค่าเดียวกัน แต่ต้องระบุชื่อเฉพาะ)
    $stmt_jobs->execute([
        ':seeker_id'      => $current_seeker_id,
        ':seeker_lat_cos' => $seeker_lat,
        ':seeker_lon'     => $seeker_lon,
        ':seeker_lat_sin' => $seeker_lat
    ]);

    $matched_jobs = $stmt_jobs->fetchAll();

} catch (Exception $e) {
    // ควรแสดง error นี้เฉพาะตอน Dev เท่านั้น เพื่อความปลอดภัย
    die("เกิดข้อผิดพลาดในการค้นหา: " . $e->getMessage()); 
}

?>

<h1>ค้นหางานที่ตรงกับคุณ (ใกล้ + เวลาว่าง)</h1>

<?php if (empty($matched_jobs)): ?>
    
    <div style="background: #fff8e1; padding: 15px; border-radius: 8px;">
        <h4>ไม่พบงานที่ตรงกับ "เวลาว่าง" และ "ระยะทาง" ของคุณในตอนนี้</h4>
        <p>ลองตรวจสอบการตั้งค่าเวลาว่างในหน้าโปรไฟล์ของคุณ หรืออาจจะยังไม่มีร้านค้าใกล้ๆ ที่ลงประกาศในเวลาที่คุณว่างครับ</p>
        <a href="profile.php">แก้ไขเวลาว่าง</a>
    </div>

<?php else: ?>

    <p>พบงาน <strong><?php echo count($matched_jobs); ?></strong> รายการ ที่ตรงกับเวลาว่างของคุณและอยู่ในรัศมี 20 กม. (โดยประมาณ)</p>
    <hr>
    
    <?php foreach ($matched_jobs as $job): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
            <h4>
                <a href="job_view.php?id=<?php echo $job['job_id']; ?>">
                    <?php echo htmlspecialchars($job['job_title']); ?>
                </a>
            </h4>
            
            <strong>ร้าน:</strong> <?php echo htmlspecialchars($job['shop_name']); ?><br>
            <strong>ค่าจ้าง:</strong> <?php echo htmlspecialchars($job['wage_per_hour']); ?> บาท/ชั่วโมง<br>
            <strong>ที่อยู่:</strong> <?php echo htmlspecialchars($job['address']); ?><br>
            
            <strong style="color: #0066cc;">
                ระยะทาง: 
                <?php echo number_format($job['distance_km'], 2); ?> กม.
            </strong>
        </div>
    <?php endforeach; ?>

<?php endif; ?>


<?php
// 6. เรียก "ส่วนท้าย"
require 'includes/footer.php';
?>