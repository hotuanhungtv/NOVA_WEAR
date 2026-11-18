<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Thông tin cá nhân</h1></div></div>
<div class="container section"><div class="row g-4">
    <div class="col-lg-6"><form class="panel" action="<?= site_url('profile') ?>" method="post">
        <?= csrf_field() ?>
        <h2 class="h5 mb-3">Hồ sơ</h2>
        <div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="<?= e($customer['Email']) ?>" disabled></div>
        <div class="mb-3"><label class="form-label">Họ và tên</label><input class="form-control" name="customer_name" required value="<?= e(old('customer_name', $customer['CustomerName'], false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="<?= e(old('phone', $customer['Phone'] ?? '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Địa chỉ</label><textarea class="form-control" name="address" rows="2"><?= e(old('address', $customer['Address'] ?? '', false)) ?></textarea></div>
        <button class="btn btn-dark">Lưu thay đổi</button>
    </form></div>
    <div class="col-lg-6"><form class="panel" action="<?= site_url('profile/password') ?>" method="post">
        <?= csrf_field() ?>
        <h2 class="h5 mb-3">Đổi mật khẩu</h2>
        <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label><input class="form-control" type="password" name="current_password" required></div>
        <div class="mb-3"><label class="form-label">Mật khẩu mới</label><input class="form-control" type="password" name="new_password" required minlength="8"></div>
        <div class="mb-3"><label class="form-label">Nhập lại mật khẩu mới</label><input class="form-control" type="password" name="confirm_password" required></div>
        <button class="btn btn-dark">Đổi mật khẩu</button>
    </form></div>
</div></div>
<?= $this->endSection() ?>
