<!-- partial-emp_header.php -->
<?php
session_start();

$emp_name = $_SESSION['employee_name'] ?? 'พนักงานทั่วไป';
?>

<style>
/* ===== DARK-MODERN SIDEBAR (Indigo / Slate / Teal) ===== */
body {
  background-color: #0f172a;
  color: #e2e8f0;
  font-family: 'Prompt', sans-serif;
}

.sidebar {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: #cbd5e1;
  width: 260px;
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  overflow-y: auto;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
  z-index: 100;
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 5px;
}

.sidebar .sidebar-brand-wrapper {
  background-color: #1e3a8a; /* Indigo */
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar .brand-logo {
  font-size: 1.15rem;
  font-weight: 600;
  letter-spacing: .5px;
  color: #e0f2fe !important;
  text-decoration: none;
}

.nav {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
}

.nav-item {
  margin: 5px 12px;
}

.nav-link {
  display: flex;
  align-items: center;
  color: #cbd5e1;
  text-decoration: none;
  padding: 10px 14px;
  border-radius: 8px;
  transition: 0.2s;
}

.nav-link:hover {
  background-color: rgba(20, 184, 166, 0.2);
  color: #5eead4;
}

.nav-link.active {
  background-color: #14b8a6;
  color: white;
}

.menu-icon i {
  font-size: 1.2rem;
  margin-right: 10px;
}

.nav-category {
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #94a3b8;
  margin: 1rem 1.2rem 0.25rem;
}

.nav-profile {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.nav-profile-image img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 2px solid #38bdf8;
}

.nav-profile-text {
  margin-left: 12px;
}

.nav-profile-text span {
  display: block;
  color: #e2e8f0;
}
.nav-profile-text small {
  color: #94a3b8;
}
</style>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper">
    <a class="brand-logo" href="dashboard.php">พนักงานทั่วไป</a>
  </div>

  <div class="nav-profile">
    <div class="nav-profile-image">
      <img src="../assets/images/faces/R.png" alt="profile">
    </div>
    <div class="nav-profile-text">
      <span class="font-weight-bold"><?= htmlspecialchars($emp_name) ?></span>
      <small>พนักงานทั่วไป</small>
    </div>
  </div>

  <ul class="nav">
    <li class="nav-item nav-category">เมนูหลัก</li>

    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php">
        <span class="menu-icon"><i class="mdi mdi-view-dashboard-outline"></i></span>
        <span class="menu-title">แดชบอร์ด</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='pr_manage.php'?'active':'' ?>" href="pr_manage.php">
        <span class="menu-icon"><i class="mdi mdi-file-document-box"></i></span>
        <span class="menu-title">จัดการขอซื้อสินค้า</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='emp_inventory.php'?'active':'' ?>" href="emp_inventory.php">
        <span class="menu-icon"><i class="mdi mdi-package-variant"></i></span>
        <span class="menu-title">ดูสินค้าคงคลัง</span>
      </a>
    </li>

    <li class="nav-item mt-2">
      <a class="nav-link" href="../logout.php">
        <span class="menu-icon"><i class="mdi mdi-logout"></i></span>
        <span class="menu-title">ออกจากระบบ</span>
      </a>
    </li>
  </ul>
</nav>
