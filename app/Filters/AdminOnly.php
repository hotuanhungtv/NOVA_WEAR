<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Chỉ quản trị viên (role = admin). Dùng SAU filter "staff". */
class AdminOnly implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('staff_role') === 'admin') {
            return null;
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody(
                '<!doctype html><meta charset="utf-8"><title>403</title>'
                . '<p style="font-family:sans-serif;padding:2rem">403 - Bạn không có quyền truy cập chức năng này. '
                . '<a href="' . site_url('admin') . '">Quay lại</a></p>'
            );
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
