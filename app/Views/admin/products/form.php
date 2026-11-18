<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$isEdit = $product !== null;
$action = $isEdit ? site_url('admin/products/' . (int) $product['ProductID']) : site_url('admin/products');
$val    = static fn (string $k, $d = '') => old($k, $d, false) ?? $d;
$oldV   = old('variants', null, false);
$rows   = [];
if (is_array($oldV)) {
    foreach ($oldV as $v) { $rows[] = ['id' => $v['id'] ?? 0, 'size' => $v['size'] ?? '', 'color' => $v['color'] ?? '', 'hex' => $v['hex'] ?? '#111111', 'stock' => $v['stock'] ?? 0, 'image' => $v['image'] ?? '']; }
} else {
    foreach ($variants as $v) { $rows[] = ['id' => $v['VariantID'], 'size' => $v['Size'], 'color' => $v['Color'], 'hex' => $v['ColorHex'], 'stock' => $v['StockQuantity'], 'image' => $v['ImageFile'] ?? '']; }
}
$active = $isEdit ? (int) $product['IsActive'] : 1;
if (old('product_name') !== null) { $active = old('is_active') ? 1 : 0; }
?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="productForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-box mb-3">
            <h2 class="h6 mb-3">Thông tin sản phẩm</h2>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mã sản phẩm</label><input class="form-control" name="product_code" required maxlength="50" value="<?= e($val('product_code', $product['ProductCode'] ?? '')) ?>"></div>
                <div class="col-md-8"><label class="form-label">Tên sản phẩm</label><input class="form-control" name="product_name" required maxlength="255" value="<?= e($val('product_name', $product['ProductName'] ?? '')) ?>"></div>
                <div class="col-12"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" rows="4"><?= e($val('description', $product['Description'] ?? '')) ?></textarea></div>
                <div class="col-md-4"><label class="form-label">Giá bán (đ)</label><input class="form-control" type="number" min="0" step="1000" name="price" required value="<?= e($val('price', isset($product['Price']) ? (int) $product['Price'] : '')) ?>"></div>
                <div class="col-md-4"><label class="form-label">Đơn vị</label><input class="form-control" name="unit" maxlength="20" value="<?= e($val('unit', $product['Unit'] ?? 'Cái')) ?>"></div>
                <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" <?= $active ? 'checked' : '' ?>><label class="form-check-label" for="act">Đang bán</label></div></div>
                <div class="col-md-6"><label class="form-label">Danh mục</label><select class="form-select" name="category_id" required><option value="">— Chọn —</option>
                    <?php $cv = (int) $val('category_id', $product['CategoryID'] ?? 0); foreach ($categories as $c): ?><option value="<?= (int) $c['CategoryID'] ?>" <?= $cv === (int) $c['CategoryID'] ? 'selected' : '' ?>><?= e($c['CategoryName']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Nhà cung cấp</label><select class="form-select" name="supplier_id" required><option value="">— Chọn —</option>
                    <?php $sv = (int) $val('supplier_id', $product['SupplierID'] ?? 0); foreach ($suppliers as $s): ?><option value="<?= (int) $s['SupplierID'] ?>" <?= $sv === (int) $s['SupplierID'] ? 'selected' : '' ?>><?= e($s['SupplierName']) ?></option><?php endforeach; ?></select></div>
            </div>
        </div>

        <!-- ===== SIZE & MÀU ===== -->
        <div class="card-box mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h6 mb-0">Size &amp; màu (tồn kho theo từng phiên bản)</h2>
                <button type="button" class="btn btn-sm btn-outline-dark" id="addRow"><i class="bi bi-plus-lg"></i> Thêm dòng</button></div>

            <div class="border rounded p-3 mb-3 bg-light">
                <div class="small fw-semibold mb-2">Tạo nhanh nhiều tổ hợp</div>
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control form-control-sm" id="genSizes" placeholder="Size: S, M, L, XL"></div>
                    <div class="col-md-5"><input class="form-control form-control-sm" id="genColors" placeholder="Màu: Đen:#111111, Trắng:#f4f4f1"></div>
                    <div class="col-md-1"><input class="form-control form-control-sm" id="genStock" type="number" min="0" value="10" title="Tồn kho mỗi dòng"></div>
                    <div class="col-md-2"><button type="button" class="btn btn-sm btn-dark w-100" id="genBtn">Tạo</button></div>
                </div>
                <div class="form-text">Dòng đã có (trùng size + màu) sẽ được giữ nguyên. Sản phẩm không có size: nhập "Free size".</div>
            </div>

            <div class="table-responsive"><table class="table table-sm var-table align-middle mb-0">
                <thead><tr><th>Size</th><th>Tên màu</th><th>Mã màu</th><th>Tồn kho</th><?php if ($isEdit): ?><th>Ảnh theo màu</th><?php endif; ?><th></th></tr></thead>
                <tbody id="varBody"></tbody>
            </table></div>
        </div>
    </div>

    <!-- ===== ẢNH ===== -->
    <div class="col-lg-4">
        <div class="card-box">
            <h2 class="h6 mb-3">Hình ảnh (tối đa 8 ảnh, mỗi ảnh ≤ 2MB)</h2>
            <?php if ($images): ?>
                <div class="img-pick mb-3">
                <?php foreach ($images as $img): ?>
                    <label><img src="<?= product_img($img['ImageFile']) ?>" alt="">
                        <div class="form-check"><input class="form-check-input" type="radio" name="primary_image" value="<?= (int) $img['ProductImageID'] ?>" <?= (int) $img['IsPrimary'] ? 'checked' : '' ?>> Ảnh chính</div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= (int) $img['ProductImageID'] ?>"> Xóa</div>
                    </label>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <label class="form-label">Thêm ảnh mới</label>
            <input class="form-control" type="file" name="product_images[]" accept="image/jpeg,image/png,image/webp" multiple>
            <div class="form-text">JPG, PNG, WebP. Ảnh đầu tiên sẽ là ảnh chính nếu chưa có ảnh.</div>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button class="btn btn-dark"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
            <a class="btn btn-outline-secondary" href="<?= site_url('admin/products') ?>">Hủy</a>
        </div>
    </div>
</div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.VARIANT_ROWS = <?= json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
window.PRODUCT_IMAGES = <?= json_encode($isEdit ? array_column($images, 'ImageFile') : [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
window.CAN_PICK_IMAGE = <?= $isEdit ? 'true' : 'false' ?>;
</script>
<script src="<?= base_url('assets/js/admin-product.js') ?>?v=3"></script>
<?= $this->endSection() ?>
