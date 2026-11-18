<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="mb-3"><a class="btn btn-dark" href="<?= site_url('admin/categories/create') ?>"><i class="bi bi-plus-lg"></i> Thêm danh mục</a></div>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>#</th><th>Tên danh mục</th><th>Mô tả</th><th class="text-center">Sản phẩm</th><th></th></tr></thead><tbody>
    <?php foreach ($categories as $c): ?>
        <tr><td><?= (int) $c['CategoryID'] ?></td><td class="fw-semibold"><?= e($c['CategoryName']) ?></td><td class="text-muted"><?= e($c['Description']) ?></td><td class="text-center"><?= (int) $c['total'] ?></td>
            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/categories/' . (int) $c['CategoryID'] . '/edit') ?>">Sửa</a>
            <form class="d-inline" method="post" action="<?= site_url('admin/categories/' . (int) $c['CategoryID'] . '/delete') ?>" onsubmit="return confirm('Xóa danh mục này?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?></tbody></table></div>
<?= $this->endSection() ?>
