<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<form class="card-box mb-3 row g-2 align-items-end" method="get">
    <div class="col-md-3"><label class="form-label small">Trạng thái</label><select class="form-select" name="status">
        <option value="">Tất cả</option>
        <?php foreach (['Pending', 'Confirmed', 'Shipping', 'Completed', 'Cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-4"><label class="form-label small">Khách (tên / SĐT)</label><input class="form-control" name="keyword" value="<?= e($keyword) ?>"></div>
    <div class="col-md-3"><label class="form-label small">Ngày đặt</label><input class="form-control" type="date" name="date" value="<?= e($date) ?>"></div>
    <div class="col-md-2 d-flex gap-2"><button class="btn btn-dark">Lọc</button><a class="btn btn-outline-secondary" href="<?= site_url('admin/orders') ?>">Xóa</a></div>
</form>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>Mã</th><th>Khách hàng</th><th>Ngày đặt</th><th>Trạng thái</th><th class="text-end">Tổng tiền</th><th></th></tr></thead><tbody>
    <?php foreach ($orders as $o): ?>
        <tr><td>#<?= (int) $o['OrderID'] ?></td><td><?= e($o['CustomerName']) ?><div class="small text-muted"><?= e($o['Phone']) ?></div></td>
            <td><?= e(date('d/m/Y H:i', strtotime($o['OrderDate']))) ?></td>
            <td><span class="badge <?= order_status_badge($o['Status']) ?>"><?= e(order_status_label($o['Status'])) ?></span></td>
            <td class="text-end"><?= money($o['TotalAmount']) ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/orders/' . (int) $o['OrderID']) ?>">Xem</a></td></tr>
    <?php endforeach; ?>
    <?php if (! $orders): ?><tr><td colspan="6" class="text-center text-muted py-4">Không có đơn hàng.</td></tr><?php endif; ?>
    </tbody></table></div>
<?= $this->endSection() ?>
