<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<form class="d-flex gap-2 mb-3" method="get"><input class="form-control" style="max-width:340px" name="keyword" placeholder="Tên / email / SĐT" value="<?= e($keyword) ?>"><button class="btn btn-dark">Tìm</button></form>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>#</th><th>Khách hàng</th><th>Email</th><th>SĐT</th><th class="text-center">Đơn</th><th class="text-center">Đánh giá</th><th>Tài khoản</th><th></th></tr></thead><tbody>
    <?php foreach ($customers as $c): ?>
        <tr><td><?= (int) $c['CustomerID'] ?></td><td class="fw-semibold"><?= e($c['CustomerName']) ?></td><td><?= e($c['Email'] ?: '—') ?></td><td><?= e($c['Phone']) ?></td>
            <td class="text-center"><?= (int) $c['OrderCount'] ?></td><td class="text-center"><?= (int) $c['ReviewCount'] ?></td>
            <td><?php if ($c['Email']): ?><span class="badge <?= (int) $c['IsActive'] ? 'bg-success' : 'bg-secondary' ?>"><?= (int) $c['IsActive'] ? 'Hoạt động' : 'Đã khóa' ?></span><?php else: ?><span class="text-muted small">Không có TK</span><?php endif; ?></td>
            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/customers/' . (int) $c['CustomerID']) ?>">Xem</a>
            <?php if ($c['Email']): ?><form class="d-inline" method="post" action="<?= site_url('admin/customers/' . (int) $c['CustomerID'] . '/toggle') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary"><?= (int) $c['IsActive'] ? 'Khóa' : 'Mở khóa' ?></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
<?= $this->endSection() ?>
