<?php
session_start();
require_once __DIR__ . "/partials/admin_header.php";
$db = $GLOBALS['conn'] ?? $conn;

/* ---------- Flash helper ---------- */
function flash($msg,$type='success'){ $_SESSION['flash']=['m'=>$msg,'t'=>$type]; }
function show_flash(){
  if(empty($_SESSION['flash'])) return;
  $f=$_SESSION['flash']; unset($_SESSION['flash']);
  echo "<div class='alert alert-{$f['t']} alert-dismissible fade show' role='alert'>"
      .htmlspecialchars($f['m'])."<button class='btn-close' data-bs-dismiss='alert'></button></div>";
}

/* ---------- CSRF ---------- */
if(!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
$CSRF=$_SESSION['csrf'];
function check_csrf($t){return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'],$t??'');}

/* ---------- Actions ---------- */
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??''; $csrf=$_POST['csrf']??'';
  if(!check_csrf($csrf)){ flash('โทเคนไม่ถูกต้อง','danger'); header("Location: paymentTypeManagement.php"); exit; }

  if($action==='create'){
    $name=trim($_POST['name']??'');
    if($name===''){ flash('กรุณากรอกชื่อ','danger'); }
    else{
      $ins=$db->prepare("INSERT INTO payment_types(name) VALUES(?)");
      $ins->bind_param("s",$name);
      $ins->execute()?flash('เพิ่มประเภทการจ่ายเรียบร้อย','success'):flash('เพิ่มไม่สำเร็จ: '.$db->error,'danger');
      $ins->close();
    }
    header("Location: paymentTypeManagement.php"); exit;
  }

  if($action==='update'){
    $id=(int)($_POST['id']??0); $name=trim($_POST['name']??'');
    if($id<=0||$name===''){ flash('ข้อมูลไม่ครบถ้วน','danger'); }
    else{
      $upd=$db->prepare("UPDATE payment_types SET name=? WHERE id=?");
      $upd->bind_param("si",$name,$id);
      $upd->execute()?flash('บันทึกการแก้ไขเรียบร้อย','success'):flash('แก้ไขไม่สำเร็จ: '.$db->error,'danger');
      $upd->close();
    }
    header("Location: paymentTypeManagement.php"); exit;
  }

  if($action==='delete'){
    $id=(int)($_POST['id']??0);
    if($id>0){
      $del=$db->prepare("DELETE FROM payment_types WHERE id=?");
      $del->bind_param("i",$id);
      $del->execute()?flash('ลบรายการเรียบร้อย','success'):flash('ลบไม่สำเร็จ: '.$db->error,'danger');
      $del->close();
    }
    header("Location: paymentTypeManagement.php"); exit;
  }
}

/* ---------- Read ---------- */
$q=trim($_GET['q']??'');
$where=" WHERE 1=1 "; $params=[]; $types='';
if($q!==''){ $where.=" AND name LIKE ? "; $params[]="%{$q}%"; $types.='s'; }

/* pagination */
$perPage=10; $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
$sqlCount="SELECT COUNT(*) total FROM payment_types {$where}";
$stmt=$db->prepare($sqlCount);
if($params) $stmt->bind_param($types,...$params);
$stmt->execute();
$total=(int)$stmt->get_result()->fetch_assoc()['total'];
$totalPages=max(1,(int)ceil($total/$perPage));
$stmt->close();

/* list */
$sql="SELECT id,name FROM payment_types {$where} ORDER BY id ASC LIMIT ?,?";
$params2=$params; $types2=$types."ii"; $params2[]=$offset; $params2[]=$perPage;
$stmt=$db->prepare($sql);
$stmt->bind_param($types2,...$params2);
$stmt->execute();
$rs=$stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการประเภทการจ่ายเงิน</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body { background:#f6f7fb; }
    .page-head {
      background:linear-gradient(135deg,#0d6efd,#5b9dff);
      color:#fff;
      border-radius:18px;
      padding:20px 22px;
      box-shadow:0 10px 25px rgba(13,110,253,.25);
    }
    .card {
      border:0;
      border-radius:18px;
      box-shadow:0 10px 25px rgba(0,0,0,.06);
    }
    table th {
      background:#f1f4f9;
      font-weight:600;
    }
    .btn-add {
      background:linear-gradient(135deg,#198754,#20c997);
      color:#fff;
      border:0;
      border-radius:10px;
      box-shadow:0 4px 10px rgba(25,135,84,.2);
    }
    .btn-add:hover { opacity:.9; }
  </style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="page-head d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1"><i class="bi bi-wallet2 me-2"></i>จัดการประเภทการจ่ายเงิน</h2>
      <div class="small opacity-75">เพิ่ม แก้ไข หรือลบประเภทการจ่ายเงิน</div>
    </div>
    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
      <i class="bi bi-plus-circle me-1"></i> เพิ่มประเภทการจ่าย
    </button>
  </div>

  <?php show_flash(); ?>

  <form class="d-flex gap-2 mb-3" method="get">
    <input class="form-control" type="search" name="q" placeholder="ค้นหาชื่อประเภทการจ่าย..." value="<?= htmlspecialchars($q) ?>">
    <button class="btn btn-primary"><i class="bi bi-search"></i></button>
  </form>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr class="text-center">
              <th width="10%">ID</th>
              <th>ชื่อประเภทการจ่าย</th>
              <th width="20%">การจัดการ</th>
            </tr>
          </thead>
          <tbody>
          <?php if($rs->num_rows): while($r=$rs->fetch_assoc()): ?>
            <tr>
              <td class="text-center"><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#modalEdit" data-id="<?= $r['id'] ?>"
                        data-name="<?= htmlspecialchars($r['name']) ?>">
                  <i class="bi bi-pencil-square"></i> แก้ไข
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $r['id'] ?>)">
                  <i class="bi bi-trash"></i> ลบ
                </button>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post">
      <input type="hidden" name="csrf" value="<?= $CSRF ?>">
      <input type="hidden" name="action" value="create">
      <div class="modal-header border-success text-success">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทการจ่าย</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">ชื่อประเภทการจ่าย</label>
        <input name="name" class="form-control" required maxlength="100">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post">
      <input type="hidden" name="csrf" value="<?= $CSRF ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="e_id">
      <div class="modal-header border-info text-info">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>แก้ไขประเภทการจ่าย</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">ชื่อประเภทการจ่าย</label>
        <input name="name" id="e_name" class="form-control" required maxlength="100">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const modalEdit=document.getElementById('modalEdit');
  modalEdit?.addEventListener('show.bs.modal',ev=>{
    const btn=ev.relatedTarget;
    document.getElementById('e_id').value=btn.getAttribute('data-id');
    document.getElementById('e_name').value=btn.getAttribute('data-name');
  });
  function confirmDelete(id){
    Swal.fire({
      title:'ยืนยันการลบ?',
      text:'คุณต้องการลบประเภทการจ่ายนี้หรือไม่',
      icon:'warning',
      showCancelButton:true,
      confirmButtonColor:'#d33',
      cancelButtonColor:'#6c757d',
      confirmButtonText:'ลบข้อมูล',
      cancelButtonText:'ยกเลิก'
    }).then((r)=>{
      if(r.isConfirmed){
        const f=document.createElement('form');
        f.method='post'; f.innerHTML=`<input type="hidden" name="csrf" value="<?= $CSRF ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(f); f.submit();
      }
    });
  }
</script>
</body>
</html>
<?php
$stmt->close();
require_once __DIR__ . "/partials/admin_footer.php";
?>
