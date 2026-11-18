<?php

namespace App\Controllers\Admin;

class Customers extends BaseAdmin
{
    public function index(): string
    {
        $keyword = trim((string) $this->request->getGet('keyword'));

        $b = $this->db->table('customers c')
            ->select('c.CustomerID, c.CustomerName, c.Email, c.Phone, c.IsActive')
            ->select('(SELECT COUNT(*) FROM orders o WHERE o.CustomerID = c.CustomerID) AS OrderCount', false)
            ->select('(SELECT COUNT(*) FROM reviews r WHERE r.CustomerID = c.CustomerID) AS ReviewCount', false);

        if ($keyword !== '') {
            $b->groupStart()->like('c.CustomerName', $keyword)->orLike('c.Email', $keyword)->orLike('c.Phone', $keyword)->groupEnd();
        }

        return view('admin/customers/index', [
            'pageTitle' => 'Khách hàng',
            'customers' => $b->orderBy('c.CustomerID', 'DESC')->get()->getResultArray(),
            'keyword'   => $keyword,
        ]);
    }

    public function show(int $id)
    {
        $customer = $this->db->table('customers')->where('CustomerID', $id)->get()->getRowArray();

        if (! $customer) {
            return redirect()->to('/admin/customers')->with('danger', 'Không tìm thấy khách hàng.');
        }

        unset($customer['PasswordHash']);

        return view('admin/customers/show', [
            'pageTitle' => 'Chi tiết khách hàng',
            'customer'  => $customer,
            'orders'    => $this->db->table('orders')->where('CustomerID', $id)->orderBy('OrderID', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function toggle(int $id)
    {
        $this->db->query('UPDATE customers SET IsActive = NOT IsActive WHERE CustomerID = ? AND Email IS NOT NULL', [$id]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái tài khoản.');
    }
}
