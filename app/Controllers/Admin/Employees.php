<?php

namespace App\Controllers\Admin;

class Employees extends BaseAdmin
{
    public function index(): string
    {
        return view('admin/employees/index', [
            'pageTitle' => 'Nhân viên',
            'employees' => $this->db->table('employees')
                ->select('EmployeeID, LastName, FirstName, Email, Role, Notes')
                ->orderBy('EmployeeID')->get()->getResultArray(),
            'meId'      => $this->staffId(),
        ]);
    }

    public function create()
    {
        $rules = [
            'last_name'  => 'required|max_length[50]',
            'first_name' => 'required|max_length[50]',
            'email'      => 'required|valid_email|max_length[255]|is_unique[employees.Email]',
            'password'   => 'required|min_length[8]',
            'role'       => 'required|in_list[staff,admin]',
        ];
        $messages = [
            'last_name'  => ['required' => 'Vui lòng nhập họ.'],
            'first_name' => ['required' => 'Vui lòng nhập tên.'],
            'email'      => ['required' => 'Vui lòng nhập email.', 'valid_email' => 'Email không hợp lệ.', 'is_unique' => 'Email đã được sử dụng.'],
            'password'   => ['required' => 'Vui lòng nhập mật khẩu.', 'min_length' => 'Mật khẩu tối thiểu 8 ký tự.'],
            'role'       => ['in_list' => 'Vai trò không hợp lệ.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('form_errors', $this->validationErrors());
        }

        $this->db->table('employees')->insert([
            'LastName'     => trim((string) $this->request->getPost('last_name')),
            'FirstName'    => trim((string) $this->request->getPost('first_name')),
            'Email'        => trim((string) $this->request->getPost('email')),
            'PasswordHash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'Role'         => $this->request->getPost('role'),
        ]);

        return redirect()->to('/admin/employees')->with('success', 'Đã thêm nhân viên.');
    }

    public function role(int $id)
    {
        $role = (string) $this->request->getPost('role');

        if ($id === $this->staffId()) {
            return redirect()->back()->with('danger', 'Không thể đổi vai trò của chính mình.');
        }
        if (! in_array($role, ['staff', 'admin'], true)) {
            return redirect()->back()->with('danger', 'Vai trò không hợp lệ.');
        }

        $this->db->table('employees')->where('EmployeeID', $id)->update(['Role' => $role]);

        return redirect()->back()->with('success', 'Đã cập nhật vai trò.');
    }

    public function delete(int $id)
    {
        if ($id === $this->staffId()) {
            return redirect()->back()->with('danger', 'Không thể xóa chính mình.');
        }
        if ($this->db->table('orders')->where('EmployeeID', $id)->countAllResults() > 0) {
            return redirect()->back()->with('danger', 'Nhân viên đã xử lý đơn hàng nên không thể xóa.');
        }

        $this->db->table('employees')->where('EmployeeID', $id)->delete();

        return redirect()->back()->with('success', 'Đã xóa nhân viên.');
    }
}
