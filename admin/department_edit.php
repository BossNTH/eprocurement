<?php
// Devolper/department_edit.php
session_start();

require_once "../connect.php";

$deptId = intval($_GET['id'] ?? 0);
if ($deptId <= 0) {
  echo "<script>alert('ID แผนกไม่ถูกต้อง'); location='departmentManagement.php';</script>";
  exit;
}

/* ดึงข้อมูลแผนก */
$sql = "SELECT department_id, name, head_employee_id
        FROM departments
        WHERE department_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$dept = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dept) {
  echo "<script>alert('ไม่พบแผนกที่ระบุ'); location='departmentManagement.php';</script>";
  exit;
}

/* ดึงรายชื่อพนักงานในแผนกนี้ (ไว้แสดงลิสต์ + ให้เลือกเป็นหัวหน้า) */
$emps = [];
$q = $conn->prepare("
  SELECT employee_id, full_name, email, phone
  FROM employees
  WHERE department_id=?
  ORDER BY full_name
");
$q->bind_param("i", $deptId);
$q->execute();
$res = $q->get_result();
while ($r = $res->fetch_assoc()) $emps[] = $r;
$q->close();

require __DIR__ . '/partials/admin_header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แก้ไขแผนก</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body{ background:#f6f7fb; }
    .card{ border:0; border-radius:18px; box-shadow:0 10px 25px rgba(0,0,0,.06); }
    .page-head{ background:linear-gradient(135deg,#0d6efd,#5b9dff); color:#fff; border-radius:18px; padding:18px 22px; }
    .input-group-text{ background:#f1f4f9; border:none; }
    .form-control, .form-select{ border:none; background:#fff; }
    .form-control:focus, .form-select:focus{ box-shadow:0 0 0 .25rem rgba(13,110,253,.15); }
    .table-hover tbody tr:hover { background:#f0f6ff; }
    .head-badge { font-size:.75rem; }
  </style>
</head>
<body>

<div class="container py-4">

  <div class="page-head mb-4">
    <h4 class="mb-0"><i class="bi bi-building-gear me-2"></i>แก้ไขแผนก</h4>
    <div class="small opacity-75">แผนก: <?= htmlspecialchars($dept['name']) ?> (ID <?= (int)$dept['department_id'] ?>)</div>
  </div>

  <!-- ฟอร์มแก้ไขชื่อแผนก + หัวหน้า -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" action="department_update.php" novalidate>
        <input type="hidden" name="department_id" value="<?= (int)$dept['department_id'] ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">ชื่อแผนก <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-building"></i></span>
              <input type="text" name="name" class="form-control"
                     value="<?= htmlspecialchars($dept['name']) ?>" required maxlength="100">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">หัวหน้าแผนก (ไม่บังคับ)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
              <select name="head_employee_id" id="headSelect" class="form-select">
                <option value="">— ไม่กำหนด —</option>
                <?php foreach ($emps as $e): ?>
                  <option value="<?= (int)$e['employee_id'] ?>"
                    <?= ((int)$dept['head_employee_id']===(int)$e['employee_id'])?'selected':''; ?>>
                    <?= htmlspecialchars($e['full_name']).' (ID '.$e['employee_id'].')' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-text">สามารถเลือกได้จากรายชื่อพนักงานในแผนกด้านล่าง หรือกดปุ่ม “ตั้งเป็นหัวหน้า” ในตาราง</div>
          </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> บันทึก
          </button>
          <a href="departmentManagement.php" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
      </form>
    </div>
  </div>

  <!-- รายชื่อพนักงานในแผนก -->
  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <h5 class="mb-0">รายชื่อพนักงานในแผนก</h5>
          <small class="text-muted">ทั้งหมด <?= number_format(count($emps)) ?> คน</small>
        </div>
        <div class="w-50">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="empSearch" class="form-control" placeholder="ค้นหาชื่อ/อีเมล/เบอร์โทร...">
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:100px;">รหัส</th>
              <th>ชื่อพนักงาน</th>
              <th>อีเมล</th>
              <th>เบอร์โทร</th>
              <th class="text-center" style="width:160px;">การจัดการ</th>
            </tr>
          </thead>
          <tbody id="empTbody">
            <?php if ($emps): ?>
              <?php foreach ($emps as $e): ?>
                <?php $isHead = ((int)$dept['head_employee_id']===(int)$e['employee_id']); ?>
                <tr>
                  <td class="text-primary fw-semibold"><?= (int)$e['employee_id'] ?></td>
                  <td>
                    <?= htmlspecialchars($e['full_name']) ?>
                    <?php if ($isHead): ?>
                      <span class="badge bg-warning text-dark head-badge ms-1"><i class="bi bi-star-fill me-1"></i>หัวหน้า</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($e['email'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($e['phone'] ?: '—') ?></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary set-head-btn"
                            data-id="<?= (int)$e['employee_id'] ?>"
                            data-name="<?= htmlspecialchars($e['full_name']) ?>">
                      <i class="bi bi-person-check"></i> ตั้งเป็นหัวหน้า
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีพนักงานในแผนกนี้</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const $ = (s, r=document)=>r.querySelector(s);
  const $$= (s, r=document)=>[...r.querySelectorAll(s)];
  const norm = (t)=> (t||'').toString().toLowerCase().trim();

  // ตั้งหัวหน้าจากรายการพนักงาน (จะไป set ค่า select ให้)
  const headSelect = $('#headSelect');
  $$('.set-head-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;
      const nm = btn.dataset.name || '';
      if(headSelect){
        headSelect.value = id;
        // แสง feedback นิดหน่อย
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> เลือกแล้ว';
        setTimeout(()=>{
          btn.classList.add('btn-outline-primary');
          btn.classList.remove('btn-primary');
          btn.innerHTML = '<i class="bi bi-person-check"></i> ตั้งเป็นหัวหน้า';
        }, 1000);
      }
    });
  });

  // ค้นหารายชื่อ
  const empSearch = $('#empSearch');
  const empTbody  = $('#empTbody');
  empSearch?.addEventListener('input', ()=>{
    const q = norm(empSearch.value);
    if(!empTbody) return;
    let visible = 0;
    [...empTbody.rows].forEach(tr=>{
      const t = norm(tr.innerText);
      const show = q==='' || t.includes(q);
      tr.style.display = show ? '' : 'none';
      if(show) visible++;
    });
  });
})();
</script>
</body>
</html>
<?php require __DIR__ . '/partials/admin_footer.php'; ?>
