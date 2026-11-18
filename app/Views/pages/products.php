<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$qs = static function (array $over = []) use ($categoryId, $keyword, $sort): string {
    $q = array_filter(['category' => $categoryId ?: null, 'keyword' => $keyword ?: null, 'sort' => $sort !== 'new' ? $sort : null], static fn ($v) => $v !== null) ;
    $q = array_merge($q, $over);
    $q = array_filter($q, static fn ($v) => $v !== null && $v !== '');
    return $q ? '?' . http_build_query($q) : '';
};
?>
<div class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Trang chủ</a></li><li class="breadcrumb-item active">Sản phẩm</li></ol></nav>
        <h1><?= $keyword !== '' ? 'Kết quả cho "' . e($keyword) . '"' : 'Tất cả sản phẩm' ?></h1>
    </div>
</div>

<div class="container">
    <div class="filter-bar">
        <div class="chip-row">
            <a class="chip <?= $categoryId === 0 ? 'active' : '' ?>" href="<?= site_url('products') . $qs(['category' => null, 'page' => null]) ?>">Tất cả</a>
            <?php foreach ($categories as $c): ?>
                <a class="chip <?= $categoryId === (int) $c['CategoryID'] ? 'active' : '' ?>" href="<?= site_url('products') . $qs(['category' => (int) $c['CategoryID'], 'page' => null]) ?>"><?= e($c['CategoryName']) ?></a>
            <?php endforeach; ?>
        </div>
        <form method="get" class="d-flex align-items-center gap-2">
            <?php if ($categoryId): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
            <?php if ($keyword !== ''): ?><input type="hidden" name="keyword" value="<?= e($keyword) ?>"><?php endif; ?>
            <span class="text-muted small text-nowrap"><?= (int) $total ?> sản phẩm</span>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sắp xếp">
                <?php foreach ($sorts as $k => $label): ?><option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (! $products): ?>
        <div class="empty"><i class="bi bi-search"></i>Không tìm thấy sản phẩm phù hợp.<div class="mt-3"><a class="btn btn-dark" href="<?= site_url('products') ?>">Xem tất cả</a></div></div>
    <?php else: ?>
        <div class="row g-3 g-lg-4">
            <?php foreach ($products as $p): ?>
                <div class="col-6 col-md-4 col-lg-3"><?= view('partials/product_card', ['p' => $p]) ?></div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Phân trang"><ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= site_url('products') . $qs(['page' => $page - 1]) ?>">&laquo;</a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= site_url('products') . $qs(['page' => $i]) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= site_url('products') . $qs(['page' => $page + 1]) ?>">&raquo;</a></li>
            </ul></nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
