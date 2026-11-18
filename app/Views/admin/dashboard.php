<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="row g-3 mb-4">
    <?php foreach (['Pending' => 'bi-hourglass-split', 'Confirmed' => 'bi-check2-circle', 'Shipping' => 'bi-truck', 'Completed' => 'bi-bag-check'] as $st => $ic): ?>
        <div class="col-6 col-lg-3"><a href="<?= site_url('admin/orders') ?>?status=<?= $st ?>" class="card-box stat d-block">
            <span class="l"><i class="bi <?= $ic ?>"></i> <?= e(order_status_label($st)) ?></span><span class="n"><?= (int) $statusCounts[$st] ?></span></a></div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><a href="<?= site_url('admin/reviews') ?>" class="card-box stat d-block"><span class="l"><i class="bi bi-star"></i> Đánh giá mới (7 ngày)</span><span class="n"><?= (int) $newReviews ?></span></a></div>
    <?php if ($isAdmin): ?>
        <div class="col-6 col-lg-3"><div class="card-box stat"><span class="l">Doanh thu hôm nay</span><span class="n"><?= money($revenue['today']) ?></span></div></div>
        <div class="col-6 col-lg-3"><div class="card-box stat"><span class="l">Doanh thu tháng này</span><span class="n"><?= money($revenue['month']) ?></span></div></div>
        <div class="col-6 col-lg-3"><div class="card-box stat"><span class="l">Doanh thu năm nay</span><span class="n"><?= money($revenue['year']) ?></span></div></div>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
<div class="row g-3">
    <div class="col-lg-6"><div class="card-box">
        <h2 class="h6 mb-3">Doanh thu theo tháng (đơn hoàn thành)</h2>
        <table class="table table-sm mb-0"><thead><tr><th>Tháng</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr></thead><tbody>
        <?php if (! $monthly): ?><tr><td colspan="3" class="text-muted">Chưa có dữ liệu.</td></tr><?php endif; ?>
        <?php foreach ($monthly as $m => $r): ?><tr><td>Tháng <?= $m ?></td><td class="text-end"><?= (int) $r['orders_count'] ?></td><td class="text-end"><?= money($r['revenue']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div></div>
    <div class="col-lg-6"><div class="card-box">
        <h2 class="h6 mb-3">Bán chạy nhất</h2>
        <table class="table table-sm mb-0"><tbody>
        <?php foreach ($bestSellers as $b): ?><tr><td><?= e($b['ProductName']) ?></td><td class="text-end"><?= (int) $b['sold'] ?> đã bán</td></tr><?php endforeach; ?>
        <?php if (! $bestSellers): ?><tr><td class="text-muted">Chưa có dữ liệu.</td></tr><?php endif; ?>
        </tbody></table>
    </div></div>
    <div class="col-12"><div class="card-box">
        <h2 class="h6 mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> Sắp hết hàng (theo từng size / màu)</h2>
        <?php if (! $lowStock): ?><p class="text-muted mb-0">Không có phiên bản nào sắp hết hàng.</p><?php else: ?>
        <table class="table table-sm mb-0"><thead><tr><th>Sản phẩm</th><th>Màu</th><th>Size</th><th class="text-end">Tồn</th></tr></thead><tbody>
        <?php foreach ($lowStock as $s): ?><tr><td><?= e($s['ProductName']) ?></td><td><?= e($s['Color']) ?></td><td><?= e($s['Size']) ?></td>
            <td class="text-end"><span class="badge <?= (int) $s['StockQuantity'] === 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= (int) $s['StockQuantity'] ?></span></td></tr><?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
