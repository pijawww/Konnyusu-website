<?php
// admin/reports/export.php - Export semua pesanan ke PDF via browser print
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/order.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

$allOrders = getAllOrders();

function getStatusLabel(string $status): string {
    $labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function getOrderTypeLabel(string $type): string {
    $labels = [
        'dine_in' => 'Dine In',
        'takeaway' => 'Take Away',
        'delivery' => 'Delivery'
    ];
    return $labels[$type] ?? $type;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Laporan Semua Pesanan - Konnyusu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --primary:#2d2a26;--accent:#c49a6c;--text-dark:#2d2a26;--text-muted:#6b6b6b;--border:#e9e6e0;
    --font-display:'Playfair Display',serif;--font-body:'DM Sans',sans-serif;
}
*{box-sizing:border-box;}
body{font-family:var(--font-body);color:var(--text-dark);background:#fff;}
.no-print{display:none;}
.header{text-align:center;padding:2rem 0;border-bottom:2px dashed var(--border);}
.header h1{font-family:var(--font-display);font-size:1.7rem;color:var(--primary);margin:0 0 .25rem 0;}
.header p{font-size:.85rem;color:var(--text-muted);margin:0;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin:1.25rem 0;}
.stat-card{border:1px solid var(--border);padding:1rem;text-align:center;border-radius:8px;}
.stat-card__val{font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--primary);}
.stat-card__lbl{font-size:.75rem;color:var(--text-muted);margin-top:.2rem;}
table{width:100%;border-collapse:collapse;margin-top:1rem;}
th{font-size:.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);padding:.6rem;text-align:left;border-bottom:1.5px solid var(--border);background:#f9f7f4;}
td{padding:.6rem;font-size:.8rem;color:var(--text-dark);border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
.order-id{font-family:monospace;font-weight:700;color:var(--primary);}
.status{display:inline-block;padding:.2rem .5rem;border-radius:4px;font-size:.72rem;font-weight:600;}
.status-selesai{background:#ecfaf4;color:#059669;}
.status-menunggu{background:#fff8ec;color:#d97706;}
.status-batal{background:#fdf0f0;color:#dc2626;}
.order-items{font-size:.72rem;color:var(--text-muted);margin-top:.2rem;}
@media print{
    body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .no-print{display:none!important;}
}
</style>
</head>
<body onload="window.print()">

<div class="no-print" style="position:fixed;top:10px;right:10px;z-index:9999;">
    <button onclick="window.history.back()" style="padding:.5rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:6px;cursor:pointer;">Kembali</button>
</div>

<div class="header">
    <h1>☕ Konnyusu</h1>
    <p>Laporan Semua Pesanan</p>
    <p style="margin-top:.5rem;font-size:.8rem;">Dicetak pada: <?= date('d F Y, H:i') ?></p>
</div>

<?php
$totalOrders = count($allOrders);
$totalRevenue = array_sum(array_column($allOrders, 'total'));
$completedOrders = count(array_filter($allOrders, fn($o) => $o['order_status'] === 'completed'));
$pendingOrders = count(array_filter($allOrders, fn($o) => $o['order_status'] === 'pending' || $o['order_status'] === 'processing'));
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card__val"><?= $totalOrders ?></div>
        <div class="stat-card__lbl">Total Pesanan</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__val"><?= $completedOrders ?></div>
        <div class="stat-card__lbl">Pesanan Selesai</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__val"><?= $pendingOrders ?></div>
        <div class="stat-card__lbl">Menunggu</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__val"><?= formatRupiah($totalRevenue) ?></div>
        <div class="stat-card__lbl">Total Pendapatan</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Pelanggan</th>
            <th>Tanggal</th>
            <th>Tipe</th>
            <th>Item</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($allOrders as $order):
        $items = getOrderItems($order['order_id']);
        $itemList = [];
        foreach ($items as $it) {
            $itemList[] = ($it['name'] ?? 'Produk') . ' x' . $it['quantity'];
        }
        $statusClass = $order['order_status'] === 'completed' ? 'status-selesai' : ($order['order_status'] === 'cancelled' ? 'status-batal' : 'status-menunggu');
    ?>
    <tr>
        <td><span class="order-id">#<?= $order['order_id'] ?></span></td>
        <td><?= htmlspecialchars($order['user_name'] ?? 'Pelanggan') ?></td>
        <td style="font-size:.78rem;color:var(--text-muted);"><?= date('d M Y, H:i', strtotime($order['order_date'])) ?></td>
        <td><?= getOrderTypeLabel($order['order_type'] ?? 'dine_in') ?></td>
        <td>
            <div><?= implode(', ', $itemList) ?></div>
        </td>
        <td style="font-weight:700;"><?= formatRupiah($order['total']) ?></td>
        <td><span class="status <?= $statusClass ?>"><?= getStatusLabel($order['order_status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
