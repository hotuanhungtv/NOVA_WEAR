<?php

namespace App\Controllers;

use App\Models\ProductModel;

/** REST API (JSON) cho sản phẩm và danh mục. */
class Api extends BaseController
{
    public function products()
    {
        $keyword    = trim((string) $this->request->getGet('keyword'));
        $categoryId = (int) $this->request->getGet('category');

        $rows = (new ProductModel())->listing($categoryId, $keyword, 'new', 200, 0);

        $data = array_map(static fn (array $r): array => [
            'id'         => (int) $r['ProductID'],
            'code'       => $r['ProductCode'],
            'name'       => $r['ProductName'],
            'price'      => (float) $r['Price'],
            'stock'      => (int) $r['StockQuantity'],
            'category'   => $r['CategoryName'],
            'image'      => $r['ImageFile'] ? product_img($r['ImageFile']) : null,
            'rating'     => $r['AvgRating'] !== null ? (float) $r['AvgRating'] : null,
            'reviews'    => (int) $r['ReviewCount'],
            'colors'     => $r['Colors'],
            'url'        => base_url('products/' . $r['ProductID']),
        ], $rows);

        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }

    public function categories()
    {
        $rows = $this->db->table('categories')->select('CategoryID, CategoryName')->orderBy('CategoryName')->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data'    => array_map(static fn ($r) => ['id' => (int) $r['CategoryID'], 'name' => $r['CategoryName']], $rows),
        ]);
    }

    /** Trang minh họa gọi API bằng fetch(). */
    public function demo(): string
    {
        return view('pages/products_api', ['pageTitle' => 'Sản phẩm từ REST API']);
    }
}
