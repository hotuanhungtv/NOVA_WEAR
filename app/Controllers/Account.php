<?php

namespace App\Controllers;

/** Hồ sơ cá nhân + đổi mật khẩu. */
class Account extends BaseController
{
    private function me(): ?array
    {
        return $this->db->table('customers')
            ->select('CustomerName, Email, Phone, Address, PasswordHash')
            ->where('CustomerID', $this->customerId())
            ->get()->getRowArray();
    }

    public function profile()
    {
        $customer = $this->me();

        if (! $customer) {
            return redirect()->to('/logout');
        }

        unset($customer['PasswordHash']);

        return view('account/profile', ['pageTitle' => 'Thông tin cá nhân', 'customer' => $customer]);
    }

    public function updateProfile()
    {
        $rules = [
            'customer_name' => 'required|max_length[100]',
            'phone'         => 'permit_empty|regex_match[/^[0-9+\s.\-]{8,20}$/]',
            'address'       => 'permit_empty|max_length[200]',
        ];
        $messages = [
            'customer_name' => ['required' => 'Họ và tên không được để trống.'],
            'phone'         => ['regex_match' => 'Số điện thoại không hợp lệ.'],
            'address'       => ['max_length' => 'Địa chỉ tối đa 200 ký tự.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/profile')->withInput()->with('form_errors', $this->validationErrors());
        }

        $name = trim((string) $this->request->getPost('customer_name'));

        $this->db->table('customers')->where('CustomerID', $this->customerId())->update([
            'CustomerName' => $name,
            'Phone'        => trim((string) $this->request->getPost('phone')),
            'Address'      => trim((string) $this->request->getPost('address')),
        ]);

        session()->set('customer_name', $name);

        return redirect()->to('/profile')->with('success', 'Đã cập nhật thông tin cá nhân.');
    }

    public function changePassword()
    {
        $customer = $this->me();
        $current  = (string) $this->request->getPost('current_password');
        $new      = (string) $this->request->getPost('new_password');
        $confirm  = (string) $this->request->getPost('confirm_password');

        $error = null;

        if (! $customer || ! password_verify($current, (string) ($customer['PasswordHash'] ?? ''))) {
            $error = 'Mật khẩu hiện tại không đúng.';
        } elseif (strlen($new) < 8) {
            $error = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        } elseif ($new !== $confirm) {
            $error = 'Xác nhận mật khẩu mới không khớp.';
        }

        if ($error !== null) {
            return redirect()->to('/profile')->with('form_errors', [$error]);
        }

        $this->db->table('customers')->where('CustomerID', $this->customerId())
            ->update(['PasswordHash' => password_hash($new, PASSWORD_DEFAULT)]);

        return redirect()->to('/profile')->with('success', 'Đã đổi mật khẩu.');
    }
}
