<?php

namespace App\Controllers\Admin;

class Dashboard extends BaseAdmin
{
    public function index(): string
    {
        $db = $this->db;

        $counts = ['Pending' => 0, 'Confirmed' => 0, 'Shipping' => 0, 'Completed' => 0, 'Cancelled' => 0];
        foreach ($db->query('SELECT Status, COUNT(*) AS total FROM orders GROUP BY Status')->getResultArray() as $r) {
            $counts[$r['Status']] = (int) $r['total'];
        }

        $data = [
            'pageTitle'    => 'Tổng quan',
            'statusCounts' => $counts,
            'newReviews'   => (int) $db->table('reviews')->where('CreatedAt >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            'isAdmin'      => $this->isAdmin(),
        ];

        if ($this->isAdmin()) {
            $rev = $db->query("
                SELECT
                  COALESCE(SUM(CASE WHEN DATE(OrderDate) = CURDATE() THEN TotalAmount END), 0) AS today,
                  COALESCE(SUM(CASE WHEN YEAR(OrderDate) = YEAR(CURDATE()) AND MONTH(OrderDate) = MONTH(CURDATE()) THEN TotalAmount END), 0) AS month,
                  COALESCE(SUM(CASE WHEN YEAR(OrderDate) = YEAR(CURDATE()) THEN TotalAmount END), 0) AS year
                FROM orders WHERE Status = 'Completed'")->getRowArray();

            $monthly = [];
            foreach ($db->query("
                SELECT MONTH(OrderDate) AS m, COUNT(*) AS orders_count, SUM(TotalAmount) AS revenue
                  FROM orders
                 WHERE Status = 'Completed' AND YEAR(OrderDate) = YEAR(CURDATE())
                 GROUP BY MONTH(OrderDate) ORDER BY m")->getResultArray() as $r) {
                $monthly[(int) $r['m']] = $r;
            }

            $data += [
                'revenue'       => $rev,
                'monthly'       => $monthly,
                'bestSellers'   => $db->query("
                    SELECT p.ProductCode, p.ProductName, SUM(od.Quantity) AS sold
                      FROM orderdetail od
                      JOIN orders o ON o.OrderID = od.OrderID
                      JOIN products p ON p.ProductID = od.ProductID
                     WHERE o.Status <> 'Cancelled'
                     GROUP BY p.ProductID, p.ProductCode, p.ProductName
                     ORDER BY sold DESC LIMIT 5")->getResultArray(),
                // tồn kho thấp tính theo từng size/màu
                'lowStock'      => $db->query("
                    SELECT p.ProductCode, p.ProductName, v.Size, v.Color, v.StockQuantity
                      FROM product_variants v
                      JOIN products p ON p.ProductID = v.ProductID
                     WHERE p.IsActive = 1 AND v.StockQuantity <= 3
                     ORDER BY v.StockQuantity ASC, p.ProductID LIMIT 10")->getResultArray(),
                'customerCount' => (int) $db->table('customers')->where('Email IS NOT NULL', null, false)->countAllResults(),
            ];
        }

        return view('admin/dashboard', $data);
    }
}
