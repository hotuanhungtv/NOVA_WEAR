<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
    <form class="d-flex gap-2" method="get">
        <input class="form-control" name="keyword" placeholder="Tên / mã sản phẩm" value="<?= e($keyword) ?>">
        <select class="form-select" name="category"><option value="0">Mọi danh mục</option>
            <?php foreach ($categories as $c): ?><option value="<?= (int) $c['CategoryID'] ?>" <?= $categoryId === (int) $c['CategoryID'] ? 'selected' : '' ?>><?= e($c['CategoryName']) ?></option><?php endforeach; ?></select>
        <button class="btn btn-dark">Lọc</button>
    </form>
    <a class="btn btn-dark" href="<?= site_url('admin/products/create') ?>"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
</div>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th></th><th>Sản phẩm</th><th>Danh mục</th><th class="text-end">Giá</th><th class="text-center">Size/màu</th><th class="text-end">Tồn kho</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($products as $p): ?>
        <tr><td><?php if ($p['ImageFile']): ?><img class="thumb" src="<?= product_img($p['ImageFile']) ?>" alt=""><?php endif; ?></td>
            <td><div class="fw-semibold"><?= e($p['ProductName']) ?></div><small class="text-muted"><?= e($p['ProductCode']) ?></small></td>
            <td><?= e($p['CategoryName']) ?></td><td class="text-end"><?= money($p['Price']) ?></td>
            <td class="text-center"><?= (int) $p['VariantCount'] ?></td>
            <td class="text-end"><span class="badge <?= (int) $p['StockQuantity'] <= 0 ? 'bg-danger' : 'bg-light text-dark border' ?>"><?= (int) $p['StockQuantity'] ?></span></td>
            <td><span class="badge <?= (int) $p['IsActive'] ? 'bg-success' : 'bg-secondary' ?>"><?= (int) $p['IsActive'] ? 'Đang bán' : 'Ngừng bán' ?></span></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/products/' . (int) $p['ProductID'] . '/edit') ?>">Sửa</a>
                <form class="d-inline" method="post" action="<?= site_url('admin/products/' . (int) $p['ProductID'] . '/delete') ?>" onsubmit="return confirm('Xóa sản phẩm này?')"><?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?>
    <?php if (! $products): ?><tr><td colspan="8" class="text-center text-muted py-4">Chưa có sản phẩm.</td></tr><?php endif; ?>
    </tbody></table></div>
<?= $this->endSection() ?>
