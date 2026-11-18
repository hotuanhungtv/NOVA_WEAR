<?php

namespace App\Controllers;

use App\Libraries\CartService;

class Cart extends BaseController
{
    public function index(): string
    {
        $cart = new CartService();

        return view('pages/cart', [
            'pageTitle' => 'Giỏ hàng',
            'lines'     => $cart->lines(),
            'total'     => $cart->total(),
            'problem'   => $cart->hasProblem(),
        ]);
    }

    /** Thêm 1 tổ hợp size + màu vào giỏ (variant_id lấy từ trang chi tiết). */
    public function add()
    {
        $variantId = (int) $this->request->getPost('variant_id');
        $qty       = (int) $this->request->getPost('quantity');

        if ($variantId <= 0) {
            return redirect()->back()->with('warning', 'Vui lòng chọn size và màu trước khi thêm vào giỏ.');
        }

        [$ok, $message] = (new CartService())->add($variantId, $qty);

        if (! $ok) {
            return redirect()->back()->with('danger', $message);
        }

        if ($this->request->getPost('buy_now')) {
            return redirect()->to('/checkout');
        }

        return redirect()->back()->with('success', $message)->with('cart_link', true);
    }

    public function update()
    {
        $cart    = new CartService();
        $limited = false;

        foreach ((array) $this->request->getPost('quantities') as $variantId => $qty) {
            if ($cart->setQuantity((int) $variantId, (int) $qty)) {
                $limited = true;
            }
        }

        $redirect = redirect()->to('/cart');

        return $limited
            ? $redirect->with('warning', 'Một số sản phẩm vượt quá tồn kho nên đã được hạ về số lượng còn lại.')
            : $redirect->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove()
    {
        (new CartService())->remove((int) $this->request->getPost('variant_id'));

        return redirect()->to('/cart')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }
}
