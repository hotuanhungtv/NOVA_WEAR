<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Bắt buộc đăng nhập tài khoản khách hàng. */
class CustomerAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('customer_id')) {
            return null;
        }

        // Nhớ trang đang xem (chỉ với GET) để quay lại sau khi đăng nhập
        if (strtolower($request->getMethod()) === 'get') {
            session()->set('after_login', '/' . ltrim($request->getUri()->getPath(), '/'));
        }

        return redirect()->to('/login')->with('info', 'Vui lòng đăng nhập để tiếp tục.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
