<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Đơn hàng của tôi</h1></div></div>
<div class="container section">
<?php if (! $orders): ?>
    <div class="empty"><i class="bi bi-receipt"></i>Bạn chưa có đơn hàng nào.<div class="mt-3"><a class="btn btn-dark" href="<?= site_url('products') ?>">Mua sắm ngay</a></div></div>
<?php else: ?>
    <div class="panel table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Trạng thái</th><th class="text-end">Tổng tiền</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= (int) $o['OrderID'] ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($o['OrderDate']))) ?></td>
                <td><span class="badge <?= order_status_badge($o['Status']) ?>"><?= e(order_status_label($o['Status'])) ?></span></td>
                <td class="text-end"><?= money($o['TotalAmount']) ?></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('orders/' . (int) $o['OrderID']) ?>">Chi tiết</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
