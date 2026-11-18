<?php

namespace App\Controllers;

use App\Libraries\CartService;
use CodeIgniter\Exceptions\PageNotFoundException;
use DomainException;
use Throwable;

class Checkout extends BaseController
{
    private function customer(): ?array
    {
        return $this->db->table('customers')
            ->select('CustomerID, CustomerName, Email, Phone, Address')
            ->where('CustomerID', $this->customerId())
            ->where('Email IS NOT NULL', null, false)
            ->get()->getRowArray();
    }

    public function index()
    {
        $cart = new CartService();

        if ($cart->count() === 0) {
            return redirect()->to('/cart');
        }

        if ($cart->hasProblem()) {
            return redirect()->to('/cart')->with('warning', 'Có sản phẩm đã hết hàng. Vui lòng xóa khỏi giỏ trước khi đặt hàng.');
        }

        $customer = $this->customer();

        if (! $customer) {
            session()->remove(['customer_id', 'customer_name']);

            return redirect()->to('/login');
        }

        return view('pages/checkout', [
            'pageTitle' => 'Đặt hàng',
            'customer'  => $customer,
            'lines'     => $cart->lines(),
            'total'     => $cart->total(),
        ]);
    }

    /** Tạo đơn hàng: khóa dòng tồn kho, kiểm tra lại, trừ kho — tất cả trong 1 transaction. */
    public function place()
    {
        $cart     = new CartService();
        $cartRaw  = $cart->raw();
        $customer = $this->customer();

        if ($cartRaw === []) {
            return redirect()->to('/cart');
        }

        if (! $customer) {
            return redirect()->to('/login');
        }

        $rules = [
            'phone'   => 'required|regex_match[/^[0-9+\s.\-]{8,20}$/]',
            'address' => 'required|min_length[8]|max_length[200]',
        ];
        $messages = [
            'phone'   => ['required' => 'Vui lòng nhập số điện thoại.', 'regex_match' => 'Số điện thoại không hợp lệ.'],
            'address' => ['required' => 'Vui lòng nhập địa chỉ giao hàng.', 'min_length' => 'Địa chỉ quá ngắn, vui lòng ghi rõ số nhà, đường, quận/huyện.', 'max_length' => 'Địa chỉ tối đa 200 ký tự.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('form_errors', $this->validationErrors());
        }

        $phone   = trim((string) $this->request->getPost('phone'));
        $address = trim((string) $this->request->getPost('address'));

        $db = $this->db;
        $db->transBegin();

        try {
            $ids  = array_keys($cartRaw);
            $rows = $db->query(
                'SELECT v.VariantID, v.ProductID, v.Size, v.Color, v.StockQuantity,
                        p.ProductName, p.Price, p.IsActive
                   FROM product_variants v
                   JOIN products p ON p.ProductID = v.ProductID
                  WHERE v.VariantID IN ?
                    FOR UPDATE',
                [$ids]
            )->getResultArray();

            $byId = [];
            foreach ($rows as $row) {
                $byId[(int) $row['VariantID']] = $row;
            }

            $items = [];
            $total = 0.0;

            foreach ($cartRaw as $variantId => $qty) {
                $row = $byId[$variantId] ?? null;

                if (! $row || ! (int) $row['IsActive']) {
                    throw new DomainException('Có sản phẩm không còn được bán. Vui lòng kiểm tra lại giỏ hàng.');
                }

                if ($qty > (int) $row['StockQuantity']) {
                    throw new DomainException(
                        'Sản phẩm "' . $row['ProductName'] . '" (size ' . $row['Size'] . ', màu ' . $row['Color']
                        . ') chỉ còn ' . (int) $row['StockQuantity'] . ' sản phẩm.'
                    );
                }

                $price   = (float) $row['Price'];
                $total  += $price * $qty;
                $items[] = [
                    'VariantID' => (int) $variantId,
                    'ProductID' => (int) $row['ProductID'],
                    'Size'      => $row['Size'],
                    'Color'     => $row['Color'],
                    'Quantity'  => $qty,
                    'UnitPrice' => $price,
                ];
            }

            $db->table('customers')
                ->where('CustomerID', (int) $customer['CustomerID'])
                ->update(['Phone' => $phone, 'Address' => $address]);

            $db->table('orders')->insert([
                'OrderDate'   => date('Y-m-d H:i:s'),
                'TotalAmount' => $total,
                'Status'      => 'Pending',
                'CustomerID'  => (int) $customer['CustomerID'],
            ]);
            $orderId = (int) $db->insertID();

            foreach ($items as $item) {
                $db->table('orderdetail')->insert([
                    'OrderID'   => $orderId,
                    'ProductID' => $item['ProductID'],
                    'VariantID' => $item['VariantID'],
                    'Size'      => $item['Size'],
                    'Color'     => $item['Color'],
                    'Quantity'  => $item['Quantity'],
                    'UnitPrice' => $item['UnitPrice'],
                ]);

                $db->query(
                    'UPDATE product_variants SET StockQuantity = StockQuantity - ?
                      WHERE VariantID = ? AND StockQuantity >= ?',
                    [$item['Quantity'], $item['VariantID'], $item['Quantity']]
                );

                if ($db->affectedRows() !== 1) {
                    throw new DomainException('Không đủ tồn kho để hoàn tất đơn hàng. Vui lòng thử lại.');
                }
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            if (! $e instanceof DomainException) {
                log_message('error', 'Lỗi đặt hàng: ' . $e->getMessage());
            }

            $message = $e instanceof DomainException
                ? $e->getMessage()
                : 'Không thể tạo đơn hàng lúc này. Vui lòng thử lại sau.';

            return redirect()->back()->withInput()->with('danger', $message);
        }

        $cart->clear();

        return redirect()->to('/order-success/' . $orderId);
    }

    public function success(int $orderId): string
    {
        $order = $this->db->table('orders o')
            ->select('o.OrderID, o.OrderDate, o.TotalAmount, o.Status, c.CustomerName, c.Phone, c.Address')
            ->join('customers c', 'c.CustomerID = o.CustomerID')
            ->where('o.OrderID', $orderId)
            ->where('o.CustomerID', $this->customerId())   // chỉ xem được đơn của chính mình
            ->get()->getRowArray();

        if (! $order) {
            throw PageNotFoundException::forPageNotFound('Không tìm thấy đơn hàng.');
        }

        $items = $this->db->table('orderdetail od')
            ->select('od.Quantity, od.UnitPrice, od.Size, od.Color, p.ProductCode, p.ProductName')
            ->join('products p', 'p.ProductID = od.ProductID')
            ->where('od.OrderID', $orderId)
            ->orderBy('od.OrderDetailID')
            ->get()->getResultArray();

        return view('pages/order_success', [
            'pageTitle' => 'Đặt hàng thành công',
            'order'     => $order,
            'items'     => $items,
        ]);
    }
}
