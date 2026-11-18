<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container"><div class="auth-wrap panel">
    <h1>Đăng nhập</h1>
    <form action="<?= site_url('login') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autofocus value="<?= e(old('email', '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label" for="password">Mật khẩu</label><input class="form-control" type="password" id="password" name="password" required></div>
        <button class="btn btn-dark w-100">Đăng nhập</button>
    </form>
    <p class="text-center small mt-3 mb-0">Chưa có tài khoản? <a class="fw-bold" href="<?= site_url('register') ?>">Đăng ký</a></p>
</div></div>
<?= $this->endSection() ?>
