<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $e = $shipper !== null; ?>
<form class="card-box" style="max-width:520px" method="post" action="<?= $e ? site_url('admin/shippers/' . (int) $shipper['ShipperID']) : site_url('admin/shippers') ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Tên đơn vị</label><input class="form-control" name="shipper_name" required maxlength="100" value="<?= e(old('shipper_name', $shipper['ShipperName'] ?? '', false)) ?>"></div>
    <div class="mb-3"><label class="form-label">Điện thoại</label><input class="form-control" name="phone" value="<?= e(old('phone', $shipper['Phone'] ?? '', false)) ?>"></div>
    <button class="btn btn-dark">Lưu</button> <a class="btn btn-outline-secondary" href="<?= site_url('admin/shippers') ?>">Hủy</a>
</form>
<?= $this->endSection() ?>
