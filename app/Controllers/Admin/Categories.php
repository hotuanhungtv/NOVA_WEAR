<?php

namespace App\Controllers\Admin;

class Categories extends BaseAdmin
{
    public function index(): string
    {
        $rows = $this->db->query('
            SELECT c.CategoryID, c.CategoryName, c.Description, COUNT(p.ProductID) AS total
              FROM categories c LEFT JOIN products p ON p.CategoryID = c.CategoryID
             GROUP BY c.CategoryID, c.CategoryName, c.Description ORDER BY c.CategoryID')->getResultArray();

        return view('admin/categories/index', ['pageTitle' => 'Danh mục', 'categories' => $rows]);
    }

    public function create(): string
    {
        return view('admin/categories/form', ['pageTitle' => 'Thêm danh mục', 'category' => null]);
    }

    private function input(): ?array
    {
        $name = trim((string) $this->request->getPost('category_name'));

        if ($name === '' || mb_strlen($name) > 200) {
            return null;
        }

        return ['CategoryName' => $name, 'Description' => trim((string) $this->request->getPost('description'))];
    }

    public function store()
    {
        $data = $this->input();

        if (! $data) {
            return redirect()->back()->withInput()->with('form_errors', ['Tên danh mục bắt buộc (tối đa 200 ký tự).']);
        }

        $this->db->table('categories')->insert($data);

        return redirect()->to('/admin/categories')->with('success', 'Đã thêm danh mục.');
    }

    public function edit(int $id)
    {
        $row = $this->db->table('categories')->where('CategoryID', $id)->get()->getRowArray();

        if (! $row) {
            return redirect()->to('/admin/categories')->with('danger', 'Không tìm thấy danh mục.');
        }

        return view('admin/categories/form', ['pageTitle' => 'Sửa danh mục', 'category' => $row]);
    }

    public function update(int $id)
    {
        $data = $this->input();

        if (! $data) {
            return redirect()->back()->withInput()->with('form_errors', ['Tên danh mục bắt buộc (tối đa 200 ký tự).']);
        }

        $this->db->table('categories')->where('CategoryID', $id)->update($data);

        return redirect()->to('/admin/categories')->with('success', 'Đã cập nhật danh mục.');
    }

    public function delete(int $id)
    {
        if ($this->db->table('products')->where('CategoryID', $id)->countAllResults() > 0) {
            return redirect()->to('/admin/categories')->with('danger', 'Danh mục còn sản phẩm nên không thể xóa.');
        }

        $this->db->table('categories')->where('CategoryID', $id)->delete();

        return redirect()->to('/admin/categories')->with('success', 'Đã xóa danh mục.');
    }
}
