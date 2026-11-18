<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<form class="card-box mb-3 row g-2 align-items-end" method="get">
    <div class="col-md-3"><label class="form-label small">Hiển thị</label><select class="form-select" name="show">
        <option value="all" <?= $show === 'all' ? 'selected' : '' ?>>Tất cả</option>
        <option value="visible" <?= $show === 'visible' ? 'selected' : '' ?>>Đang hiển thị</option>
        <option value="hidden" <?= $show === 'hidden' ? 'selected' : '' ?>>Đã ẩn</option></select></div>
    <div class="col-md-3"><label class="form-label small">Số sao</label><select class="form-select" name="rating">
        <option value="0">Tất cả</option><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>><?= $i ?> sao</option><?php endfor; ?></select></div>
    <div class="col-md-3"><button class="btn btn-dark">Lọc</button></div>
</form>
<div class="card-box p-0 table-responsive"><table class="table mb-0">
    <thead><tr><th>Sản phẩm</th><th>Khách</th><th>Đánh giá</th><th>Ngày</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($reviews as $r): ?>
        <tr class="<?= (int) $r['IsVisible'] ? '' : 'table-secondary' ?>">
            <td style="max-width:200px"><a href="<?= site_url('products/' . (int) $r['ProductID']) ?>#reviews" target="_blank"><?= e($r['ProductName']) ?></a></td>
            <td><?= e($r['CustomerName']) ?></td>
            <td style="min-width:260px"><?= stars($r['Rating']) ?><?php if ($r['Title']): ?> <strong><?= e($r['Title']) ?></strong><?php endif; ?><div class="small"><?= e(mb_strimwidth($r['Comment'], 0, 160, '…')) ?></div></td>
            <td class="small text-nowrap"><?= e(date('d/m/Y H:i', strtotime($r['CreatedAt']))) ?></td>
            <td><span class="badge <?= (int) $r['IsVisible'] ? 'bg-success' : 'bg-secondary' ?>"><?= (int) $r['IsVisible'] ? 'Hiển thị' : 'Đã ẩn' ?></span></td>
            <td class="text-end text-nowrap">
                <form class="d-inline" method="post" action="<?= site_url('admin/reviews/' . (int) $r['ReviewID'] . '/toggle') ?>"><?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-dark"><?= (int) $r['IsVisible'] ? 'Ẩn' : 'Hiện' ?></button></form>
                <form class="d-inline" method="post" action="<?= site_url('admin/reviews/' . (int) $r['ReviewID'] . '/delete') ?>" onsubmit="return confirm('Xóa vĩnh viễn đánh giá này?')"><?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (! $reviews): ?><tr><td colspan="6" class="text-center text-muted py-4">Chưa có đánh giá.</td></tr><?php endif; ?>
    </tbody></table></div>
<?= $this->endSection() ?>
