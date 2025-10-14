<?php
session_start();

$manager_name = $_SESSION['employee_name'] ?? 'ผู้จัดการจัดซื้อ';
?>
<!-- ===== DARK MODERN SIDEBAR: PROCUREMENT MANAGER ===== -->
<style>
:root {
  --clr-bg-top: #1e40af;
  --clr-bg-bottom: #0f172a;
  --clr-accent: #22d3ee;
  --clr-text: #e2e8f0;
  --clr-muted: #94a3b8;
  --clr-hover: rgba(34,211,238,0.15);
}

body {
  background-color: var(--clr-bg-bottom);
  color: var(--clr-text);
  font-family: 'Prompt', sans-serif;
  margin: 0;
}

.sidebar {
  width: 260px;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  box-shadow: 4px 0 12px rgba(0,0,0,0.45);
  overflow-y: auto;
  z-index: 100;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar { width: 6px; }
.sidebar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }

/* Header Logo */
.sidebar-brand-wrapper {
  background: linear-gradient(90deg, var(--clr-bg-top), var(--clr-accent));
  height: 75px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 -1px 0 rgba(255,255,255,0.1);
}
.brand-logo {
  color: #fff;
  font-weight: 700;
  font-size: 1.2rem;
  text-decoration: none;
  letter-spacing: .5px;
}

/* Profile */
.nav-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.nav-profile-image img {
  width: 50px; height: 50px;
  border-radius: 50%;
  border: 2px solid var(--clr-accent);
  object-fit: cover;
}
.nav-profile-text span {
  display: block;
  font-weight: 600;
  color: var(--clr-text);
}
.nav-profile-text small {
  color: var(--clr-muted);
}

/* Nav Menu */
.nav {
  list-style: none;
  padding: 1rem 0;
  margin: 0;
}
.nav-category {
  font-size: .8rem;
  text-transform: uppercase;
  color: var(--clr-muted);
  margin: 0.5rem 1.5rem;
  letter-spacing: 0.8px;
}

.nav-item { margin: 3px 10px; }

.nav-link {
  display: flex;
  align-items: center;
  color: var(--clr-text);
  text-decoration: none;
  padding: 10px 14px;
  border-radius: 8px;
  transition: all 0.2s;
}
.nav-link:hover {
  background: var(--clr-hover);
  color: var(--clr-accent);
}
.nav-link.active {
  background: var(--clr-accent);
  color: #fff;
  font-weight: 500;
}
.menu-icon {
  width: 24px;
  text-align: center;
  margin-right: 10px;
  font-size: 1.1rem;
}
</style>

<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand-wrapper">
    <a class="brand-logo" href="dashboard.php">Procurement Manager</a>
  </div>

  <div class="nav-profile">
    <div class="nav-profile-image">
      <img src="../assets/images/faces/R.png" alt="profile">
    </div>
    <div class="nav-profile-text">
      <span><?= htmlspecialchars($manager_name) ?></span>
      <small>ผู้จัดการจัดซื้อ</small>
    </div>
  </div>

  <ul class="nav">
    <li class="nav-category">เมนูหลัก</li>

    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='purchase_approval.php'?'active':'' ?>" href="purchase_approval.php">
        <span class="menu-icon"><i class="bi bi-file-earmark-check"></i></span>
        <span class="menu-title">อนุมัติใบสั่งซื้อ</span>
      </a>
    </li>

    <li class="nav-item mt-3">
      <a class="nav-link" href="../logout.php">
        <span class="menu-icon"><i class="bi bi-box-arrow-right"></i></span>
        <span class="menu-title">ออกจากระบบ</span>
      </a>
    </li>
  </ul>
</nav>
