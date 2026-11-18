<?php
$isAdmin = session()->get('staff_role') === 'admin';
$path    = trim(service('request')->getUri()->getPath(), '/');
$active  = static fn (string $p) => ($p === 'admin' ? $path === 'admin' : str_contains($path, $p)) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Quản trị') ?> | NOVA Admin</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>?v=3" rel="stylesheet">
</head>
<body>
<div class="adm">
    <aside class="adm-side">
        <a class="brand" href="<?= site_url('admin') ?>"><img src="<?= base_url('assets/img/logo-white.png') ?>" alt="NOVA"></a>
        <a class="nav-l <?= $active('admin') ?>" href="<?= site_url('admin') ?>"><i class="bi bi-speedometer2"></i> Tổng quan</a>
        <a class="nav-l <?= $active('admin/orders') ?>" href="<?= site_url('admin/orders') ?>"><i class="bi bi-receipt"></i> Đơn hàng</a>
        <a class="nav-l <?= $active('admin/reviews') ?>" href="<?= site_url('admin/reviews') ?>"><i class="bi bi-star"></i> Đánh giá</a>
        <?php if ($isAdmin): ?>
            <div class="sec">Quản lý</div>
            <a class="nav-l <?= $active('admin/products') ?>" href="<?= site_url('admin/products') ?>"><i class="bi bi-box-seam"></i> Sản phẩm</a>
            <a class="nav-l <?= $active('admin/categories') ?>" href="<?= site_url('admin/categories') ?>"><i class="bi bi-tags"></i> Danh mục</a>
            <a class="nav-l <?= $active('admin/shippers') ?>" href="<?= site_url('admin/shippers') ?>"><i class="bi bi-truck"></i> Vận chuyển</a>
            <a class="nav-l <?= $active('admin/customers') ?>" href="<?= site_url('admin/customers') ?>"><i class="bi bi-people"></i> Khách hàng</a>
            <a class="nav-l <?= $active('admin/employees') ?>" href="<?= site_url('admin/employees') ?>"><i class="bi bi-person-badge"></i> Nhân viên</a>
        <?php endif; ?>
        <div class="sec">Khác</div>
        <a class="nav-l" href="<?= site_url('/') ?>" target="_blank"><i class="bi bi-shop"></i> Xem cửa hàng</a>
    </aside>
    <div class="adm-main">
        <div class="adm-top">
            <h1><?= e($pageTitle ?? '') ?></h1>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><i class="bi bi-person-circle"></i> <?= e(session()->get('staff_name')) ?> <span class="badge bg-dark"><?= $isAdmin ? 'Admin' : 'Nhân viên' ?></span></span>
                <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/logout') ?>">Đăng xuất</a>
            </div>
        </div>
        <div class="adm-body">
            <?= view('partials/flash_admin') ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
