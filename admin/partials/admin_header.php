<?php
// admin/partials/admin_header.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/../../connect.php";
date_default_timezone_set('Asia/Bangkok');

$page_title  = $page_title  ?? 'Admin Panel';
$active_menu = $active_menu ?? '';

function active($key, $active_menu)
{
  return $key === $active_menu ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- โหลดธีมกลางของระบบ -->
  <link rel="stylesheet" href="../../assets/css/app-theme.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    html,
    body {
      margin: 0;
      height: 100%;
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-sans);
    }

    .layout {
      display: grid;
      grid-template-columns: 250px 1fr;
      height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      background: linear-gradient(180deg, rgba(79, 70, 229, .95), rgba(15, 23, 42, .95));
      color: #e2e8f0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px 16px;
      position: sticky;

      top: 0;
      bottom: 0;
      left: 0;

    }

    .brand {
      display: flex;
      align-items: center;
      gap: .6rem;
      font-weight: 800;
      font-size: 1.2rem;
      color: #fff;
      margin-bottom: 1rem;
    }

    .user {
      background: rgba(255, 255, 255, 0.1);
      border-radius: var(--radius);
      padding: 10px 12px;
      font-size: .9rem;
      margin-bottom: 1rem;
      color: #cbd5e1;
    }

    .nav a {
      display: flex;
      align-items: center;
      gap: .7rem;
      padding: .55rem .9rem;
      border-radius: .6rem;
      text-decoration: none;
      font-weight: 500;
      color: #cbd5e1;
      transition: all .2s ease;
    }

    .nav a:hover {
      background: rgba(148, 163, 184, .15);
      color: #fff;
    }

    .nav a.active {
      background: rgba(79, 70, 229, .35);
      color: #fff;
      border-left: 3px solid var(--accent);
      padding-left: calc(.9rem - 3px);
      font-weight: 600;
    }

    .logout {
      color: #fca5a5;
      font-weight: 600;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .logout:hover {
      color: #fecaca;
    }

    /* Main content */
    .content {
      background: var(--surface);
      padding: 24px;
      overflow-y: auto;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--line);
    }

    .topbar h4 {
      font-weight: 700;
      color: var(--accent);
    }

    @media (max-width: 992px) {
      .layout {
        grid-template-columns: 1fr;
      }

      .sidebar {
        flex-direction: row;
        height: auto;
        position: relative;
      }

      .nav {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
      }
    }
  </style>
</head>

<body>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div>
        <div class="brand"><i class="bi bi-grid-1x2-fill"></i> Admin Panel</div>
        <div class="user">อัปเดตล่าสุด: <?= date('d/m/Y H:i') ?></div>

        <nav class="nav d-flex flex-column gap-1">
          <a href="../admin/dashboard.php" class="<?= active('dashboard', $active_menu) ?>"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a>
          <a href="../admin/employeeManagement.php" class="<?= active('employees', $active_menu) ?>"><i class="bi bi-people"></i> พนักงาน</a>
          <a href="../admin/departmentManagement.php" class="<?= active('departments', $active_menu) ?>"><i class="bi bi-building"></i> แผนก</a>
          <a href="../admin/productCategoryManagement.php" class="<?= active('product_types', $active_menu) ?>"><i class="bi bi-box-seam"></i> หมวดสินค้า</a>
          <a href="../admin/paymentTypeManagement.php" class="<?= active('payment_types', $active_menu) ?>"><i class="bi bi-credit-card"></i> ประเภทการจ่าย</a>
          <a href="../admin/supplierManagement.php" class="<?= active('suppliers', $active_menu) ?>"><i class="bi bi-person-badge"></i> ผู้ขาย/สมาชิก</a>
        </nav>
      </div>

      <div>
        <a href="../logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
      </div>
    </aside>
    