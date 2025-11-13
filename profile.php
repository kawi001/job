<?php
/*
 * ไฟล์: /profile.php
 * (เวอร์ชัน ไฮบริด + เพิ่มฟังก์ชันรูปภาพร้านค้า + แก้บั๊ก 'phone')
 */

// 1. เรียก "ส่วนหัว" (โหลด Lib แผนที่, session, config)
require 'includes/header.php'; 

// 2. ดึงข้อมูล
$profile_data = null; 
$seeker_id = null; 
$availability_data = []; 
$current_lat = ''; 
$current_lon = ''; 
$current_image_path = 'images/default_shop.png'; 
$shop_id = null; 

if ($role_id == 1) {
    // (ดึงโปรไฟล์ Seeker)
    $stmt = $pdo->prepare("SELECT * FROM JOB_SEEKER_PROFILES WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile_data = $stmt->fetch();
    
    $seeker_id = $profile_data['seeker_id'] ?? null; 
    $current_lat = trim($profile_data['latitude'] ?? '');
    $current_lon = trim($profile_data['longitude'] ?? '');
    
    // (ดึง "เวลาว่าง" ของ Seeker)
    if ($seeker_id) {
        $stmt_avail = $pdo->prepare("SELECT * FROM SEEKER_AVAILABILITY WHERE seeker_id = ? ORDER BY day_of_week");
        $stmt_avail->execute([$seeker_id]); 
        $availability_data = $stmt_avail->fetchAll();
    }

} elseif ($role_id == 2) {
    // (ดึงโปรไฟล์ Employer)
    $stmt = $pdo->prepare("SELECT * FROM SHOP_PROFILES WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile_data = $stmt->fetch();
    
    $current_lat = trim($profile_data['latitude'] ?? '');
    $current_lon = trim($profile_data['longitude'] ?? '');
    
    // (ดึงพาธรูปภาพและ Shop ID มาเตรียมไว้)
    $shop_id = $profile_data['shop_id'] ?? null;
    $current_image_path = $profile_data['image_path'] ?? 'images/default_shop.png'; 
}

if (!$profile_data) { die("ไม่พบข้อมูลโปรไฟล์"); }

$days_of_week = [
    1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี',
    5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์'
];
?>

<h1>แก้ไขโปรไฟล์</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e0ffe0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 4px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form action="profile_update.php" method="POST" style="background:#f9f9f9; padding:15px; border-radius:8px;">
    <h4>ข้อมูลส่วนตัว / ร้านค้า</h4>
    
    <?php if ($role_id == 1): // --- ฟอร์ม 'Job Seeker' (Role 1) --- ?>
        <input type="hidden" name="role_type" value="seeker">
        <div><label>ชื่อ-สกุล:</label> <input type="text" name="name" value="<?php echo htmlspecialchars($profile_data['name']); ?>"></div>
        <div><label>เบอร์โทร:</label> <input type="text" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>"></div>
        <div><label>ที่อยู่:</label> <textarea name="address"><?php echo htmlspecialchars($profile_data['address']); ?></textarea></div>
        <div><label>ทักษะ:</label> <input type="text" name="skills" value="<?php echo htmlspecialchars($profile_data['skills']); ?>"></div>
        <div><label>ประสบการณ์:</label> <textarea name="experience"><?php echo htmlspecialchars($profile_data['experience']); ?></textarea></div>
    <?php elseif ($role_id == 2): // --- ฟอร์ม 'Employer' (Role 2) --- ?>
        <input type="hidden" name="role_type" value="employer">
        <div><label>ชื่อร้านค้า:</label> <input type="text" name="shop_name" value="<?php echo htmlspecialchars($profile_data['shop_name']); ?>"></div>
        <div><label>เบอร์โทรร้าน:</label> <input type="text" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>"></div>
        <div><label>ที่อยู่ร้าน:</label> <textarea name="address"><?php echo htmlspecialchars($profile_data['address']); ?></textarea></div>
        <div><label>คำอธิบายร้าน:</label> <textarea name="description"><?php echo htmlspecialchars($profile_data['description']); ?></textarea></div>
    <?php endif; ?>

    <label>ตำแหน่ง (คลิกบนแผนที่เพื่อปักหมุด):</label>
    <div id="mapid" style="height: 300px; margin-bottom: 15px; border: 1px solid #ccc; cursor: pointer;"></div>
    <div><label>Latitude:</label> <input type="text" name="latitude" id="latitude" value="<?php echo htmlspecialchars($current_lat); ?>" readonly></div>
    <div><label>Longitude:</label> <input type="text" name="longitude" id="longitude" value="<?php echo htmlspecialchars($current_lon); ?>" readonly></div>
    <button type="submit">บันทึก (ข้อมูลส่วนตัว/ร้านค้า)</button>
</form>

<hr style="margin: 30px 0;">

<?php if ($role_id == 2 && $shop_id): ?>
    
    <h2>รูปภาพโปรไฟล์ร้านค้า</h2>
    
    <div style="margin-bottom: 20px;">
        <img src="<?php echo htmlspecialchars($current_image_path); ?>" 
             alt="Shop Profile Image" 
             style="width: 200px; height: 200px; object-fit: cover; border: 1px solid #ccc; border-radius: 8px;">
    </div>
    
    <form action="profile_update_image.php" method="POST" enctype="multipart/form-data" style="background:#f0f0f0; padding:15px; border-radius:8px;">
        
        <h4>อัปโหลด/เปลี่ยนรูปภาพใหม่</h4>
        
        <input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>"> 
        
        <div>
            <label for="shop_image">เลือกไฟล์รูปภาพ (JPG, PNG):</label>
            <input type="file" name="shop_image" id="shop_image" accept="image/jpeg,image/png" required>
        </div>
        
        <div style="margin-top: 15px;">
            <button type="submit">อัปโหลดและบันทึกรูปภาพ</button>
        </div>
    </form>
    
    <hr style="margin: 30px 0;">

<?php endif; ?>
<?php if ($role_id == 1): ?>
    <h3>จัดการเวลาว่าง (Availability)</h3>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead> <tr> <th>วัน</th> <th>เวลาเริ่ม</th> <th>เวลาสิ้นสุด</th> <th>จัดการ</th> </tr> </thead>
        <tbody>
            <?php if (empty($availability_data)): ?>
                <tr><td colspan="4">คุณยังไม่ได้เพิ่มเวลาว่าง</td></tr>
            <?php else: ?>
                <?php foreach ($availability_data as $avail): ?>
                    <tr>
                        <td><?php echo $days_of_week[$avail['day_of_week']]; ?></td>
                        <td><?php echo htmlspecialchars($avail['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($avail['end_time']); ?></td>
                        <td>
                            <a href="availability_delete.php?id=<?php echo $avail['availability_id']; ?>" 
                               style="color:red;" onclick="return confirm('ลบ?');">ลบ</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <form action="availability_add.php" method="POST" style="margin-top: 20px; background: #e6f7ff; padding: 15px; border-radius: 8px;">
        <h4>เพิ่มเวลาว่างใหม่:</h4>
        <select name="day_of_week" required>
            <option value="">-- เลือกวัน --</option>
            <?php foreach ($days_of_week as $num => $name): ?>
                <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <input type="hidden" name="seeker_id" value="<?php echo $seeker_id; ?>">
        <button type="submit">เพิ่มเวลา</button>
    </form>
<?php endif; ?>


<script>
    // ... โค้ด JavaScript แผนที่เดิม ...
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    let startLat = 18.7883; 
    let startLon = 98.9853;
    let startZoom = 10; 
    const savedLat = (latInput.value && latInput.value != 0) ? parseFloat(latInput.value) : null;
    const savedLon = (lonInput.value && lonInput.value != 0) ? parseFloat(lonInput.value) : null;
    
    if (savedLat && savedLon) {
        startLat = savedLat;
        startLon = savedLon;
        startZoom = 16; 
    }

    const map = L.map('mapid').setView([startLat, startLon], startZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = null; 

    if (savedLat && savedLon) {
        marker = L.marker([startLat, startLon], { draggable: true }).addTo(map);
        marker.on('dragend', function(e) {
            const newPos = marker.getLatLng();
            latInput.value = newPos.lat.toFixed(6);
            lonInput.value = newPos.lng.toFixed(6);
        });
    } else {
        map.attributionControl.setPrefix('กำลังค้นหาตำแหน่งของคุณ...');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const realLat = position.coords.latitude;
                    const realLon = position.coords.longitude;
                    map.flyTo([realLat, realLon], 16); 
                    map.attributionControl.setPrefix('คลิกบนแผนที่เพื่อปักหมุด');
                },
                function(error) {
                    map.attributionControl.setPrefix('ค้นหาล้มเหลว (คลิกเพื่อปักหมุด)');
                }
            );
        } else {
            map.attributionControl.setPrefix('เบราว์เซอร์ไม่รองรับ GPS (คลิกเพื่อปักหมุด)');
        }
    }

    map.on('click', function(e) {
        const newPos = e.latlng;
        latInput.value = newPos.lat.toFixed(6);
        lonInput.value = newPos.lng.toFixed(6);
        
        if (marker === null) {
            marker = L.marker(newPos, { draggable: true }).addTo(map);
            map.attributionControl.setPrefix(''); 
            marker.on('dragend', function(e) {
                const newPos = marker.getLatLng();
                latInput.value = newPos.lat.toFixed(6);
                lonInput.value = newPos.lng.toFixed(6);
            });
        } else {
            marker.setLatLng(newPos);
        }
    });
</script>
<?php
// 4. เรียก "ส่วนท้าย" (footer)
require 'includes/footer.php';
?>