<!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập quản trị | NOVA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/admin.css') ?>?v=3" rel="stylesheet"></head>
<body><div class="login-box card-box p-4">
    <div class="text-center mb-3"><img src="<?= base_url('assets/img/logo-black.png') ?>" alt="NOVA" height="46"></div>
    <h1 class="h5 text-center mb-3">Đăng nhập quản trị</h1>
    <?= view('partials/flash_admin') ?>
    <form method="post" action="<?= site_url('admin/login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required autofocus value="<?= e(old('email', '', false)) ?>"></div>
        <div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required></div>
        <button class="btn btn-dark w-100">Đăng nhập</button>
    </form>
    <p class="text-center small mt-3 mb-0"><a href="<?= site_url('/') ?>">← Về cửa hàng</a></p>
</div></body></html>
