<?php

namespace App\Controllers;

use Throwable;
use DomainException;

/** Đơn hàng của khách đang đăng nhập. */
class Orders extends BaseController
{
    public function index(): string
    {
        $orders = $this->db->table('orders')
            ->select('OrderID, OrderDate, TotalAmount, Status')
            ->where('CustomerID', $this->customerId())
            ->orderBy('OrderID', 'DESC')
            ->get()->getResultArray();

        return view('account/orders', ['pageTitle' => 'Đơn hàng của tôi', 'orders' => $orders]);
    }

    public function show(int $orderId)
    {
        $order = $this->db->table('orders')
            ->select('OrderID, OrderDate, TotalAmount, Status')
            ->where('OrderID', $orderId)
            ->where('CustomerID', $this->customerId())
            ->get()->getRowArray();

        if (! $order) {
            return redirect()->to('/orders')->with('danger', 'Không tìm thấy đơn hàng.');
        }

        $items = $this->db->table('orderdetail od')
            ->select('od.Quantity, od.UnitPrice, od.Size, od.Color, p.ProductID, p.ProductCode, p.ProductName')
            ->select('(SELECT r.ReviewID FROM reviews r WHERE r.ProductID = od.ProductID AND r.CustomerID = ' . (int) $this->customerId() . ' LIMIT 1) AS ReviewID', false)
            ->join('products p', 'p.ProductID = od.ProductID')
            ->where('od.OrderID', $orderId)
            ->orderBy('od.OrderDetailID')
            ->get()->getResultArray();

        return view('account/order_view', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'order'      => $order,
            'items'      => $items,
        ]);
    }

    /** Chỉ hủy được đơn "Chờ xác nhận"; hoàn lại tồn kho theo từng size/màu. */
    public function cancel(int $orderId)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $order = $db->query(
                'SELECT Status FROM orders WHERE OrderID = ? AND CustomerID = ? FOR UPDATE',
                [$orderId, $this->customerId()]
            )->getRowArray();

            if (! $order) {
                throw new DomainException('Không tìm thấy đơn hàng.');
            }

            if ($order['Status'] !== 'Pending') {
                throw new DomainException('Chỉ hủy được đơn đang ở trạng thái "Chờ xác nhận".');
            }

            self::restoreStock($db, $orderId);

            $db->table('orders')->where('OrderID', $orderId)->update(['Status' => 'Cancelled']);
            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()->to('/orders/' . $orderId)->with(
                'danger',
                $e instanceof DomainException ? $e->getMessage() : 'Không thể hủy đơn lúc này.'
            );
        }

        return redirect()->to('/orders/' . $orderId)->with('success', 'Đã hủy đơn hàng #' . $orderId . '.');
    }

    /** Cộng lại tồn kho của các biến thể trong đơn (dùng khi hủy đơn, cả phía admin). */
    public static function restoreStock($db, int $orderId): void
    {
        $lines = $db->table('orderdetail')
            ->select('VariantID, Quantity')
            ->where('OrderID', $orderId)
            ->get()->getResultArray();

        foreach ($lines as $line) {
            if (empty($line['VariantID'])) {
                continue; // đơn cũ không có size/màu
            }

            $db->query(
                'UPDATE product_variants SET StockQuantity = StockQuantity + ? WHERE VariantID = ?',
                [(int) $line['Quantity'], (int) $line['VariantID']]
            );
        }
    }
}
