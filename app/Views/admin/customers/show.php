<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-lg-4"><div class="card-box">
        <h2 class="h6"><?= e($customer['CustomerName']) ?></h2>
        <div class="small">Email: <?= e($customer['Email'] ?: '—') ?></div><div class="small">SĐT: <?= e($customer['Phone'] ?: '—') ?></div>
        <div class="small">Địa chỉ: <?= e(trim(($customer['Address'] ?? '') . ' ' . ($customer['City'] ?? ''))) ?: '—' ?></div>
        <a class="btn btn-outline-secondary btn-sm mt-3" href="<?= site_url('admin/customers') ?>">← Danh sách</a>
    </div></div>
    <div class="col-lg-8"><div class="card-box p-0 table-responsive"><table class="table mb-0">
        <thead><tr><th>Mã đơn</th><th>Ngày</th><th>Trạng thái</th><th class="text-end">Tổng</th><th></th></tr></thead><tbody>
        <?php foreach ($orders as $o): ?><tr><td>#<?= (int) $o['OrderID'] ?></td><td><?= e(date('d/m/Y', strtotime($o['OrderDate']))) ?></td>
            <td><span class="badge <?= order_status_badge($o['Status']) ?>"><?= e(order_status_label($o['Status'])) ?></span></td><td class="text-end"><?= money($o['TotalAmount']) ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/orders/' . (int) $o['OrderID']) ?>">Xem</a></td></tr><?php endforeach; ?>
        <?php if (! $orders): ?><tr><td colspan="5" class="text-center text-muted py-3">Chưa có đơn hàng.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?= $this->endSection() ?>
