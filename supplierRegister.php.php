<?php
require_once "connect.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>สมัครสมาชิกผู้ขาย</title>

  <!-- ใช้ธีมกลางของระบบ -->
  <link rel="stylesheet" href="assets/css/app-theme.css">
  <script src="assets/js/app-ui.js"></script>
</head>
<body>

  <div class="navbar">
    <div class="inner container">
      <div class="brand">
        <span class="logo"></span> ระบบจัดซื้อ (E-Procurement)
      </div>
    </div>
  </div>

  <div class="container" style="display:flex; justify-content:center; align-items:center; min-height:90vh;">
    <div class="card" style="max-width:520px; width:100%; padding:32px;">
      <h1 class="mb-2" style="text-align:center;">สมัครสมาชิกผู้ขาย (Seller)</h1>
      <p style="text-align:center; color:var(--muted); margin-bottom:24px;">
        กรอกข้อมูลด้านล่างเพื่อสร้างบัญชีผู้ขายในระบบจัดซื้อ
      </p>

      <form action="register_process.php" method="post" class="grid">
        <!-- ผู้ใช้ (ตาราง users) -->
        <div class="col-12">
          <label>ชื่อผู้ใช้ (Username)</label>
          <input type="text" class="input" name="username" placeholder="เช่น seller01" required>
        </div>

        <div class="col-12">
          <label>รหัสผ่าน</label>
          <input type="password" class="input" name="password" placeholder="รหัสผ่าน (อย่างน้อย 6 ตัว)" minlength="6" required>
        </div>

        <div class="col-12">
          <label>ยืนยันรหัสผ่าน</label>
          <input type="password" class="input" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
        </div>

        <hr class="col-12" style="border-color: var(--line); margin: 16px 0;">

        <!-- ข้อมูลผู้ขาย (ตาราง suppliers) -->
        <div class="col-12">
          <label>ชื่อผู้ขาย/ชื่อร้าน</label>
          <input type="text" class="input" name="supplier_name" placeholder="เช่น ร้านสมชายการค้า" required>
        </div>

        <div class="col-12">
          <label>ข้อมูลติดต่อเพิ่มเติม</label>
          <input type="text" class="input" name="contact_info" placeholder="เช่น ชื่อผู้ติดต่อ, Line, เลขผู้เสียภาษี ฯลฯ">
        </div>

        <div class="col-12">
          <label>อีเมล</label>
          <input type="email" class="input" name="email" placeholder="กรอกอีเมล" required>
        </div>

        <div class="col-12">
          <label>เบอร์โทร</label>
          <input type="text" class="input" name="phone" placeholder="เช่น 08x-xxx-xxxx">
        </div>

        <div class="col-12">
          <label>ที่อยู่</label>
          <textarea class="input" name="address" rows="2" placeholder="ที่อยู่สำหรับติดต่อ/ออกเอกสาร"></textarea>
        </div>

        <!-- role -->
        <input type="hidden" name="role" value="seller">

        <div class="col-12 mt-2">
          <button type="submit" class="btn primary" style="width:100%; justify-content:center;">
            สมัครสมาชิก
          </button>
        </div>
      </form>

      <div class="mt-3" style="text-align:center;">
        มีบัญชีแล้ว? <a href="index.php">เข้าสู่ระบบ</a>
      </div>
    </div>
  </div>

  <div class="footer" style="text-align:center;">
    © <?php echo date('Y'); ?> ระบบจัดซื้อภายใน
  </div>
</body>
</html>
