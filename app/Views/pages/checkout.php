<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Đặt hàng</h1></div></div>
<div class="container section">
<div class="row g-4">
    <div class="col-lg-7">
        <form class="panel" action="<?= site_url('checkout') ?>" method="post">
            <?= csrf_field() ?>
            <h2 class="h5 mb-3">Thông tin giao hàng</h2>
            <div class="mb-3"><label class="form-label">Họ và tên</label><input class="form-control" value="<?= e($customer['CustomerName']) ?>" disabled></div>
            <div class="mb-3"><label class="form-label" for="phone">Số điện thoại</label>
                <input class="form-control" id="phone" name="phone" required value="<?= e(old('phone', $customer['Phone'] ?? '', false)) ?>" placeholder="09xx xxx xxx"></div>
            <div class="mb-3"><label class="form-label" for="address">Địa chỉ nhận hàng</label>
                <textarea class="form-control" id="address" name="address" rows="3" required maxlength="200" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= e(old('address', $customer['Address'] ?? '', false)) ?></textarea></div>
            <div class="mb-4"><label class="form-label">Phương thức thanh toán</label>
                <div class="form-check"><input class="form-check-input" type="radio" checked id="cod"><label class="form-check-label" for="cod">Thanh toán khi nhận hàng (COD)</label></div></div>
            <button class="btn btn-dark w-100">Xác nhận đặt hàng</button>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <h2 class="h5 mb-3">Đơn hàng của bạn</h2>
            <?php foreach ($lines as $l): ?>
                <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold"><?= e($l['ProductName']) ?></div>
                        <small class="text-muted"><?= e($l['Color']) ?> · Size <?= e($l['Size']) ?> · SL <?= (int) $l['Quantity'] ?></small>
                    </div>
                    <div class="text-nowrap"><?= money($l['Subtotal']) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="summary-row total"><span>Tổng cộng</span><span><?= money($total) ?></span></div>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
