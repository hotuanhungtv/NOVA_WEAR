<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $e = $category !== null; ?>
<form class="card-box" style="max-width:640px" method="post" action="<?= $e ? site_url('admin/categories/' . (int) $category['CategoryID']) : site_url('admin/categories') ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Tên danh mục</label><input class="form-control" name="category_name" required maxlength="200" value="<?= e(old('category_name', $category['CategoryName'] ?? '', false)) ?>"></div>
    <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" rows="3"><?= e(old('description', $category['Description'] ?? '', false)) ?></textarea></div>
    <button class="btn btn-dark">Lưu</button> <a class="btn btn-outline-secondary" href="<?= site_url('admin/categories') ?>">Hủy</a>
</form>
<?= $this->endSection() ?>
