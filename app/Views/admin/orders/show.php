<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-lg-8"><div class="card-box">
        <div class="d-flex justify-content-between mb-3"><h2 class="h6 mb-0">Đơn #<?= (int) $order['OrderID'] ?> · <?= e(date('d/m/Y H:i', strtotime($order['OrderDate']))) ?></h2>
            <span class="badge <?= order_status_badge($order['Status']) ?>"><?= e(order_status_label($order['Status'])) ?></span></div>
        <div class="table-responsive"><table class="table">
            <thead><tr><th>Sản phẩm</th><th>Màu / Size</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead><tbody>
            <?php foreach ($items as $it): ?>
                <tr><td><?= e($it['ProductName']) ?><div class="small text-muted"><?= e($it['ProductCode']) ?></div></td><td><?= e($it['Color'] ?: '—') ?> / <?= e($it['Size'] ?: '—') ?></td>
                    <td class="text-end"><?= money($it['UnitPrice']) ?></td><td class="text-end"><?= (int) $it['Quantity'] ?></td><td class="text-end"><?= money($it['UnitPrice'] * $it['Quantity']) ?></td></tr>
            <?php endforeach; ?></tbody>
            <tfoot><tr><th colspan="4" class="text-end">Tổng cộng</th><th class="text-end"><?= money($order['TotalAmount']) ?></th></tr></tfoot>
        </table></div>
    </div></div>
    <div class="col-lg-4">
        <div class="card-box mb-3">
            <h2 class="h6">Khách hàng</h2>
            <div class="fw-semibold"><?= e($order['CustomerName']) ?></div>
            <div><?= e($order['Phone']) ?></div><div class="text-muted small"><?= e(trim(($order['Address'] ?? '') . ' ' . ($order['City'] ?? ''))) ?></div>
            <?php if ($order['LastName']): ?><hr><div class="small text-muted">Nhân viên xử lý: <?= e($order['LastName'] . ' ' . $order['FirstName']) ?></div><?php endif; ?>
        </div>
        <?php if ($next): ?>
        <form class="card-box" method="post" action="<?= site_url('admin/orders/' . (int) $order['OrderID'] . '/status') ?>">
            <?= csrf_field() ?>
            <h2 class="h6">Cập nhật trạng thái</h2>
            <div class="mb-2"><select class="form-select" name="status" required>
                <?php foreach ($next as $s): ?><option value="<?= $s ?>"><?= e(order_status_label($s)) ?></option><?php endforeach; ?>
            </select></div>
            <div class="mb-3"><label class="form-label small">Đơn vị vận chuyển (bắt buộc khi chuyển "Đang giao")</label><select class="form-select" name="shipper_id">
                <option value="">— Chọn —</option>
                <?php foreach ($shippers as $sh): ?><option value="<?= (int) $sh['ShipperID'] ?>" <?= (int) $order['ShipperID'] === (int) $sh['ShipperID'] ? 'selected' : '' ?>><?= e($sh['ShipperName']) ?></option><?php endforeach; ?>
            </select></div>
            <button class="btn btn-dark w-100">Lưu</button>
            <div class="form-text">Hủy đơn sẽ tự hoàn lại tồn kho theo từng size/màu.</div>
        </form>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm mt-3" href="<?= site_url('admin/orders') ?>">← Danh sách</a>
    </div>
</div>
<?= $this->endSection() ?>
