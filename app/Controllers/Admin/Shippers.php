<?php

namespace App\Controllers\Admin;

class Shippers extends BaseAdmin
{
    public function index(): string
    {
        $rows = $this->db->query('
            SELECT s.ShipperID, s.ShipperName, s.Phone, COUNT(o.OrderID) AS total
              FROM shippers s LEFT JOIN orders o ON o.ShipperID = s.ShipperID
             GROUP BY s.ShipperID, s.ShipperName, s.Phone ORDER BY s.ShipperID')->getResultArray();

        return view('admin/shippers/index', ['pageTitle' => 'Đơn vị vận chuyển', 'shippers' => $rows]);
    }

    public function create(): string
    {
        return view('admin/shippers/form', ['pageTitle' => 'Thêm đơn vị vận chuyển', 'shipper' => null]);
    }

    private function input(): ?array
    {
        $name  = trim((string) $this->request->getPost('shipper_name'));
        $phone = trim((string) $this->request->getPost('phone'));

        if ($name === '' || mb_strlen($name) > 100 || ($phone !== '' && ! preg_match('/^[0-9+\s.\-]{6,20}$/', $phone))) {
            return null;
        }

        return ['ShipperName' => $name, 'Phone' => $phone];
    }

    public function store()
    {
        $data = $this->input();

        if (! $data) {
            return redirect()->back()->withInput()->with('form_errors', ['Tên bắt buộc; số điện thoại (nếu có) phải hợp lệ.']);
        }

        $this->db->table('shippers')->insert($data);

        return redirect()->to('/admin/shippers')->with('success', 'Đã thêm đơn vị vận chuyển.');
    }

    public function edit(int $id)
    {
        $row = $this->db->table('shippers')->where('ShipperID', $id)->get()->getRowArray();

        if (! $row) {
            return redirect()->to('/admin/shippers')->with('danger', 'Không tìm thấy đơn vị vận chuyển.');
        }

        return view('admin/shippers/form', ['pageTitle' => 'Sửa đơn vị vận chuyển', 'shipper' => $row]);
    }

    public function update(int $id)
    {
        $data = $this->input();

        if (! $data) {
            return redirect()->back()->withInput()->with('form_errors', ['Tên bắt buộc; số điện thoại (nếu có) phải hợp lệ.']);
        }

        $this->db->table('shippers')->where('ShipperID', $id)->update($data);

        return redirect()->to('/admin/shippers')->with('success', 'Đã cập nhật.');
    }

    public function delete(int $id)
    {
        if ($this->db->table('orders')->where('ShipperID', $id)->countAllResults() > 0) {
            return redirect()->to('/admin/shippers')->with('danger', 'Đơn vị này đã giao đơn hàng nên không thể xóa.');
        }

        $this->db->table('shippers')->where('ShipperID', $id)->delete();

        return redirect()->to('/admin/shippers')->with('success', 'Đã xóa.');
    }
}
