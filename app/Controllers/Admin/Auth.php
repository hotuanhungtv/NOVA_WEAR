<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('staff_id')) {
            return redirect()->to('/admin');
        }

        return view('admin/login', ['pageTitle' => 'Đăng nhập quản trị']);
    }

    public function attempt()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $employee = $email === '' ? null : $this->db->table('employees')
            ->select('EmployeeID, LastName, FirstName, PasswordHash, Role')
            ->where('Email', $email)
            ->get()->getRowArray();

        if (! $employee || empty($employee['PasswordHash']) || ! password_verify($password, $employee['PasswordHash'])) {
            return redirect()->back()->withInput()->with('danger', 'Email hoặc mật khẩu không đúng.');
        }

        session()->regenerate();
        session()->set([
            'staff_id'   => (int) $employee['EmployeeID'],
            'staff_name' => trim($employee['LastName'] . ' ' . $employee['FirstName']),
            'staff_role' => $employee['Role'],
        ]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->remove(['staff_id', 'staff_name', 'staff_role']);
        session()->regenerate();

        return redirect()->to('/admin/login');
    }
}
