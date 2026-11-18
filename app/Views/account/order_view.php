<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Đơn hàng #<?= (int) $order['OrderID'] ?></h1></div></div>
<div class="container section"><div class="panel">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div><span class="badge <?= order_status_badge($order['Status']) ?> fs-6"><?= e(order_status_label($order['Status'])) ?></span>
            <span class="text-muted ms-2"><?= e(date('d/m/Y H:i', strtotime($order['OrderDate']))) ?></span></div>
        <?php if ($order['Status'] === 'Pending'): ?>
            <form action="<?= site_url('orders/' . (int) $order['OrderID'] . '/cancel') ?>" method="post" onsubmit="return confirm('Hủy đơn hàng này?')">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hủy đơn</button></form>
        <?php endif; ?>
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Sản phẩm</th><th>Màu / Size</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><a class="fw-semibold" href="<?= site_url('products/' . (int) $it['ProductID']) ?>"><?= e($it['ProductName']) ?></a>
                    <?php if ($order['Status'] === 'Completed'): ?>
                        <div><a class="small text-decoration-underline" href="<?= site_url('products/' . (int) $it['ProductID']) ?>#reviews"><i class="bi bi-star"></i> <?= $it['ReviewID'] ? 'Sửa đánh giá' : 'Viết đánh giá' ?></a></div>
                    <?php endif; ?></td>
                <td><?= e($it['Color'] ?: '—') ?> / <?= e($it['Size'] ?: '—') ?></td>
                <td class="text-end"><?= money($it['UnitPrice']) ?></td>
                <td class="text-end"><?= (int) $it['Quantity'] ?></td>
                <td class="text-end"><?= money($it['UnitPrice'] * $it['Quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="4" class="text-end">Tổng cộng</th><th class="text-end"><?= money($order['TotalAmount']) ?></th></tr></tfoot>
    </table></div>
    <a class="btn btn-outline-dark btn-sm" href="<?= site_url('orders') ?>">← Danh sách đơn hàng</a>
</div></div>
<?= $this->endSection() ?>
