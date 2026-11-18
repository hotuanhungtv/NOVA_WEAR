<?php

namespace App\Controllers\Admin;

use App\Controllers\Orders as CustomerOrders;
use DomainException;
use Throwable;

class Orders extends BaseAdmin
{
    private const TRANSITIONS = [
        'Pending'   => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Shipping', 'Cancelled'],
        'Shipping'  => ['Completed', 'Cancelled'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    public function index(): string
    {
        $status  = (string) $this->request->getGet('status');
        $keyword = trim((string) $this->request->getGet('keyword'));
        $date    = (string) $this->request->getGet('date');

        $b = $this->db->table('orders o')
            ->select('o.OrderID, o.OrderDate, o.TotalAmount, o.Status, c.CustomerName, c.Phone')
            ->join('customers c', 'c.CustomerID = o.CustomerID');

        if (isset(self::TRANSITIONS[$status])) {
            $b->where('o.Status', $status);
        } else {
            $status = '';
        }

        if ($keyword !== '') {
            $b->groupStart()->like('c.CustomerName', $keyword)->orLike('c.Phone', $keyword)->groupEnd();
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $b->where('DATE(o.OrderDate)', $date);
        } else {
            $date = '';
        }

        return view('admin/orders/index', [
            'pageTitle' => 'Quản lý đơn hàng',
            'orders'    => $b->orderBy('o.OrderID', 'DESC')->get()->getResultArray(),
            'status'    => $status,
            'keyword'   => $keyword,
            'date'      => $date,
        ]);
    }

    public function show(int $id)
    {
        $order = $this->db->table('orders o')
            ->select('o.*, c.CustomerName, c.Phone, c.Address, c.City, e.LastName, e.FirstName')
            ->join('customers c', 'c.CustomerID = o.CustomerID')
            ->join('employees e', 'e.EmployeeID = o.EmployeeID', 'left')
            ->where('o.OrderID', $id)
            ->get()->getRowArray();

        if (! $order) {
            return redirect()->to('/admin/orders')->with('danger', 'Không tìm thấy đơn hàng.');
        }

        $items = $this->db->table('orderdetail od')
            ->select('od.Quantity, od.UnitPrice, od.Size, od.Color, p.ProductCode, p.ProductName')
            ->join('products p', 'p.ProductID = od.ProductID')
            ->where('od.OrderID', $id)
            ->orderBy('od.OrderDetailID')
            ->get()->getResultArray();

        return view('admin/orders/show', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'order'     => $order,
            'items'     => $items,
            'shippers'  => $this->db->table('shippers')->orderBy('ShipperName')->get()->getResultArray(),
            'next'      => self::TRANSITIONS[$order['Status']] ?? [],
        ]);
    }

    public function updateStatus(int $id)
    {
        $newStatus = (string) $this->request->getPost('status');
        $shipperId = (int) $this->request->getPost('shipper_id');
        $db        = $this->db;

        $db->transBegin();

        try {
            $order = $db->query('SELECT Status, ShipperID FROM orders WHERE OrderID = ? FOR UPDATE', [$id])->getRowArray();

            if (! $order) {
                throw new DomainException('Không tìm thấy đơn hàng.');
            }

            if (! in_array($newStatus, self::TRANSITIONS[$order['Status']] ?? [], true)) {
                throw new DomainException('Không thể chuyển sang trạng thái đã chọn.');
            }

            if ($shipperId <= 0 && $order['ShipperID'] !== null) {
                $shipperId = (int) $order['ShipperID'];
            }

            if ($newStatus === 'Shipping' && $shipperId <= 0) {
                throw new DomainException('Vui lòng chọn đơn vị vận chuyển trước khi chuyển sang Đang giao.');
            }

            if ($shipperId > 0 && ! $db->table('shippers')->where('ShipperID', $shipperId)->countAllResults()) {
                throw new DomainException('Đơn vị vận chuyển không tồn tại.');
            }

            if ($newStatus === 'Cancelled') {
                CustomerOrders::restoreStock($db, $id);   // hoàn tồn kho theo size/màu
            }

            $db->table('orders')->where('OrderID', $id)->update([
                'Status'     => $newStatus,
                'EmployeeID' => $this->staffId(),
                'ShipperID'  => $shipperId > 0 ? $shipperId : null,
            ]);

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()->to('/admin/orders/' . $id)->with(
                'danger',
                $e instanceof DomainException ? $e->getMessage() : 'Không thể cập nhật đơn hàng.'
            );
        }

        return redirect()->to('/admin/orders/' . $id)->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }
}
