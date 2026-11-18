<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Home extends BaseController
{
    public function index(): string
    {
        $products = new ProductModel();

        return view('pages/home', [
            'pageTitle'  => 'Streetwear',
            'categories' => $products->categoriesWithCover(),
            'latest'     => $products->latest(12),
        ]);
    }
}
