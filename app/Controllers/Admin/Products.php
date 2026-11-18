<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\Files\UploadedFile;
use DomainException;
use Throwable;

/** Quản lý sản phẩm: thông tin, nhiều ảnh, và các biến thể size + màu (kèm tồn kho). */
class Products extends BaseAdmin
{
    private const MAX_IMAGES = 8;
    private const MAX_BYTES  = 2 * 1024 * 1024;
    private const MIMES      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    // ---------------------------------------------------------------- danh sách
    public function index(): string
    {
        $keyword    = trim((string) $this->request->getGet('keyword'));
        $categoryId = (int) $this->request->getGet('category');

        $b = $this->db->table('products p')
            ->select('p.ProductID, p.ProductCode, p.ProductName, p.Price, p.IsActive, c.CategoryName')
            ->select('(SELECT pi.ImageFile FROM product_images pi WHERE pi.ProductID = p.ProductID AND pi.IsPrimary = 1 LIMIT 1) AS ImageFile', false)
            ->select('(SELECT COALESCE(SUM(v.StockQuantity),0) FROM product_variants v WHERE v.ProductID = p.ProductID) AS StockQuantity', false)
            ->select('(SELECT COUNT(*) FROM product_variants v2 WHERE v2.ProductID = p.ProductID) AS VariantCount', false)
            ->join('categories c', 'c.CategoryID = p.CategoryID');

        if ($categoryId > 0) {
            $b->where('p.CategoryID', $categoryId);
        }
        if ($keyword !== '') {
            $b->groupStart()->like('p.ProductName', $keyword)->orLike('p.ProductCode', $keyword)->groupEnd();
        }

        return view('admin/products/index', [
            'pageTitle'  => 'Quản lý sản phẩm',
            'products'   => $b->orderBy('p.ProductID', 'DESC')->get()->getResultArray(),
            'categories' => $this->categories(),
            'keyword'    => $keyword,
            'categoryId' => $categoryId,
        ]);
    }

    private function categories(): array
    {
        return $this->db->table('categories')->orderBy('CategoryName')->get()->getResultArray();
    }

    private function suppliers(): array
    {
        return $this->db->table('suppliers')->orderBy('SupplierName')->get()->getResultArray();
    }

    // ---------------------------------------------------------------- tạo mới
    public function create(): string
    {
        return view('admin/products/form', [
            'pageTitle'  => 'Thêm sản phẩm',
            'product'    => null,
            'images'     => [],
            'variants'   => [],
            'categories' => $this->categories(),
            'suppliers'  => $this->suppliers(),
        ]);
    }

    public function store()
    {
        [$data, $errors] = $this->readProduct(0);
        [$variants, $vErrors] = $this->readVariants([]);
        $errors = array_merge($errors, $vErrors);

        $files = $this->uploadedFiles();
        $errors = array_merge($errors, $this->checkFiles($files, 0));

        if ($errors) {
            return redirect()->back()->withInput()->with('form_errors', $errors);
        }

        $db    = $this->db;
        $saved = [];
        $db->transBegin();

        try {
            $db->table('products')->insert($data);
            $productId = (int) $db->insertID();

            $saved = $this->saveFiles($files, $data['ProductCode']);
            foreach ($saved as $i => $name) {
                $db->table('product_images')->insert([
                    'ProductID' => $productId, 'ImageFile' => $name, 'AltText' => $data['ProductName'],
                    'IsPrimary' => $i === 0 ? 1 : 0, 'SortOrder' => $i + 1,
                ]);
            }

            foreach ($variants as $i => $v) {
                $db->table('product_variants')->insert([
                    'ProductID' => $productId, 'Size' => $v['size'], 'Color' => $v['color'],
                    'ColorHex' => $v['hex'], 'StockQuantity' => $v['stock'], 'ImageFile' => null, 'SortOrder' => $i,
                ]);
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            $this->deleteFiles($saved);
            log_message('error', 'Thêm sản phẩm lỗi: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('danger', 'Không thể lưu sản phẩm. Vui lòng thử lại.');
        }

        return redirect()->to('/admin/products/' . $productId . '/edit')
            ->with('success', 'Đã thêm sản phẩm. Bạn có thể gán ảnh riêng cho từng màu ở bảng size/màu bên dưới.');
    }

    // ---------------------------------------------------------------- sửa
    private function find(int $id): ?array
    {
        return $this->db->table('products')->where('ProductID', $id)->get()->getRowArray();
    }

    private function imagesOf(int $id): array
    {
        return $this->db->table('product_images')->where('ProductID', $id)
            ->orderBy('IsPrimary', 'DESC')->orderBy('SortOrder')->orderBy('ProductImageID')
            ->get()->getResultArray();
    }

    private function variantsOf(int $id): array
    {
        return $this->db->table('product_variants')->where('ProductID', $id)
            ->orderBy('SortOrder')->orderBy('VariantID')->get()->getResultArray();
    }

    public function edit(int $id)
    {
        $product = $this->find($id);

        if (! $product) {
            return redirect()->to('/admin/products')->with('danger', 'Không tìm thấy sản phẩm.');
        }

        return view('admin/products/form', [
            'pageTitle'  => 'Sửa sản phẩm',
            'product'    => $product,
            'images'     => $this->imagesOf($id),
            'variants'   => $this->variantsOf($id),
            'categories' => $this->categories(),
            'suppliers'  => $this->suppliers(),
        ]);
    }

    public function update(int $id)
    {
        $product = $this->find($id);

        if (! $product) {
            return redirect()->to('/admin/products')->with('danger', 'Không tìm thấy sản phẩm.');
        }

        $images   = $this->imagesOf($id);
        $imgById  = [];
        foreach ($images as $img) {
            $imgById[(int) $img['ProductImageID']] = $img;
        }

        $deleteIds = array_values(array_intersect(
            array_map('intval', (array) $this->request->getPost('delete_images')),
            array_keys($imgById)
        ));
        $remaining = array_values(array_filter($images, static fn ($i) => ! in_array((int) $i['ProductImageID'], $deleteIds, true)));

        [$data, $errors]      = $this->readProduct($id);
        $allowedImages        = array_column($remaining, 'ImageFile');
        [$variants, $vErrors] = $this->readVariants($allowedImages);
        $files                = $this->uploadedFiles();
        $errors               = array_merge($errors, $vErrors, $this->checkFiles($files, count($remaining)));

        if ($errors) {
            return redirect()->back()->withInput()->with('form_errors', $errors);
        }

        $db       = $this->db;
        $newFiles = [];
        $db->transBegin();

        try {
            $db->table('products')->where('ProductID', $id)->update($data);

            // ---- ảnh ----
            if ($deleteIds) {
                $db->table('product_images')->whereIn('ProductImageID', $deleteIds)->delete();
            }

            $newFiles = $this->saveFiles($files, $data['ProductCode']);
            $sort     = count($remaining);
            foreach ($newFiles as $name) {
                $db->table('product_images')->insert([
                    'ProductID' => $id, 'ImageFile' => $name, 'AltText' => $data['ProductName'],
                    'IsPrimary' => 0, 'SortOrder' => ++$sort,
                ]);
            }

            $all       = $this->imagesOf($id);
            $primaryId = (int) $this->request->getPost('primary_image');
            $ids       = array_map(static fn ($i) => (int) $i['ProductImageID'], $all);
            if (! in_array($primaryId, $ids, true)) {
                $primaryId = $ids[0] ?? 0;
            }
            foreach ($all as $n => $img) {
                $db->table('product_images')->where('ProductImageID', (int) $img['ProductImageID'])->update([
                    'IsPrimary' => (int) $img['ProductImageID'] === $primaryId ? 1 : 0,
                    'SortOrder' => $n + 1,
                ]);
            }

            // ---- biến thể size/màu ----
            $existing = [];
            foreach ($this->variantsOf($id) as $v) {
                $existing[(int) $v['VariantID']] = $v;
            }
            $keep = [];
            foreach ($variants as $i => $v) {
                $row = [
                    'Size' => $v['size'], 'Color' => $v['color'], 'ColorHex' => $v['hex'],
                    'StockQuantity' => $v['stock'], 'ImageFile' => $v['image'] ?: null, 'SortOrder' => $i,
                ];
                if ($v['id'] > 0 && isset($existing[$v['id']])) {
                    $db->table('product_variants')->where('VariantID', $v['id'])->update($row);
                    $keep[] = $v['id'];
                } else {
                    $db->table('product_variants')->insert($row + ['ProductID' => $id]);
                }
            }
            $drop = array_diff(array_keys($existing), $keep);
            if ($drop) {
                $db->table('product_variants')->whereIn('VariantID', $drop)->delete();
            }

            // ảnh biến thể trỏ tới ảnh vừa xóa -> bỏ liên kết
            $names = array_column($this->imagesOf($id), 'ImageFile');
            $q = $db->table('product_variants')->where('ProductID', $id)->where('ImageFile IS NOT NULL', null, false);
            if ($names) {
                $q->whereNotIn('ImageFile', $names);
            }
            $q->update(['ImageFile' => null]);

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            $this->deleteFiles($newFiles);
            log_message('error', 'Sửa sản phẩm lỗi: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('danger', 'Không thể lưu thay đổi. Kiểm tra lại size/màu có bị trùng không.');
        }

        $this->deleteFiles(array_map(static fn ($i) => $imgById[$i]['ImageFile'], $deleteIds));

        return redirect()->to('/admin/products/' . $id . '/edit')->with('success', 'Đã lưu sản phẩm.');
    }

    // ---------------------------------------------------------------- xóa
    public function delete(int $id)
    {
        $product = $this->find($id);

        if (! $product) {
            return redirect()->to('/admin/products')->with('danger', 'Không tìm thấy sản phẩm.');
        }

        // Đã từng được đặt -> chỉ ngừng bán để giữ lịch sử đơn hàng
        if ($this->db->table('orderdetail')->where('ProductID', $id)->countAllResults() > 0) {
            $this->db->table('products')->where('ProductID', $id)->update(['IsActive' => 0]);

            return redirect()->to('/admin/products')->with('warning', 'Sản phẩm đã có trong đơn hàng nên được chuyển sang "Ngừng bán" thay vì xóa.');
        }

        $files = array_column($this->imagesOf($id), 'ImageFile');
        $this->db->table('products')->where('ProductID', $id)->delete();   // ảnh, size/màu, đánh giá xóa theo (CASCADE)
        $this->deleteFiles($files);

        return redirect()->to('/admin/products')->with('success', 'Đã xóa sản phẩm.');
    }

    // ---------------------------------------------------------------- đọc & kiểm tra dữ liệu form
    /** @return array{0:array<string,mixed>,1:list<string>} */
    private function readProduct(int $id): array
    {
        $p = $this->request;
        $errors = [];

        $code  = trim((string) $p->getPost('product_code'));
        $name  = trim((string) $p->getPost('product_name'));
        $price = (string) $p->getPost('price');
        $catId = (int) $p->getPost('category_id');
        $supId = (int) $p->getPost('supplier_id');

        if ($code === '' || mb_strlen($code) > 50) {
            $errors[] = 'Mã sản phẩm bắt buộc và tối đa 50 ký tự.';
        } else {
            $q = $this->db->table('products')->where('ProductCode', $code);
            if ($id > 0) {
                $q->where('ProductID !=', $id);
            }
            if ($q->countAllResults() > 0) {
                $errors[] = 'Mã sản phẩm "' . $code . '" đã tồn tại.';
            }
        }
        if ($name === '' || mb_strlen($name) > 255) {
            $errors[] = 'Tên sản phẩm bắt buộc và tối đa 255 ký tự.';
        }
        if (! is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Giá bán không hợp lệ.';
        }
        if (! $this->db->table('categories')->where('CategoryID', $catId)->countAllResults()) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }
        if (! $this->db->table('suppliers')->where('SupplierID', $supId)->countAllResults()) {
            $errors[] = 'Vui lòng chọn nhà cung cấp.';
        }

        return [[
            'ProductCode' => $code,
            'ProductName' => $name,
            'Description' => trim((string) $p->getPost('description')),
            'Unit'        => trim((string) $p->getPost('unit')) ?: 'Cái',
            'Price'       => is_numeric($price) ? (float) $price : 0,
            'IsActive'    => $p->getPost('is_active') ? 1 : 0,
            'CategoryID'  => $catId,
            'SupplierID'  => $supId,
        ], $errors];
    }

    /**
     * Đọc bảng size/màu từ form: variants[i][id|size|color|hex|stock|image]
     *
     * @param list<string> $allowedImages ảnh hợp lệ có thể gán cho biến thể
     * @return array{0:list<array<string,mixed>>,1:list<string>}
     */
    private function readVariants(array $allowedImages): array
    {
        $rows = [];
        $errors = [];
        $seen = [];

        foreach ((array) $this->request->getPost('variants') as $v) {
            if (! is_array($v)) {
                continue;
            }
            $size  = trim((string) ($v['size'] ?? ''));
            $color = trim((string) ($v['color'] ?? ''));
            if ($size === '' && $color === '') {
                continue; // dòng trống
            }

            $hex   = (string) ($v['hex'] ?? '#111111');
            $stock = $v['stock'] ?? 0;
            $image = (string) ($v['image'] ?? '');

            if ($size === '' || mb_strlen($size) > 20) {
                $errors[] = 'Size không được để trống và tối đa 20 ký tự.';
                continue;
            }
            if ($color === '' || mb_strlen($color) > 50) {
                $errors[] = 'Tên màu không được để trống và tối đa 50 ký tự.';
                continue;
            }
            if (! preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
                $hex = '#111111';
            }
            if (! is_numeric($stock) || (int) $stock < 0) {
                $errors[] = 'Tồn kho của "' . $size . ' / ' . $color . '" không hợp lệ.';
                continue;
            }

            $key = mb_strtolower($size . '|' . $color);
            if (isset($seen[$key])) {
                $errors[] = 'Trùng tổ hợp size/màu: ' . $size . ' / ' . $color . '.';
                continue;
            }
            $seen[$key] = true;

            $rows[] = [
                'id' => (int) ($v['id'] ?? 0), 'size' => $size, 'color' => $color, 'hex' => strtolower($hex),
                'stock' => (int) $stock, 'image' => in_array($image, $allowedImages, true) ? $image : '',
            ];
        }

        if ($rows === [] && $errors === []) {
            $errors[] = 'Cần ít nhất 1 tổ hợp size/màu (sản phẩm không có size thì dùng "Free size").';
        }

        return [$rows, $errors];
    }

    // ---------------------------------------------------------------- ảnh
    /** @return list<UploadedFile> */
    private function uploadedFiles(): array
    {
        $files = $this->request->getFileMultiple('product_images') ?: [];

        return array_values(array_filter($files, static fn ($f) => $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE));
    }

    /** @param list<UploadedFile> $files */
    private function checkFiles(array $files, int $already): array
    {
        $errors = [];

        if ($already + count($files) > self::MAX_IMAGES) {
            $errors[] = 'Mỗi sản phẩm tối đa ' . self::MAX_IMAGES . ' ảnh.';
        }

        foreach ($files as $f) {
            if (! $f->isValid()) {
                $errors[] = 'Ảnh "' . $f->getClientName() . '" tải lên bị lỗi.';
            } elseif ($f->getSize() > self::MAX_BYTES) {
                $errors[] = 'Ảnh "' . $f->getClientName() . '" vượt quá 2MB.';
            } elseif (! isset(self::MIMES[$f->getMimeType()])) {
                $errors[] = 'Ảnh "' . $f->getClientName() . '" phải là JPG, PNG hoặc WebP.';
            }
        }

        return $errors;
    }

    /** @param list<UploadedFile> $files @return list<string> tên file đã lưu */
    private function saveFiles(array $files, string $code): array
    {
        $dir   = FCPATH . 'uploads/products';
        $slug  = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($code)), '-') ?: 'sp';
        $saved = [];

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach ($files as $f) {
            $name = $slug . '-' . bin2hex(random_bytes(5)) . '.' . self::MIMES[$f->getMimeType()];
            $f->move($dir, $name);
            $saved[] = $name;
        }

        return $saved;
    }

    /** @param list<string> $names */
    private function deleteFiles(array $names): void
    {
        foreach ($names as $name) {
            $path = FCPATH . 'uploads/products/' . basename((string) $name);
            // không xóa ảnh mẫu đang được sản phẩm khác dùng chung
            $inUse = $this->db->table('product_images')->where('ImageFile', $name)->countAllResults() > 0;
            if (! $inUse && is_file($path)) {
                @unlink($path);
            }
        }
    }
}
