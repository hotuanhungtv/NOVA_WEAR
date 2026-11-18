<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="hero">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5500">
        <?php
        $slides = [
            ['eyebrow' => 'Bộ sưu tập mới', 'title' => 'Streetwear<br>mỗi ngày', 'text' => 'Áo thun, sơ mi, quần và outerwear phom rộng – chọn size & màu bạn thích.', 'img' => 'jacket-black.png', 'cta' => ['Mua ngay', '']],
            ['eyebrow' => 'Basic tee', 'title' => 'Áo thun<br>NOVA Basic', 'text' => 'Cotton 100% dày dặn, nhiều màu, đủ size S – XL.', 'img' => 'tee-white.png', 'cta' => ['Xem áo thun', '?category=1']],
            ['eyebrow' => 'Denim & Workwear', 'title' => 'Quần &<br>áo khoác', 'text' => 'Denim 12oz, canvas workwear – bền, đẹp, lên form chuẩn.', 'img' => 'pants-denim.png', 'cta' => ['Khám phá', '?category=4']],
        ];
        ?>
        <div class="carousel-indicators">
            <?php foreach ($slides as $i => $_): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($slides as $i => $s): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="container">
                        <div class="hero-slide">
                            <div class="hero-copy">
                                <span class="eyebrow"><?= e($s['eyebrow']) ?></span>
                                <h1><?= $s['title'] ?></h1>
                                <p><?= e($s['text']) ?></p>
                                <a class="btn btn-light mt-2" href="<?= site_url('products') . $s['cta'][1] ?>"><?= e($s['cta'][0]) ?></a>
                            </div>
                            <div class="hero-img" style="background-image:url('<?= product_img($s['img']) ?>')"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="perks">
    <div class="container"><div class="row">
        <div class="col-6 col-lg-3"><div class="perk"><i class="bi bi-truck"></i><div><strong>Giao hàng toàn quốc</strong><span>Freeship từ 599.000đ</span></div></div></div>
        <div class="col-6 col-lg-3"><div class="perk"><i class="bi bi-arrow-repeat"></i><div><strong>Đổi hàng 7 ngày</strong><span>Đổi size, đổi màu dễ dàng</span></div></div></div>
        <div class="col-6 col-lg-3"><div class="perk"><i class="bi bi-shield-check"></i><div><strong>Chất lượng đảm bảo</strong><span>Vải dày, may kỹ</span></div></div></div>
        <div class="col-6 col-lg-3"><div class="perk"><i class="bi bi-headset"></i><div><strong>Hỗ trợ 8:30 – 22:00</strong><span>Hotline 0900 000 000</span></div></div></div>
    </div></div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title"><h2>Danh mục</h2><a href="<?= site_url('products') ?>">Xem tất cả</a></div>
        <div class="row g-3">
            <?php foreach ($categories as $c): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a class="cat-tile" href="<?= site_url('products') ?>?category=<?= (int) $c['CategoryID'] ?>">
                        <?php if ($c['CoverImage']): ?><img src="<?= product_img($c['CoverImage']) ?>" alt="<?= e($c['CategoryName']) ?>" loading="lazy"><?php endif; ?>
                        <div class="cap"><h3><?= e($c['CategoryName']) ?></h3><small><?= (int) $c['total'] ?> sản phẩm</small></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="section-title"><h2>Sản phẩm mới</h2><a href="<?= site_url('products') ?>">Xem thêm</a></div>
        <div class="row g-3 g-lg-4">
            <?php foreach ($latest as $p): ?>
                <div class="col-6 col-md-4 col-lg-3"><?= view('partials/product_card', ['p' => $p]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
