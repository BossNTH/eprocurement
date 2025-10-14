<?php
session_start();
require_once("../connect.php");
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$TABLE_EMPLOYEES   = "employees";
$TABLE_DEPARTMENTS = "departments";
$TABLE_CATEGORIES  = "product_categories";
$TABLE_SUPPLIERS   = "suppliers";

function scalar_or_zero(mysqli $conn, string $sql): float
{
    try {
        $res = $conn->query($sql);
        if ($res && ($row = $res->fetch_row())) return (float)$row[0];
    } catch (Throwable $e) {
        return 0;
    }
    return 0;
}

$totalEmployees   = scalar_or_zero($conn, "SELECT COUNT(*) FROM {$TABLE_EMPLOYEES}");
$totalDepartments = scalar_or_zero($conn, "SELECT COUNT(*) FROM {$TABLE_DEPARTMENTS}");
$totalCategories  = scalar_or_zero($conn, "SELECT COUNT(*) FROM {$TABLE_CATEGORIES}");
$totalSuppliers   = scalar_or_zero($conn, "SELECT COUNT(*) FROM {$TABLE_SUPPLIERS}");
$totalUsers       = scalar_or_zero($conn, "SELECT COUNT(*) FROM users");

$page_title = "แดชบอร์ดผู้ดูแลระบบ";
$active_menu = "dashboard";
require __DIR__ . '/partials/admin_header.php';
?>

<style>
    body {
        background: linear-gradient(180deg, #f0fdfa, #f9fafb);
        color: #0f172a;
        font-family: "Prompt", sans-serif;
    }

    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding: 1.5rem;
    }

    .dashboard-container h2 {
        color: #3c369e;
        font-weight: 700;
    }

    /* ===== การ์ดสถิติ ===== */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        transition: transform .2s ease, box-shadow .3s ease;
        border-top: 6px solid #3c369e;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(13, 148, 136, 0.2);
    }

    .stat-card h3 {
        margin: 0;
        font-size: 1rem;
        color: #3c369e;
        font-weight: 600;
    }

    .stat-card .value {
        font-size: 2.4rem;
        font-weight: 800;
        color: #3c369e;
        margin-top: .4rem;
    }

    .stat-card .desc {
        font-size: .9rem;
        color: #464646ff;
        margin-top: .3rem;
    }

    .c-emp {
        border-top-color: #3c369e;
    }

    .c-dep {
        border-top-color: #06b6d4;
    }

    .c-cat {
        border-top-color: #f59e0b;
    }

    .c-sup {
        border-top-color: #22c55e;
    }

    /* ===== ตารางสรุป ===== */
    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }

    .summary-card h2 {
        color: #3c369e;
        font-size: 1.3rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    table.summary {
        width: 100%;
        border-collapse: collapse;
    }

    table.summary th,
    table.summary td {
        text-align: left;
        padding: 12px 14px;
    }

    table.summary th {
        background: #3c369e;
        color: #fff;
        font-weight: 600;
        border-bottom: 2px solid #2f2b7d;
    }

    table.summary td {
        border-bottom: 1px solid #e2e8f0;
    }

    table.summary tr:hover {
        background: #9995ee2d;
    }

    table.summary td.text-right {
        text-align: right;
    }

    @media(max-width: 768px) {
        .stat-card .value {
            font-size: 1.8rem;
        }

        .dashboard-container {
            padding: 1rem;
        }
    }
</style>

<div class="dashboard-container">
    <h2><i class="bi bi-speedometer2"></i> แดชบอร์ดผู้ดูแลระบบ</h2>

    <!-- การ์ดสถิติ -->
    <div class="stat-grid">
        <div class="stat-card c-emp">
            <h3><i class="bi bi-people-fill"></i> พนักงาน</h3>
            <div class="value"><?= number_format($totalEmployees) ?></div>
            <div class="desc">จำนวนพนักงานทั้งหมดในระบบ</div>
        </div>

        <div class="stat-card c-dep">
            <h3><i class="bi bi-building"></i> แผนก</h3>
            <div class="value"><?= number_format($totalDepartments) ?></div>
            <div class="desc">จำนวนแผนกทั้งหมด</div>
        </div>

        <div class="stat-card c-cat">
            <h3><i class="bi bi-box-seam"></i> หมวดสินค้า</h3>
            <div class="value"><?= number_format($totalCategories) ?></div>
            <div class="desc">ประเภทหมวดสินค้าทั้งหมด</div>
        </div>

        <div class="stat-card c-sup">
            <h3><i class="bi bi-person-badge-fill"></i> ผู้ขาย / สมาชิก</h3>
            <div class="value"><?= number_format($totalSuppliers) ?></div>
            <div class="desc">จำนวนผู้ขายทั้งหมด</div>
        </div>
    </div>

    <!-- ตารางข้อมูลสรุป -->
    <div class="summary-card">
        <h2>สรุปข้อมูลเบื้องต้น</h2>
        <table class="summary">
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th class="text-right">จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>พนักงานทั้งหมด</td>
                    <td class="text-right"><?= number_format($totalEmployees) ?></td>
                </tr>
                <tr>
                    <td>แผนกทั้งหมด</td>
                    <td class="text-right"><?= number_format($totalDepartments) ?></td>
                </tr>
                <tr>
                    <td>หมวดสินค้า</td>
                    <td class="text-right"><?= number_format($totalCategories) ?></td>
                </tr>
                <tr>
                    <td>ผู้ขาย/สมาชิก</td>
                    <td class="text-right"><?= number_format($totalSuppliers) ?></td>
                </tr>
                <tr>
                    <td>บัญชีผู้ใช้ในระบบ</td>
                    <td class="text-right"><?= number_format($totalUsers) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/partials/admin_footer.php'; ?>