<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$pid      = (int) $product['ProductID'];
$soldOut  = (int) $product['StockQuantity'] <= 0;
$mainImg  = $images[0]['ImageFile'] ?? null;
$loggedIn = (bool) session()->get('customer_id');
$old      = static fn (string $k, $d = '') => old($k, $d, false) ?? $d;
$rErrors  = (array) (session()->getFlashdata('review_errors') ?? []);
$myRating = (int) $old('rating', $mine['Rating'] ?? 0);
$starUrl  = static fn (int $s) => site_url('products/' . $pid) . ($s ? '?star=' . $s : '') . '#reviews';
?>
<div class="page-head py-3">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url('products') ?>?category=<?= (int) $product['CategoryID'] ?>"><?= e($product['CategoryName']) ?></a></li>
            <li class="breadcrumb-item active"><?= e($product['ProductName']) ?></li>
        </ol></nav>
    </div>
</div>

<div class="container section pb-0 pt-4">
    <div class="row g-4 g-lg-5">

        <!-- ===== Thư viện ảnh ===== -->
        <div class="col-lg-7">
            <div class="gallery">
                <div class="gallery-thumbs" id="thumbs">
                    <?php foreach ($images as $i => $img): ?>
                        <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-src="<?= product_img($img['ImageFile']) ?>" aria-label="Ảnh <?= $i + 1 ?>">
                            <img src="<?= product_img($img['ImageFile']) ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="gallery-main">
                    <?php if ($mainImg): ?><img id="mainImage" src="<?= product_img($mainImg) ?>" alt="<?= e($product['ProductName']) ?>"><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ===== Thông tin + chọn size/màu ===== -->
        <div class="col-lg-5 pd-info">
            <span class="p-cat"><?= e($product['CategoryName']) ?> · <?= e($product['ProductCode']) ?></span>
            <h1><?= e($product['ProductName']) ?></h1>

            <div class="pd-rate">
                <?php if ($stats['count'] > 0): ?>
                    <?= stars($stats['avg']) ?> <strong class="text-dark"><?= number_format($stats['avg'], 1, ',', '') ?></strong>
                    <a href="#reviews"><?= (int) $stats['count'] ?> đánh giá</a>
                <?php else: ?>
                    <a href="#reviews">Chưa có đánh giá – hãy là người đầu tiên</a>
                <?php endif; ?>
            </div>

            <div class="pd-price"><?= money($product['Price']) ?></div>

            <?php if ($product['Description']): ?><p class="text-muted"><?= nl2br(e($product['Description'])) ?></p><?php endif; ?>

            <form action="<?= site_url('cart/add') ?>" method="post" id="addForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="variant_id" id="variantId" value="">

                <div class="opt-label"><span>Màu sắc</span><span class="chosen" id="colorName">Chọn màu</span></div>
                <div class="swatches" id="swatches" role="radiogroup" aria-label="Màu sắc"></div>

                <div class="opt-label"><span>Kích thước</span><span><button type="button" data-bs-toggle="modal" data-bs-target="#sizeGuide"><i class="bi bi-rulers"></i> Bảng size</button></span></div>
                <div class="sizes" id="sizes" role="radiogroup" aria-label="Kích thước"></div>

                <div class="stock-note" id="stockNote" aria-live="polite"></div>
                <div class="pick-error" id="pickError">Vui lòng chọn đầy đủ màu và size.</div>

                <div class="pd-actions">
                    <div class="qty">
                        <button type="button" id="qMinus" aria-label="Giảm">−</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="99" aria-label="Số lượng">
                        <button type="button" id="qPlus" aria-label="Tăng">+</button>
                    </div>
                    <button type="submit" class="btn btn-outline-dark" id="btnAdd" <?= $soldOut ? 'disabled' : '' ?>><i class="bi bi-bag-plus"></i> Thêm vào giỏ</button>
                    <button type="submit" class="btn btn-dark" name="buy_now" value="1" id="btnBuy" <?= $soldOut ? 'disabled' : '' ?>>Mua ngay</button>
                </div>
            </form>

            <ul class="list-unstyled small text-muted mt-4 mb-0 d-grid gap-1">
                <li><i class="bi bi-truck"></i> Freeship đơn từ 599.000đ</li>
                <li><i class="bi bi-arrow-repeat"></i> Đổi hàng trong 7 ngày (đổi size / đổi màu)</li>
            </ul>
        </div>
    </div>
</div>

<!-- ===================== ĐÁNH GIÁ ===================== -->
<section class="container section" id="reviews">
    <div class="section-title"><h2>Đánh giá &amp; nhận xét</h2></div>

    <?php if ($stats['count'] > 0): ?>
    <div class="rv-summary mb-4">
        <div class="rv-score">
            <div class="num"><?= number_format($stats['avg'], 1, ',', '') ?></div>
            <?= stars($stats['avg'], 'fs-5') ?>
            <div class="small text-muted mt-1"><?= (int) $stats['count'] ?> đánh giá</div>
        </div>
        <div class="rv-bars">
            <?php foreach ([5, 4, 3, 2, 1] as $s):
                $n = $stats['dist'][$s]; $pct = $stats['count'] ? round($n * 100 / $stats['count']) : 0; ?>
                <div class="rv-bar">
                    <a href="<?= $starUrl($star === $s ? 0 : $s) ?>" class="<?= $star === $s ? 'active' : '' ?>"><?= $s ?> <i class="bi bi-star-fill" style="color:var(--star)"></i></a>
                    <div class="track"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                    <span><?= $n ?></span>
                </div>
            <?php endforeach; ?>
            <?php if ($star): ?><a class="small" href="<?= $starUrl(0) ?>">Bỏ lọc (đang xem <?= $star ?> sao)</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <?php if (! $reviewList): ?>
                <div class="empty"><i class="bi bi-chat-square-text"></i><?= $star ? 'Chưa có đánh giá ' . $star . ' sao.' : 'Chưa có đánh giá nào cho sản phẩm này.' ?></div>
            <?php endif; ?>

            <?php foreach ($reviewList as $r): ?>
                <article class="rv-item">
                    <div class="rv-head">
                        <span class="rv-avatar"><?= e(mb_strtoupper(mb_substr(trim(preg_replace('/^.*\s/u', '', $r['CustomerName'])), 0, 1))) ?></span>
                        <span class="rv-name"><?= e(mask_name($r['CustomerName'])) ?></span>
                        <?php if ((int) $r['Verified']): ?><span class="rv-verified"><i class="bi bi-patch-check-fill"></i> Đã mua hàng</span><?php endif; ?>
                        <span class="rv-time"><?= e(time_ago($r['CreatedAt'])) ?><?= $r['UpdatedAt'] ? ' · đã chỉnh sửa' : '' ?></span>
                    </div>
                    <?= stars($r['Rating']) ?>
                    <?php if ($r['Title']): ?><div class="rv-title"><?= e($r['Title']) ?></div><?php endif; ?>
                    <p class="rv-text"><?= e($r['Comment']) ?></p>
                    <?php if ($loggedIn && (int) $r['CustomerID'] === (int) session()->get('customer_id')): ?>
                        <form action="<?= site_url('reviews/' . (int) $r['ReviewID'] . '/delete') ?>" method="post" class="mt-2" onsubmit="return confirm('Xóa đánh giá của bạn?')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-link text-danger p-0 text-decoration-underline">Xóa đánh giá của tôi</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>

            <?php if ($reviewPages > 1): ?>
                <nav class="mt-3"><ul class="pagination">
                    <?php for ($i = 1; $i <= $reviewPages; $i++):
                        $href = site_url('products/' . $pid) . '?' . http_build_query(array_filter(['star' => $star ?: null, 'rpage' => $i > 1 ? $i : null])) ; ?>
                        <li class="page-item <?= $i === $reviewPage ? 'active' : '' ?>"><a class="page-link" href="<?= rtrim($href, '?') ?>#reviews"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        </div>

        <!-- Form viết đánh giá -->
        <div class="col-lg-5">
            <div class="rv-form">
                <h3 class="h5 mb-3"><?= $mine ? 'Sửa đánh giá của bạn' : 'Viết đánh giá' ?></h3>

                <?php if (! $loggedIn): ?>
                    <p class="text-muted mb-3">Vui lòng đăng nhập để chia sẻ cảm nhận về sản phẩm.</p>
                    <a class="btn btn-dark" href="<?= site_url('login') ?>">Đăng nhập</a>
                <?php elseif (! $canReview): ?>
                    <p class="text-muted mb-0"><i class="bi bi-info-circle"></i> Chỉ khách đã mua sản phẩm này mới có thể đánh giá.</p>
                <?php else: ?>
                    <?php if ($mine && ! (int) $mine['IsVisible']): ?><div class="alert alert-warning py-2">Đánh giá của bạn đang bị ẩn bởi quản trị viên.</div><?php endif; ?>
                    <?php if ($rErrors): ?><div class="alert alert-danger py-2"><ul class="mb-0 ps-3"><?php foreach ($rErrors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                    <?php if ($purchased): ?><div class="small text-success mb-2"><i class="bi bi-patch-check-fill"></i> Bạn đã mua sản phẩm này</div><?php endif; ?>

                    <form action="<?= site_url('products/' . $pid . '/review') ?>" method="post">
                        <?= csrf_field() ?>
                        <label class="form-label d-block">Bạn chấm mấy sao?</label>
                        <div class="star-input mb-3">
                            <?php for ($s = 5; $s >= 1; $s--): ?>
                                <input type="radio" name="rating" id="st<?= $s ?>" value="<?= $s ?>" <?= $myRating === $s ? 'checked' : '' ?>>
                                <label for="st<?= $s ?>" title="<?= $s ?> sao"><i class="bi bi-star-fill"></i></label>
                            <?php endfor; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rvTitle">Tiêu đề (không bắt buộc)</label>
                            <input class="form-control" id="rvTitle" name="title" maxlength="120" value="<?= e($old('title', $mine['Title'] ?? '')) ?>" placeholder="Ví dụ: Vải đẹp, form chuẩn">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rvText">Nhận xét</label>
                            <textarea class="form-control" id="rvText" name="comment" rows="4" maxlength="1000" required placeholder="Chất vải, form dáng, size bạn mặc..."><?= e($old('comment', $mine['Comment'] ?? '')) ?></textarea>
                        </div>
                        <button class="btn btn-dark w-100"><?= $mine ? 'Cập nhật đánh giá' : 'Gửi đánh giá' ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="container section pt-0">
    <div class="section-title"><h2>Có thể bạn cũng thích</h2></div>
    <div class="row g-3 g-lg-4">
        <?php foreach ($related as $p): ?><div class="col-6 col-lg-3"><?= view('partials/product_card', ['p' => $p]) ?></div><?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Bảng size -->
<div class="modal fade" id="sizeGuide" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Bảng size tham khảo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
        <div class="modal-body">
            <p class="small text-muted">Áo (phom rộng) – chọn theo chiều cao &amp; cân nặng:</p>
            <table class="table table-sm table-bordered size-table">
                <thead><tr><th>Size</th><th>Cao (cm)</th><th>Nặng (kg)</th></tr></thead>
                <tbody><tr><td>S</td><td>155–165</td><td>45–55</td></tr><tr><td>M</td><td>163–172</td><td>54–65</td></tr><tr><td>L</td><td>170–178</td><td>64–75</td></tr><tr><td>XL</td><td>175–185</td><td>74–85</td></tr></tbody>
            </table>
            <p class="small text-muted mt-3">Quần – size theo vòng eo:</p>
            <table class="table table-sm table-bordered size-table">
                <thead><tr><th>Size</th><th>28</th><th>30</th><th>32</th><th>34</th></tr></thead>
                <tbody><tr><td>Vòng eo (cm)</td><td>72</td><td>76</td><td>80</td><td>84</td></tr></tbody>
            </table>
            <p class="small text-muted mb-0">Nếu phân vân giữa 2 size, hãy chọn size lớn hơn để mặc thoải mái.</p>
        </div>
    </div></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.NOVA_VARIANTS = <?= json_encode($variants, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= base_url('assets/js/product.js') ?>?v=3"></script>
<?= $this->endSection() ?>
