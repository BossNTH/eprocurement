<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include("../connect.php");

/* ===== ดึงรายการแผนกมาใส่ select ===== */
$departments = [];
$dept_sql = "SELECT department_id, name FROM departments ORDER BY name ASC";
if ($res = $conn->query($dept_sql)) {
    while ($row = $res->fetch_assoc()) $departments[] = $row;
}

/* ===== ดึง role จาก DB ===== */
$roles = [];
$role_sql = "SELECT DISTINCT role FROM users WHERE role IS NOT NULL AND role <> '' ORDER BY role ASC";
if ($res = $conn->query($role_sql)) {
    while ($r = $res->fetch_assoc()) $roles[] = trim($r['role']);
}

/* ===== รวม role มาตรฐาน ===== */
$defaults = ['employee', 'manager', 'admin', 'seller', 'procurement', 'procurement_manager'];
$roles = array_values(array_unique(array_merge($defaults, $roles)));

// เรียงลำดับ
$preferredOrder = ['employee', 'manager', 'procurement', 'procurement_manager', 'seller', 'admin'];
usort($roles, function ($a, $b) use ($preferredOrder) {
    $pa = array_search($a, $preferredOrder);
    $pa = ($pa === false) ? 999 : $pa;
    $pb = array_search($b, $preferredOrder);
    $pb = ($pb === false) ? 999 : $pb;
    return $pa <=> $pb;
});

require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มพนักงาน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f6f7fb;
        }

        .page-head {
            background: linear-gradient(135deg, #1e293b, #5b9dff);
            color: #fff;
            border-radius: 18px;
            padding: 20px 22px;
            box-shadow: 0 10px 25px rgba(13, 110, 253, .25);
        }

        .card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
        }

        .input-group-text {
            background: #f1f4f9;
            border: none;
        }

        .form-control,
        .form-select {
            border: none;
            background: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15);
        }

        .required:after {
            content: " *";
            color: #dc3545;
        }

        /* ===== Layout กับ Sidebar ===== */
        .admin-main {
            margin-left: 260px;
            /* กว้างของ sidebar ที่คุณใช้ (เช่น 240–260px) */
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* กรณี sidebar ซ่อนไว้ (เช่นใน mobile) */
        @media (max-width: 991px) {
            .admin-main {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body style="background:#f4f6fb;">

    <div class="container-fluid py-4 px-3">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">

                <!-- หัวข้อหน้า -->
                <div class="card border-0 mb-4 shadow-sm rounded-4 overflow-hidden">
                    <div class="bg-gradient-primary text-white p-4"
                        style="background:linear-gradient(135deg,#0061ff,#60efff);">
                        <h3 class="mb-1"><i class="bi bi-person-plus me-2"></i>เพิ่มพนักงาน</h3>
                        <p class="mb-0 opacity-75">กรอกข้อมูลพื้นฐานของพนักงานให้ครบถ้วนก่อนบันทึก</p>
                    </div>
                </div>

                <!-- ฟอร์มเพิ่มพนักงาน -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form id="employeeForm" action="employee_save.php" method="POST" novalidate>
                            <div class="row g-3">

                                <!-- ชื่อ -->
                                <div class="col-md-6">
                                    <label class="form-label required">ชื่อพนักงาน</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                        <input type="text" name="full_name" class="form-control" placeholder="กรอกชื่อ-นามสกุล" required>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label required">Email (ใช้เป็น Username)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required>
                                    </div>
                                </div>

                                <!-- เบอร์โทร -->
                                <div class="col-md-6">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" name="phone" class="form-control"
                                            placeholder="เช่น 08x-xxx-xxxx"
                                            pattern="^[0-9+\-\s()]{8,20}$">
                                    </div>
                                </div>

                                <!-- แผนก -->
                                <div class="col-md-6">
                                    <label class="form-label required">แผนก</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                        <select name="department_id" class="form-select" required>
                                            <option value="">-- เลือกแผนก --</option>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?= htmlspecialchars($d['department_id']) ?>">
                                                    <?= htmlspecialchars($d['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- สถานะ -->
                                <div class="col-md-6">
                                    <label class="form-label required">สถานะพนักงาน</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-toggle2-on"></i></span>
                                        <select name="status" class="form-select" required>
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <label class="form-label required">สิทธิ์การใช้งาน (Role)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                        <select name="role" class="form-select" required>
                                            <option value="">-- เลือก Role --</option>
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label class="form-label">รหัสผ่านเริ่มต้น</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control" placeholder="เว้นว่าง = Emp123456">
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="employeeManagement.php" class="btn btn-light">ยกเลิก</a>
                                <button type="reset" class="btn btn-outline-secondary">ล้างฟอร์ม</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> บันทึก
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('employeeForm');
            form.addEventListener('submit', async (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                e.preventDefault();
                const email = document.getElementById('email').value.trim();
                const resp = await fetch('check_email.php?email=' + encodeURIComponent(email));
                const data = await resp.json();
                if (data.exists) {
                    Swal.fire('อีเมลซ้ำ', 'อีเมลนี้ถูกใช้งานแล้วในระบบ', 'error');
                } else {
                    form.submit();
                }
            });
        })();
    </script>
</body>

</html>
<?php require __DIR__ . '/partials/admin_footer.php'; ?>