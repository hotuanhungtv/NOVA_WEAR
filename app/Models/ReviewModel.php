<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Nhận xét & đánh giá của khách hàng.
 */
class ReviewModel extends Model
{
    protected $table      = 'reviews';
    protected $primaryKey = 'ReviewID';
    protected $returnType = 'array';

    /** Điều kiện "đã mua hàng": có đơn Đang giao / Hoàn thành chứa sản phẩm này. */
    private const VERIFIED_SQL = "EXISTS (
        SELECT 1 FROM orders o
          JOIN orderdetail od ON od.OrderID = o.OrderID
         WHERE o.CustomerID = r.CustomerID
           AND od.ProductID = r.ProductID
           AND o.Status IN ('Shipping', 'Completed')
    )";

    /**
     * Thống kê các bài đang hiển thị.
     *
     * @return array{avg:float,count:int,dist:array<int,int>}
     */
    public function stats(int $productId): array
    {
        $rows = $this->db->table('reviews')
            ->select('Rating, COUNT(*) AS total', false)
            ->where('ProductID', $productId)
            ->where('IsVisible', 1)
            ->groupBy('Rating')
            ->get()->getResultArray();

        $dist  = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $count = 0;
        $sum   = 0;

        foreach ($rows as $row) {
            $star        = (int) $row['Rating'];
            $dist[$star] = (int) $row['total'];
            $count      += (int) $row['total'];
            $sum        += $star * (int) $row['total'];
        }

        return [
            'avg'   => $count > 0 ? round($sum / $count, 1) : 0.0,
            'count' => $count,
            'dist'  => $dist,
        ];
    }

    private function visibleQuery(int $productId, int $star)
    {
        $builder = $this->db->table('reviews r')
            ->join('customers c', 'c.CustomerID = r.CustomerID')
            ->where('r.ProductID', $productId)
            ->where('r.IsVisible', 1);

        if ($star >= 1 && $star <= 5) {
            $builder->where('r.Rating', $star);
        }

        return $builder;
    }

    public function countVisible(int $productId, int $star = 0): int
    {
        return (int) $this->visibleQuery($productId, $star)->countAllResults();
    }

    public function listVisible(int $productId, int $star, int $limit, int $offset): array
    {
        return $this->visibleQuery($productId, $star)
            ->select('r.ReviewID, r.Rating, r.Title, r.Comment, r.CreatedAt, r.UpdatedAt, r.CustomerID, c.CustomerName')
            ->select(self::VERIFIED_SQL . ' AS Verified', false)
            ->orderBy('r.CreatedAt', 'DESC')
            ->orderBy('r.ReviewID', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResultArray();
    }

    /** Bài đánh giá của chính khách này (kể cả đang bị ẩn). */
    public function mine(int $productId, int $customerId): ?array
    {
        $row = $this->db->table('reviews')
            ->where('ProductID', $productId)
            ->where('CustomerID', $customerId)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Khách đã có đơn Đang giao / Hoàn thành chứa sản phẩm này chưa? */
    public function hasPurchased(int $productId, int $customerId): bool
    {
        $row = $this->db->query(
            "SELECT 1 FROM orders o
               JOIN orderdetail od ON od.OrderID = o.OrderID
              WHERE o.CustomerID = ? AND od.ProductID = ?
                AND o.Status IN ('Shipping', 'Completed')
              LIMIT 1",
            [$customerId, $productId]
        )->getRowArray();

        return (bool) $row;
    }

    /** Thêm mới hoặc cập nhật bài đánh giá (mỗi khách 1 bài / sản phẩm). */
    public function saveReview(int $productId, int $customerId, int $rating, string $title, string $comment): void
    {
        $existing = $this->mine($productId, $customerId);

        if ($existing) {
            $this->db->table('reviews')
                ->where('ReviewID', (int) $existing['ReviewID'])
                ->update([
                    'Rating'    => $rating,
                    'Title'     => $title !== '' ? $title : null,
                    'Comment'   => $comment,
                    'UpdatedAt' => date('Y-m-d H:i:s'),
                ]);

            return;
        }

        $this->db->table('reviews')->insert([
            'ProductID'  => $productId,
            'CustomerID' => $customerId,
            'Rating'     => $rating,
            'Title'      => $title !== '' ? $title : null,
            'Comment'    => $comment,
            'IsVisible'  => 1,
            'CreatedAt'  => date('Y-m-d H:i:s'),
        ]);
    }
}
