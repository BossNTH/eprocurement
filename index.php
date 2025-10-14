<?php
  include("connect.php");
  session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ - ระบบจัดซื้อ</title>

  <!-- ใช้ธีมที่สร้างไว้ก่อนหน้านี้ -->
  <link rel="stylesheet" href="assets/css/app-theme.css">
</head>
<body>

  <!-- แถบนำทาง -->
  <div class="navbar">
    <div class="inner container">
      <div class="brand">
        <span class="logo"></span> ระบบจัดซื้อ (E-Procurement)
      </div>
    </div>
  </div>

  <!-- กล่อง Login -->
  <div class="container" style="display:flex; align-items:center; justify-content:center; min-height:80vh;">
    <div class="card" style="max-width:420px; width:100%; padding:32px;">
      <h1 class="mb-2" style="text-align:center;">เข้าสู่ระบบ</h1>
      <p style="color:var(--muted); text-align:center; margin-bottom:20px;">
        กรุณากรอกชื่อผู้ใช้และรหัสผ่านเพื่อเข้าสู่ระบบ
      </p>

      <form method="post" action="check_login.php" class="grid">
        <div class="col-12">
          <label>ชื่อผู้ใช้ (Username)</label>
          <input type="text" name="username" class="input" required autofocus>
        </div>

        <div class="col-12">
          <label>รหัสผ่าน (Password)</label>
          <input type="password" name="password" class="input" required>
        </div>

        <div class="col-12 mt-3">
          <button type="submit" class="btn primary" style="width:100%; justify-content:center;">
            เข้าสู่ระบบ
          </button>
        </div>
      </form>

      <div class="mt-3" style="text-align:center; font-size:0.95rem;">
        เป็นคู่ค้าใหม่?
        <a href="supplierRegister.php.php">ลงทะเบียนที่นี่</a>
      </div>
    </div>
  </div>

  <div class="footer" style="text-align:center;">
    © <?php echo date('Y'); ?> ระบบจัดซื้อภายใน
  </div>

  <script src="assets/js/app-ui.js"></script>
</body>
</html>
