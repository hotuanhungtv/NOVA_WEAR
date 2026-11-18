<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="row g-3">
<div class="col-lg-8"><div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>Nhân viên</th><th>Email</th><th>Vai trò</th><th></th></tr></thead><tbody>
    <?php foreach ($employees as $m): $me = (int) $m['EmployeeID'] === $meId; ?>
        <tr><td class="fw-semibold"><?= e($m['LastName'] . ' ' . $m['FirstName']) ?> <?= $me ? '<span class="badge bg-dark">Bạn</span>' : '' ?></td><td><?= e($m['Email']) ?></td>
            <td><?php if ($me): ?><span class="badge bg-dark"><?= e($m['Role']) ?></span><?php else: ?>
                <form class="d-flex gap-1" method="post" action="<?= site_url('admin/employees/' . (int) $m['EmployeeID'] . '/role') ?>"><?= csrf_field() ?>
                    <select class="form-select form-select-sm" name="role"><option value="staff" <?= $m['Role'] === 'staff' ? 'selected' : '' ?>>Nhân viên</option><option value="admin" <?= $m['Role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select>
                    <button class="btn btn-sm btn-outline-dark">Lưu</button></form><?php endif; ?></td>
            <td class="text-end"><?php if (! $me): ?><form method="post" action="<?= site_url('admin/employees/' . (int) $m['EmployeeID'] . '/delete') ?>" onsubmit="return confirm('Xóa nhân viên này?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody></table></div></div>
<div class="col-lg-4"><form class="card-box" method="post" action="<?= site_url('admin/employees') ?>">
    <?= csrf_field() ?><h2 class="h6 mb-3">Thêm nhân viên</h2>
    <div class="row g-2 mb-2"><div class="col"><input class="form-control" name="last_name" placeholder="Họ" required value="<?= e(old('last_name', '', false)) ?>"></div><div class="col"><input class="form-control" name="first_name" placeholder="Tên" required value="<?= e(old('first_name', '', false)) ?>"></div></div>
    <input class="form-control mb-2" type="email" name="email" placeholder="Email" required value="<?= e(old('email', '', false)) ?>">
    <input class="form-control mb-2" type="password" name="password" placeholder="Mật khẩu (≥ 8 ký tự)" required minlength="8">
    <select class="form-select mb-3" name="role"><option value="staff">Nhân viên</option><option value="admin">Admin</option></select>
    <button class="btn btn-dark w-100">Thêm</button>
</form></div>
</div>
<?= $this->endSection() ?>
