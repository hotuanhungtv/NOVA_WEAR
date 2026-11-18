<?php
$types = ['success' => 'success', 'danger' => 'danger', 'warning' => 'warning', 'info' => 'info'];
$errors = (array) (session()->getFlashdata('form_errors') ?? []);
$showCartLink = (bool) session()->getFlashdata('cart_link');
$has = $errors !== [];
foreach ($types as $k => $_) { if (session()->has($k)) { $has = true; } }
if (! $has) { return; }
?>
<div class="container flash-wrap">
    <?php foreach ($types as $key => $cls): ?>
        <?php if ($msg = session()->getFlashdata($key)): ?>
            <div class="alert alert-<?= $cls ?> alert-dismissible fade show" role="alert">
                <?= e($msg) ?>
                <?php if ($key === 'success' && $showCartLink): ?>
                    <a class="fw-bold ms-2 text-decoration-underline" href="<?= site_url('cart') ?>">Xem giỏ hàng</a>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
</div>
