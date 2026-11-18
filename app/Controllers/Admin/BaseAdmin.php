<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class BaseAdmin extends BaseController
{
    protected function staffId(): int
    {
        return (int) session()->get('staff_id');
    }

    protected function isAdmin(): bool
    {
        return session()->get('staff_role') === 'admin';
    }
}
