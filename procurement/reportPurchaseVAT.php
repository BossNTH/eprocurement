<?php
require_once __DIR__ . '/../auth.php';
requireRole('procurement');
// Sample data for VAT report
$reportRows = [
    [
        'tax_invoice_no' => 'INV202510-001',
        'tax_invoice_date' => '2025-10-10',
        'po_no' => 'PO202510-0002',
        'supplier_name' => 'Supplier A',
        'supplier_tax_id' => '1234567890123',
        'base_amount' => 10000,
        'vat_amount' => 700,
        'total_amount' => 10700,
        'branch_code' => 'B001',
        'payment_ref' => 'PAY123',
        'budget_code' => 'BC001'
    ],
    [
        'tax_invoice_no' => 'INV202510-002',
        'tax_invoice_date' => '2025-10-11',
        'po_no' => 'PO202510-0003',
        'supplier_name' => 'Supplier B',
        'supplier_tax_id' => '9876543210987',
        'base_amount' => 20000,
        'vat_amount' => 1400,
        'total_amount' => 21400,
        'branch_code' => 'B002',
        'payment_ref' => 'PAY124',
        'budget_code' => 'BC002'
    ],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานภาษีซื้อ</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>รายงานภาษีซื้อ</h1>
    <table>
        <thead>
            <tr>
                <th>เลขที่ใบกำกับภาษี</th>
                <th>วันที่ใบกำกับภาษี</th>
                <th>เลขที่ PO</th>
                <th>ชื่อผู้ขาย</th>
                <th>เลขประจำตัวผู้เสียภาษี</th>
                <th>มูลค่าก่อน VAT</th>
                <th>มูลค่า VAT</th>
                <th>มูลค่ารวม</th>
                <th>รหัสสาขา</th>
                <th>เลขที่อ้างอิงการจ่าย</th>
                <th>รหัสงบประมาณ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportRows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['tax_invoice_no']) ?></td>
                    <td><?= htmlspecialchars($row['tax_invoice_date']) ?></td>
                    <td><?= htmlspecialchars($row['po_no']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_tax_id']) ?></td>
                    <td><?= number_format($row['base_amount'], 2) ?></td>
                    <td><?= number_format($row['vat_amount'], 2) ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['branch_code']) ?></td>
                    <td><?= htmlspecialchars($row['payment_ref']) ?></td>
                    <td><?= htmlspecialchars($row['budget_code']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">กลับแดชบอร์ด</a></p>
</body>
</html>