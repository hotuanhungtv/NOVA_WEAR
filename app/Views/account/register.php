<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container"><div class="auth-wrap panel">
    <h1>Đăng ký</h1>
    <form action="<?= site_url('register') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Họ và tên</label><input class="form-control" name="customer_name" required maxlength="100" value="<?= e(old('customer_name', '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="<?= e(old('phone', '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required value="<?= e(old('email', '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Mật khẩu (tối thiểu 8 ký tự)</label><input class="form-control" type="password" name="password" required minlength="8"></div>
        <div class="mb-3"><label class="form-label">Nhập lại mật khẩu</label><input class="form-control" type="password" name="confirm_password" required></div>
        <button class="btn btn-dark w-100">Tạo tài khoản</button>
    </form>
    <p class="text-center small mt-3 mb-0">Đã có tài khoản? <a class="fw-bold" href="<?= site_url('login') ?>">Đăng nhập</a></p>
</div></div>
<?= $this->endSection() ?>
