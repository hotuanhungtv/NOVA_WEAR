<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="mb-3"><a class="btn btn-dark" href="<?= site_url('admin/shippers/create') ?>"><i class="bi bi-plus-lg"></i> Thêm đơn vị vận chuyển</a></div>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>#</th><th>Tên</th><th>Điện thoại</th><th class="text-center">Số đơn</th><th></th></tr></thead><tbody>
    <?php foreach ($shippers as $s): ?>
        <tr><td><?= (int) $s['ShipperID'] ?></td><td class="fw-semibold"><?= e($s['ShipperName']) ?></td><td><?= e($s['Phone']) ?></td><td class="text-center"><?= (int) $s['total'] ?></td>
            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/shippers/' . (int) $s['ShipperID'] . '/edit') ?>">Sửa</a>
            <form class="d-inline" method="post" action="<?= site_url('admin/shippers/' . (int) $s['ShipperID'] . '/delete') ?>" onsubmit="return confirm('Xóa đơn vị này?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?></tbody></table></div>
<?= $this->endSection() ?>
