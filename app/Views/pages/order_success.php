<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container section">
    <div class="panel mx-auto" style="max-width:720px">
        <div class="text-center mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size:3.2rem"></i>
            <h1 class="h3 mt-2">Đặt hàng thành công!</h1>
            <p class="text-muted mb-0">Mã đơn hàng <strong>#<?= (int) $order['OrderID'] ?></strong> – chúng tôi sẽ sớm liên hệ xác nhận.</p>
        </div>
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th>Sản phẩm</th><th>Size / Màu</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr><td><?= e($it['ProductName']) ?></td><td><?= e($it['Size'] ?: '—') ?> / <?= e($it['Color'] ?: '—') ?></td><td class="text-end"><?= (int) $it['Quantity'] ?></td><td class="text-end"><?= money($it['UnitPrice'] * $it['Quantity']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Tổng cộng</th><th class="text-end"><?= money($order['TotalAmount']) ?></th></tr></tfoot>
        </table></div>
        <p class="small text-muted">Giao tới: <?= e($order['CustomerName']) ?> · <?= e($order['Phone']) ?> · <?= e($order['Address']) ?></p>
        <div class="d-flex gap-2 justify-content-center">
            <a class="btn btn-dark" href="<?= site_url('orders/' . (int) $order['OrderID']) ?>">Xem đơn hàng</a>
            <a class="btn btn-outline-dark" href="<?= site_url('products') ?>">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
