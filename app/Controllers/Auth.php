<?php

namespace App\Controllers;

/** Đăng nhập / đăng ký / đăng xuất của khách hàng. */
class Auth extends BaseController
{
    public function login()
    {
        if ($this->customerId()) {
            return redirect()->to('/');
        }

        return view('account/login', ['pageTitle' => 'Đăng nhập']);
    }

    public function attempt()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return redirect()->back()->withInput()->with('danger', 'Vui lòng nhập email và mật khẩu.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('danger', 'Email không hợp lệ.');
        }

        $customer = $this->db->table('customers')
            ->select('CustomerID, CustomerName, PasswordHash, IsActive')
            ->where('Email', $email)
            ->get()->getRowArray();

        if ($customer && ! (int) $customer['IsActive']) {
            return redirect()->back()->withInput()->with('danger', 'Tài khoản đã bị khóa. Vui lòng liên hệ cửa hàng.');
        }

        if (! $customer || empty($customer['PasswordHash']) || ! password_verify($password, $customer['PasswordHash'])) {
            return redirect()->back()->withInput()->with('danger', 'Email hoặc mật khẩu không đúng.');
        }

        $next = safe_local_path(session()->get('after_login'), '/');

        session()->regenerate();
        session()->remove('after_login');
        session()->set([
            'customer_id'   => (int) $customer['CustomerID'],
            'customer_name' => $customer['CustomerName'],
        ]);

        return redirect()->to($next);
    }

    public function register()
    {
        if ($this->customerId()) {
            return redirect()->to('/');
        }

        return view('account/register', ['pageTitle' => 'Đăng ký tài khoản']);
    }

    public function store()
    {
        $rules = [
            'customer_name'    => 'required|max_length[100]',
            'phone'            => 'permit_empty|regex_match[/^[0-9+\s.\-]{8,20}$/]',
            'email'            => 'required|valid_email|max_length[255]|is_unique[customers.Email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        $messages = [
            'customer_name'    => ['required' => 'Vui lòng nhập họ và tên.'],
            'phone'            => ['regex_match' => 'Số điện thoại không hợp lệ.'],
            'email'            => [
                'required'    => 'Vui lòng nhập email.',
                'valid_email' => 'Email không hợp lệ.',
                'is_unique'   => 'Email này đã được sử dụng.',
            ],
            'password'         => ['required' => 'Vui lòng nhập mật khẩu.', 'min_length' => 'Mật khẩu phải có ít nhất 8 ký tự.'],
            'confirm_password' => ['required' => 'Vui lòng nhập lại mật khẩu.', 'matches' => 'Mật khẩu xác nhận không khớp.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('form_errors', $this->validationErrors());
        }

        $name = trim((string) $this->request->getPost('customer_name'));

        $this->db->table('customers')->insert([
            'CustomerName' => $name,
            'ContactName'  => $name,
            'Phone'        => trim((string) $this->request->getPost('phone')) ?: null,
            'Email'        => trim((string) $this->request->getPost('email')),
            'PasswordHash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'IsActive'     => 1,
        ]);

        return redirect()->to('/login')->with('success', 'Đăng ký thành công. Bạn có thể đăng nhập.');
    }

    /** Chỉ xóa thông tin đăng nhập, giữ lại giỏ hàng trong session. */
    public function logout()
    {
        session()->remove(['customer_id', 'customer_name', 'after_login']);
        session()->regenerate();

        return redirect()->to('/');
    }
}
