<?php
/** @var array $p */
$soldOut = (int) $p['StockQuantity'] <= 0;
$url     = site_url('products/' . (int) $p['ProductID']);
?>
<article class="p-card">
    <a class="p-thumb" href="<?= $url ?>">
        <?php if (! empty($p['ImageFile'])): ?>
            <img src="<?= product_img($p['ImageFile']) ?>" alt="<?= e($p['ProductName']) ?>" loading="lazy">
        <?php endif; ?>
        <?php if ($soldOut): ?><span class="p-badge soldout">Hết hàng</span><?php endif; ?>
    </a>
    <div class="p-body">
        <span class="p-cat"><?= e($p['CategoryName']) ?></span>
        <h3 class="p-name"><a href="<?= $url ?>"><?= e($p['ProductName']) ?></a></h3>
        <div class="p-price"><?= money($p['Price']) ?></div>
        <div class="p-meta">
            <?php if ((int) $p['ReviewCount'] > 0): ?>
                <span class="p-rating"><?= stars($p['AvgRating']) ?> <span><?= number_format((float) $p['AvgRating'], 1, ',', '') ?> (<?= (int) $p['ReviewCount'] ?>)</span></span>
            <?php else: ?>
                <span class="p-rating">Chưa có đánh giá</span>
            <?php endif; ?>
            <?php if (! empty($p['Colors'])): ?>
                <span class="dots">
                    <?php foreach (array_slice($p['Colors'], 0, 4) as $c): ?>
                        <span class="dot" style="background:<?= e($c['hex']) ?>" title="<?= e($c['name']) ?>"></span>
                    <?php endforeach; ?>
                    <?php if (count($p['Colors']) > 4): ?><small class="text-muted">+<?= count($p['Colors']) - 4 ?></small><?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</article>
