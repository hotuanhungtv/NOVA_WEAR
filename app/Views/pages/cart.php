<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Giỏ hàng</h1></div></div>
<div class="container section">
<?php if (! $lines): ?>
    <div class="empty"><i class="bi bi-bag"></i>Giỏ hàng của bạn đang trống.<div class="mt-3"><a class="btn btn-dark" href="<?= site_url('products') ?>">Tiếp tục mua sắm</a></div></div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-8">
        <form action="<?= site_url('cart/update') ?>" method="post" id="cartForm">
            <?= csrf_field() ?>
            <div class="panel py-1">
            <?php foreach ($lines as $l): ?>
                <div class="cart-line <?= $l['OutOfStock'] ? 'out' : '' ?>">
                    <a href="<?= site_url('products/' . $l['ProductID']) ?>"><?php if ($l['Image']): ?><img src="<?= product_img($l['Image']) ?>" alt=""><?php endif; ?></a>
                    <div>
                        <a class="fw-semibold" href="<?= site_url('products/' . $l['ProductID']) ?>"><?= e($l['ProductName']) ?></a>
                        <div class="opt mt-1">
                            <span class="dot" style="background:<?= e($l['ColorHex']) ?>"></span><?= e($l['Color']) ?>
                            <span>·</span> Size <strong class="text-dark"><?= e($l['Size']) ?></strong>
                        </div>
                        <div class="small mt-1"><?= money($l['Price']) ?></div>
                        <?php if ($l['OutOfStock']): ?>
                            <div class="small text-danger mt-1">Phiên bản này đã hết hàng – vui lòng xóa khỏi giỏ.</div>
                        <?php else: ?>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <input type="number" class="form-control form-control-sm" style="width:80px" min="0" max="<?= (int) $l['Stock'] ?>" name="quantities[<?= (int) $l['VariantID'] ?>]" value="<?= (int) $l['Quantity'] ?>" aria-label="Số lượng">
                                <small class="text-muted">Còn <?= (int) $l['Stock'] ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold mb-2"><?= money($l['Subtotal']) ?></div>
                        <button class="btn btn-sm btn-link text-danger p-0" type="submit" formaction="<?= site_url('cart/remove') ?>" name="variant_id" value="<?= (int) $l['VariantID'] ?>"><i class="bi bi-trash"></i> Xóa</button>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="<?= site_url('products') ?>" class="btn btn-outline-dark btn-sm">← Tiếp tục mua sắm</a>
                <button class="btn btn-dark btn-sm">Cập nhật giỏ hàng</button>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="panel">
            <h2 class="h5 mb-3">Tóm tắt đơn hàng</h2>
            <div class="summary-row"><span>Tạm tính</span><span><?= money($total) ?></span></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span><?= $total >= 599000 ? 'Miễn phí' : 'Tính khi giao' ?></span></div>
            <div class="summary-row total"><span>Tổng cộng</span><span><?= money($total) ?></span></div>
            <?php if ($problem): ?>
                <div class="alert alert-warning mt-3 mb-0 small">Hãy xóa các sản phẩm hết hàng để tiếp tục thanh toán.</div>
            <?php else: ?>
                <a href="<?= site_url('checkout') ?>" class="btn btn-dark w-100 mt-3">Tiến hành đặt hàng</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
