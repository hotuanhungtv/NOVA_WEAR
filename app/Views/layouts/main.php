<?php
$nav     = nova_nav();
$isLogin = (bool) session()->get('customer_id');
$cName   = (string) session()->get('customer_name');
$isStaff = (bool) session()->get('staff_id');
$kw      = (string) service('request')->getGet('keyword');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'NOVA Wear') ?> | NOVA Wear</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/nova.css') ?>?v=3" rel="stylesheet">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span><i class="bi bi-truck"></i> Giao hàng toàn quốc · Freeship đơn từ 599.000đ</span>
        <span class="topbar-right"><i class="bi bi-arrow-repeat"></i> Đổi hàng trong 7 ngày</span>
    </div>
</div>

<div class="site-sticky">
    <header class="container">
        <div class="header-main">

            <!-- Trái: nút menu (mobile) + ô tìm kiếm -->
            <div class="header-left">
                <button class="menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Mở menu">
                    <i class="bi bi-list"></i>
                </button>
                <form class="search-box" action="<?= site_url('products') ?>" method="get" role="search">
                    <input type="search" name="keyword" value="<?= e($kw) ?>" placeholder="Nhập từ khóa để tìm sản phẩm" aria-label="Tìm sản phẩm">
                    <button type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <!-- Giữa: logo -->
            <a class="logo" href="<?= site_url('/') ?>" aria-label="NOVA Wear - Trang chủ">
                <img src="<?= base_url('assets/img/logo-black.png') ?>" alt="NOVA Wear">
            </a>

            <!-- Phải: hotline · tài khoản · giỏ hàng (không bao giờ xuống dòng) -->
            <div class="header-right">
                <a class="hd-item hd-hotline" href="tel:0900000000">
                    <i class="bi bi-telephone-fill"></i>
                    <span class="hd-text"><small>Đặt hàng nhanh</small><strong>0900 000 000</strong></span>
                </a>

                <?php if ($isLogin): ?>
                    <div class="dropdown">
                        <a class="hd-item hd-account" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person"></i>
                            <span class="hd-text"><?= e(mb_strimwidth($cName, 0, 16, '…')) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= site_url('profile') ?>">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="<?= site_url('orders') ?>">Đơn hàng của tôi</a></li>
                            <?php if ($isStaff): ?><li><a class="dropdown-item" href="<?= site_url('admin') ?>">Trang quản trị</a></li><?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= site_url('logout') ?>">Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="hd-item hd-account" href="<?= site_url('login') ?>">
                        <i class="bi bi-person"></i><span class="hd-text">Tài khoản</span>
                    </a>
                <?php endif; ?>

                <a class="hd-item hd-cart" href="<?= site_url('cart') ?>" aria-label="Giỏ hàng">
                    <span class="hd-icon">
                        <i class="bi bi-bag"></i>
                        <?php if ($nav['cartCount'] > 0): ?><span class="cart-badge"><?= (int) $nav['cartCount'] ?></span><?php endif; ?>
                    </span>
                    <?php if ($nav['cartCount'] > 0): ?><span class="cart-total"><?= money($nav['cartTotal']) ?></span><?php endif; ?>
                </a>
            </div>
        </div>

        <form class="header-mobile-search search-box" action="<?= site_url('products') ?>" method="get">
            <input type="search" name="keyword" value="<?= e($kw) ?>" placeholder="Tìm sản phẩm..." aria-label="Tìm sản phẩm">
            <button type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
        </form>
    </header>

    <nav class="main-menu" aria-label="Danh mục">
        <div class="container">
            <ul class="menu">
                <li class="has-mega">
                    <a href="<?= site_url('products') ?>">All <i class="bi bi-chevron-down" style="font-size:.7rem"></i></a>
                    <div class="mega">
                        <a href="<?= site_url('products') ?>">Tất cả sản phẩm</a>
                        <?php foreach ($nav['categories'] as $c): ?>
                            <a href="<?= site_url('products') ?>?category=<?= (int) $c['CategoryID'] ?>"><?= e($c['CategoryName']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <?php foreach ($nav['categories'] as $c): ?>
                    <li><a href="<?= site_url('products') ?>?category=<?= (int) $c['CategoryID'] ?>"><?= e($c['CategoryName']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</div>

<!-- Menu trên điện thoại -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <img src="<?= base_url('assets/img/logo-black.png') ?>" alt="NOVA Wear" height="36">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body offcanvas-menu">
        <a href="<?= site_url('products') ?>">Tất cả sản phẩm</a>
        <?php foreach ($nav['categories'] as $c): ?>
            <a href="<?= site_url('products') ?>?category=<?= (int) $c['CategoryID'] ?>"><?= e($c['CategoryName']) ?></a>
        <?php endforeach; ?>
        <a href="<?= site_url('orders') ?>">Đơn hàng của tôi</a>
        <a href="tel:0900000000"><i class="bi bi-telephone"></i> 0900 000 000</a>
    </div>
</div>

<main class="<?= e($mainClass ?? '') ?>">
    <?= view('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <img src="<?= base_url('assets/img/logo-white.png') ?>" alt="NOVA Wear" height="48" class="mb-3">
                <p>NOVA Wear - thương hiệu thời trang streetwear dành cho người trẻ: áo thun, sơ mi, quần, outerwear và phụ kiện.</p>
                <div class="d-flex gap-3 fs-5">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Chính sách</h5>
                <ul>
                    <li><a href="#">Chính sách thành viên</a></li>
                    <li><a href="#">Hướng dẫn bảo quản</a></li>
                    <li><a href="#">Chính sách đổi hàng</a></li>
                    <li><a href="#">Chính sách vận chuyển</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Liên kết</h5>
                <ul>
                    <li><a href="<?= site_url('/') ?>">Trang chủ</a></li>
                    <li><a href="<?= site_url('products') ?>">Sản phẩm</a></li>
                    <li><a href="<?= site_url('cart') ?>">Giỏ hàng</a></li>
                    <li><a href="<?= site_url('admin/login') ?>">Dành cho nhân viên</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Liên hệ</h5>
                <ul>
                    <li><i class="bi bi-geo-alt"></i> 01 Nguyễn Huệ, Q.1, TP. Hồ Chí Minh</li>
                    <li><i class="bi bi-envelope"></i> hotro@novawear.vn</li>
                    <li><i class="bi bi-telephone"></i> 0900 000 000</li>
                    <li><i class="bi bi-clock"></i> 8:30 - 22:00 mỗi ngày</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; <?= date('Y') ?> NOVA Wear. All rights reserved.</span>
            <span><i class="bi bi-cash-coin"></i> COD &nbsp; <i class="bi bi-credit-card"></i> Thẻ &nbsp; <i class="bi bi-bank"></i> Chuyển khoản</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
