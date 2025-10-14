<?php
require_once "../connect.php";
require_once __DIR__ . "/partials/procurement_header.php";

/* ------------------ ดึงข้อมูลสินค้า ------------------ */
$search = trim($_GET['q'] ?? '');
$where = '';
if ($search !== '') {
    $where = "WHERE p.product_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql = "
  SELECT p.product_id, p.product_name, p.description, p.qty_onhand, 
         p.reorder_point, p.unit_price, p.uom, c.name, 
         c.category_id, p.created_at, p.updated_at
  FROM products p
  LEFT JOIN product_categories c ON p.category_id = c.category_id
  $where
  ORDER BY p.product_id DESC
";
$products = $conn->query($sql);
$categories = $conn->query("SELECT category_id, name FROM product_categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า | ฝ่ายจัดซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #e9e9e9ff;
            color: #353535ff;
            font-family: 'Prompt', sans-serif;
            padding-left: 260px;
        }

        .container-fluid {
            padding: 2rem;
        }

        .card {
            background: #ffffffff;
            border-radius: 16px;
            border: 1px solid rgba(20, 184, 166, 0.3);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .3);
        }

        .table thead {
            background: #334155;
            color: #a5f3fc;
        }

        .table tbody tr:hover {
            background-color: rgba(20, 184, 166, 0.1);
        }

        .btn-add {
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            border: 0;
            color: #fff;
            border-radius: 8px;
        }

        .btn-add:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-box-seam me-2"></i>จัดการสินค้า</h2>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <form class="d-flex" method="get">
                <input class="form-control me-2" type="search" name="q" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                <?php if ($search): ?>
                    <a href="manage_products.php" class="btn btn-outline-secondary ms-2">ล้าง</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า</button>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-center">
                            <th width="8%">รหัส</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th>หน่วยนับ</th>
                            <th>ราคาต่อหน่วย</th>
                            <th>คงเหลือ</th>
                            <th>จุดสั่งซื้อใหม่</th>
                            <th>อัปเดตล่าสุด</th>
                            <th width="12%">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): while ($p = $products->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?= $p['product_id'] ?></td>
                                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                                    <td><?= htmlspecialchars($p['name'] ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($p['uom']) ?></td>
                                    <td class="text-end"><?= number_format($p['unit_price'], 2) ?></td>
                                    <td class="text-end"><?= number_format($p['qty_onhand'], 2) ?></td>
                                    <td class="text-end"><?= number_format($p['reorder_point'], 2) ?></td>
                                    <td class="text-center"><?= date("d/m/Y H:i", strtotime($p['updated_at'])) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit"
                                            data-id="<?= $p['product_id'] ?>"
                                            data-name="<?= htmlspecialchars($p['product_name']) ?>"
                                            data-desc="<?= htmlspecialchars($p['description']) ?>"
                                            data-qty="<?= htmlspecialchars($p['qty_onhand']) ?>"
                                            data-reorder="<?= htmlspecialchars($p['reorder_point']) ?>"
                                            data-price="<?= htmlspecialchars($p['unit_price']) ?>"
                                            data-uom="<?= htmlspecialchars($p['uom']) ?>"
                                            data-cat="<?= htmlspecialchars($p['category_id']) ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $p['product_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">ไม่มีข้อมูลสินค้า</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มสินค้า -->
    <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="post" action="product_save.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>เพิ่มสินค้าใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">หมวดหมู่</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php
                            $categories->data_seek(0);
                            while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-3"><label class="form-label">คงเหลือ</label><input type="number" name="qty_onhand" step="0.01" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">จุดสั่งซื้อใหม่</label><input type="number" name="reorder_point" step="0.01" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">ราคาต่อหน่วย</label><input type="number" name="unit_price" step="0.01" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">หน่วยนับ (UOM)</label><input type="text" name="uom" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-success">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal แก้ไขสินค้า -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="post" action="product_update.php">
                <input type="hidden" name="product_id" id="edit_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>แก้ไขสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">หมวดหมู่</label>
                        <select name="category_id" id="edit_category" class="form-select" required>
                            <?php
                            $categories->data_seek(0);
                            while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-3"><label class="form-label">คงเหลือ</label><input type="number" name="qty_onhand" step="0.01" id="edit_qty" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">จุดสั่งซื้อใหม่</label><input type="number" name="reorder_point" step="0.01" id="edit_reorder" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">ราคาต่อหน่วย</label><input type="number" name="unit_price" step="0.01" id="edit_price" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">หน่วยนับ (UOM)</label><input type="text" name="uom" id="edit_uom" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-info text-white">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', event => {
            const btn = event.relatedTarget;
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_desc').value = btn.getAttribute('data-desc');
            document.getElementById('edit_qty').value = btn.getAttribute('data-qty');
            document.getElementById('edit_reorder').value = btn.getAttribute('data-reorder');
            document.getElementById('edit_price').value = btn.getAttribute('data-price');
            document.getElementById('edit_uom').value = btn.getAttribute('data-uom');
            document.getElementById('edit_category').value = btn.getAttribute('data-cat');
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'ลบสินค้า?',
                text: 'คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((r) => {
                if (r.isConfirmed) {
                    window.location = 'product_delete.php?id=' + id;
                }
            });
        }
    </script>
</body>

</html>