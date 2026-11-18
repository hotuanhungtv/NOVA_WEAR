<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * Truy vấn sản phẩm cho trang khách hàng (kèm tồn kho theo size/màu, điểm đánh giá).
 */
class ProductModel extends Model
{
    protected $table      = 'products';
    protected $primaryKey = 'ProductID';
    protected $returnType = 'array';

    /** Các sắp xếp được phép: khóa => nhãn */
    public const SORTS = [
        'new'        => 'Mới nhất',
        'price_asc'  => 'Giá tăng dần',
        'price_desc' => 'Giá giảm dần',
        'rating'     => 'Đánh giá cao',
    ];

    /** Truy vấn nền cho thẻ sản phẩm. */
    private function cardQuery(): BaseBuilder
    {
        return $this->db->table('products p')
            ->select('p.ProductID, p.ProductCode, p.ProductName, p.Price, c.CategoryID, c.CategoryName')
            ->select('(SELECT pi.ImageFile FROM product_images pi WHERE pi.ProductID = p.ProductID AND pi.IsPrimary = 1 LIMIT 1) AS ImageFile', false)
            ->select('(SELECT COALESCE(SUM(v.StockQuantity), 0) FROM product_variants v WHERE v.ProductID = p.ProductID) AS StockQuantity', false)
            ->select('(SELECT ROUND(AVG(r.Rating), 1) FROM reviews r WHERE r.ProductID = p.ProductID AND r.IsVisible = 1) AS AvgRating', false)
            ->select('(SELECT COUNT(*) FROM reviews r2 WHERE r2.ProductID = p.ProductID AND r2.IsVisible = 1) AS ReviewCount', false)
            ->join('categories c', 'c.CategoryID = p.CategoryID')
            ->where('p.IsActive', 1);
    }

    private function applyFilters(BaseBuilder $builder, int $categoryId, string $keyword): void
    {
        if ($categoryId > 0) {
            $builder->where('p.CategoryID', $categoryId);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('p.ProductName', $keyword)
                ->orLike('p.ProductCode', $keyword)
                ->groupEnd();
        }
    }

    /** Danh sách có lọc + sắp xếp + phân trang. */
    public function listing(int $categoryId, string $keyword, string $sort, int $limit, int $offset): array
    {
        $builder = $this->cardQuery();
        $this->applyFilters($builder, $categoryId, $keyword);

        switch ($sort) {
            case 'price_asc':
                $builder->orderBy('p.Price', 'ASC')->orderBy('p.ProductID', 'DESC');
                break;
            case 'price_desc':
                $builder->orderBy('p.Price', 'DESC')->orderBy('p.ProductID', 'DESC');
                break;
            case 'rating':
                $builder->orderBy('AvgRating IS NULL', 'ASC', false)
                    ->orderBy('AvgRating', 'DESC', false)
                    ->orderBy('ReviewCount', 'DESC', false)
                    ->orderBy('p.ProductID', 'DESC');
                break;
            default:
                $builder->orderBy('p.ProductID', 'DESC');
        }

        $rows = $builder->limit($limit, $offset)->get()->getResultArray();

        return $this->withColors($rows);
    }

    public function countListing(int $categoryId, string $keyword): int
    {
        $builder = $this->db->table('products p')->where('p.IsActive', 1);
        $this->applyFilters($builder, $categoryId, $keyword);

        return (int) $builder->countAllResults();
    }

    public function latest(int $limit): array
    {
        $rows = $this->cardQuery()->orderBy('p.ProductID', 'DESC')->limit($limit)->get()->getResultArray();

        return $this->withColors($rows);
    }

    /** Sản phẩm cùng danh mục (gợi ý ở trang chi tiết). */
    public function related(int $productId, int $categoryId, int $limit = 4): array
    {
        $rows = $this->cardQuery()
            ->where('p.CategoryID', $categoryId)
            ->where('p.ProductID !=', $productId)
            ->orderBy('p.ProductID', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();

        return $this->withColors($rows);
    }

    /** Chi tiết một sản phẩm đang bán (null nếu không có). */
    public function detail(int $id): ?array
    {
        $row = $this->cardQuery()
            ->select('p.Description, p.Unit, s.SupplierName')
            ->join('suppliers s', 's.SupplierID = p.SupplierID')
            ->where('p.ProductID', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    public function images(int $productId): array
    {
        return $this->db->table('product_images')
            ->select('ProductImageID, ImageFile, IsPrimary')
            ->where('ProductID', $productId)
            ->orderBy('IsPrimary', 'DESC')
            ->orderBy('SortOrder', 'ASC')
            ->orderBy('ProductImageID', 'ASC')
            ->get()->getResultArray();
    }

    public function variants(int $productId): array
    {
        return $this->db->table('product_variants')
            ->where('ProductID', $productId)
            ->orderBy('SortOrder', 'ASC')
            ->orderBy('VariantID', 'ASC')
            ->get()->getResultArray();
    }

    /** Danh mục + số sản phẩm + ảnh bìa (trang chủ). */
    public function categoriesWithCover(): array
    {
        return $this->db->query("
            SELECT c.CategoryID, c.CategoryName, c.Description,
                   COUNT(p.ProductID) AS total,
                   (SELECT pi.ImageFile
                      FROM product_images pi
                      JOIN products p2 ON p2.ProductID = pi.ProductID
                     WHERE p2.CategoryID = c.CategoryID AND p2.IsActive = 1 AND pi.IsPrimary = 1
                     ORDER BY p2.ProductID LIMIT 1) AS CoverImage
              FROM categories c
              LEFT JOIN products p ON p.CategoryID = c.CategoryID AND p.IsActive = 1
             GROUP BY c.CategoryID, c.CategoryName, c.Description
             ORDER BY c.CategoryID
        ")->getResultArray();
    }

    /**
     * Gắn danh sách màu (để vẽ chấm màu trên thẻ sản phẩm) vào từng dòng.
     *
     * @param list<array<string,mixed>> $rows
     */
    private function withColors(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $ids    = array_map(static fn ($r) => (int) $r['ProductID'], $rows);
        $colors = $this->db->table('product_variants')
            ->select('ProductID, Color, ColorHex, MIN(SortOrder) AS so', false)
            ->whereIn('ProductID', $ids)
            ->groupBy('ProductID, Color, ColorHex')
            ->orderBy('ProductID', 'ASC')
            ->orderBy('so', 'ASC', false)
            ->get()->getResultArray();

        $map = [];

        foreach ($colors as $c) {
            $map[(int) $c['ProductID']][] = ['name' => $c['Color'], 'hex' => $c['ColorHex']];
        }

        foreach ($rows as &$row) {
            $row['Colors'] = $map[(int) $row['ProductID']] ?? [];
        }
        unset($row);

        return $rows;
    }
}
