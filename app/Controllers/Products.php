<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ReviewModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Products extends BaseController
{
    private const PER_PAGE        = 12;
    private const REVIEWS_PER_PAGE = 5;

    /** Danh sách sản phẩm: lọc theo từ khóa, danh mục, sắp xếp + phân trang. */
    public function index(): string
    {
        $model = new ProductModel();

        $categoryId = (int) $this->request->getGet('category');
        $keyword    = trim((string) $this->request->getGet('keyword'));
        $sort       = (string) $this->request->getGet('sort');
        $page       = max(1, (int) $this->request->getGet('page'));

        if (! isset(ProductModel::SORTS[$sort])) {
            $sort = 'new';
        }

        $total      = $model->countListing($categoryId, $keyword);
        $totalPages = (int) ceil($total / self::PER_PAGE);
        $page       = $totalPages > 0 ? min($page, $totalPages) : 1;

        $products = $model->listing($categoryId, $keyword, $sort, self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        $categories = $this->db->table('categories')
            ->select('CategoryID, CategoryName')
            ->orderBy('CategoryName')
            ->get()->getResultArray();

        return view('pages/products', [
            'pageTitle'  => 'Sản phẩm',
            'products'   => $products,
            'categories' => $categories,
            'total'      => $total,
            'totalPages' => $totalPages,
            'page'       => $page,
            'categoryId' => $categoryId,
            'keyword'    => $keyword,
            'sort'       => $sort,
            'sorts'      => ProductModel::SORTS,
        ]);
    }

    /** Chi tiết sản phẩm: chọn size/màu, thêm giỏ, xem & viết đánh giá. */
    public function show(int $id): string
    {
        $model   = new ProductModel();
        $product = $model->detail($id);

        if (! $product) {
            throw PageNotFoundException::forPageNotFound('Không tìm thấy sản phẩm.');
        }

        $reviews = new ReviewModel();

        // ---- Phân trang + lọc đánh giá ----
        $star       = (int) $this->request->getGet('star');
        $star       = ($star >= 1 && $star <= 5) ? $star : 0;
        $rpage      = max(1, (int) $this->request->getGet('rpage'));
        $rTotal     = $reviews->countVisible($id, $star);
        $rPages     = (int) ceil($rTotal / self::REVIEWS_PER_PAGE);
        $rpage      = $rPages > 0 ? min($rpage, $rPages) : 1;

        // ---- Quyền đánh giá của khách đang đăng nhập ----
        $customerId = $this->customerId();
        $mine       = $customerId ? $reviews->mine($id, $customerId) : null;
        $purchased  = $customerId ? $reviews->hasPurchased($id, $customerId) : false;
        $canReview  = $customerId > 0 && (! review_requires_purchase() || $purchased);

        // ---- Biến thể (size + màu) cho bộ chọn ở trang ----
        $variants = array_map(static function (array $v): array {
            return [
                'id'    => (int) $v['VariantID'],
                'size'  => $v['Size'],
                'color' => $v['Color'],
                'hex'   => $v['ColorHex'],
                'stock' => (int) $v['StockQuantity'],
                'image' => $v['ImageFile'] ? product_img($v['ImageFile']) : '',
            ];
        }, $model->variants($id));

        return view('pages/product_detail', [
            'pageTitle'   => $product['ProductName'],
            'product'     => $product,
            'images'      => $model->images($id),
            'variants'    => $variants,
            'related'     => $model->related($id, (int) $product['CategoryID'], 4),
            'stats'       => $reviews->stats($id),
            'reviewList'  => $reviews->listVisible($id, $star, self::REVIEWS_PER_PAGE, ($rpage - 1) * self::REVIEWS_PER_PAGE),
            'reviewTotal' => $rTotal,
            'reviewPages' => $rPages,
            'reviewPage'  => $rpage,
            'star'        => $star,
            'mine'        => $mine,
            'purchased'   => $purchased,
            'canReview'   => $canReview,
            'needPurchase' => review_requires_purchase() && ! $purchased,
        ]);
    }
}
