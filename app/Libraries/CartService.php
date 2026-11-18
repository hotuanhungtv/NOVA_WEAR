<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Giỏ hàng lưu trong session: [VariantID => số lượng].
 * Mỗi dòng là một tổ hợp SIZE + MÀU của một sản phẩm.
 */
class CartService
{
    private const KEY = 'cart';

    private BaseConnection $db;

    /** @var list<array<string,mixed>>|null */
    private ?array $linesCache = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /** @return array<int,int> */
    public function raw(): array
    {
        $cart = session()->get(self::KEY);

        if (! is_array($cart)) {
            return [];
        }

        $clean = [];

        foreach ($cart as $variantId => $qty) {
            $variantId = (int) $variantId;
            $qty       = (int) $qty;

            if ($variantId > 0 && $qty > 0) {
                $clean[$variantId] = $qty;
            }
        }

        return $clean;
    }

    private function save(array $cart): void
    {
        session()->set(self::KEY, $cart);
        $this->linesCache = null;
    }

    public function clear(): void
    {
        $this->save([]);
    }

    /** Tổng số sản phẩm (cộng số lượng các dòng). */
    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function total(): float
    {
        $total = 0.0;

        foreach ($this->lines() as $line) {
            $total += $line['Subtotal'];
        }

        return $total;
    }

    /** Có dòng nào hết hàng / vượt tồn kho không? */
    public function hasProblem(): bool
    {
        foreach ($this->lines() as $line) {
            if ($line['OutOfStock']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Chi tiết từng dòng giỏ hàng. Dòng của sản phẩm đã bị xóa/ngừng bán sẽ tự bị loại;
     * số lượng vượt tồn kho được hạ xuống bằng tồn kho.
     *
     * @return list<array<string,mixed>>
     */
    public function lines(): array
    {
        if ($this->linesCache !== null) {
            return $this->linesCache;
        }

        $cart = $this->raw();

        if ($cart === []) {
            return $this->linesCache = [];
        }

        $rows = $this->db->table('product_variants v')
            ->select('v.VariantID, v.Size, v.Color, v.ColorHex, v.StockQuantity, v.ImageFile AS VariantImage')
            ->select('p.ProductID, p.ProductCode, p.ProductName, p.Price')
            ->select('(SELECT pi.ImageFile FROM product_images pi WHERE pi.ProductID = p.ProductID AND pi.IsPrimary = 1 LIMIT 1) AS PrimaryImage', false)
            ->join('products p', 'p.ProductID = v.ProductID')
            ->where('p.IsActive', 1)
            ->whereIn('v.VariantID', array_keys($cart))
            ->get()->getResultArray();

        $byId = [];

        foreach ($rows as $row) {
            $byId[(int) $row['VariantID']] = $row;
        }

        $lines   = [];
        $changed = false;

        foreach ($cart as $variantId => $qty) {
            if (! isset($byId[$variantId])) {
                unset($cart[$variantId]);
                $changed = true;
                continue;
            }

            $row   = $byId[$variantId];
            $stock = (int) $row['StockQuantity'];

            if ($stock > 0 && $qty > $stock) {
                $qty              = $stock;
                $cart[$variantId] = $stock;
                $changed          = true;
            }

            $lines[] = [
                'VariantID'  => $variantId,
                'ProductID'  => (int) $row['ProductID'],
                'ProductCode' => $row['ProductCode'],
                'ProductName' => $row['ProductName'],
                'Size'       => $row['Size'],
                'Color'      => $row['Color'],
                'ColorHex'   => $row['ColorHex'],
                'Price'      => (float) $row['Price'],
                'Stock'      => $stock,
                'Quantity'   => $qty,
                'Subtotal'   => (float) $row['Price'] * $qty,
                'Image'      => $row['VariantImage'] ?: $row['PrimaryImage'],
                'OutOfStock' => $stock <= 0,
            ];
        }

        if ($changed) {
            $this->save($cart);
        }

        return $this->linesCache = $lines;
    }

    /** @return array{0:bool,1:string} [thành công?, thông báo] */
    public function add(int $variantId, int $qty): array
    {
        $qty = max(1, $qty);

        $variant = $this->db->table('product_variants v')
            ->select('v.VariantID, v.Size, v.Color, v.StockQuantity')
            ->join('products p', 'p.ProductID = v.ProductID')
            ->where('v.VariantID', $variantId)
            ->where('p.IsActive', 1)
            ->get()->getRowArray();

        if (! $variant) {
            return [false, 'Phiên bản sản phẩm không còn tồn tại.'];
        }

        $stock = (int) $variant['StockQuantity'];

        if ($stock <= 0) {
            return [false, 'Size ' . $variant['Size'] . ' - màu ' . $variant['Color'] . ' hiện đã hết hàng.'];
        }

        $cart = $this->raw();
        $new  = ($cart[$variantId] ?? 0) + $qty;
        $msg  = 'Đã thêm vào giỏ hàng.';

        if ($new > $stock) {
            $new = $stock;
            $msg = 'Size ' . $variant['Size'] . ' - màu ' . $variant['Color']
                . ' chỉ còn ' . $stock . ' sản phẩm, giỏ hàng đã được cập nhật theo số lượng này.';
        }

        $cart[$variantId] = $new;
        $this->save($cart);

        return [true, $msg];
    }

    /** Đặt số lượng (<= 0 là xóa dòng). Trả về true nếu có hạ số lượng vì thiếu tồn kho. */
    public function setQuantity(int $variantId, int $qty): bool
    {
        $cart = $this->raw();

        if (! isset($cart[$variantId])) {
            return false;
        }

        if ($qty <= 0) {
            unset($cart[$variantId]);
            $this->save($cart);

            return false;
        }

        $row   = $this->db->table('product_variants')
            ->select('StockQuantity')->where('VariantID', $variantId)
            ->get()->getRowArray();
        $stock = (int) ($row['StockQuantity'] ?? 0);

        $limited = false;

        if ($qty > $stock) {
            $qty     = $stock;
            $limited = true;
        }

        if ($qty <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId] = $qty;
        }

        $this->save($cart);

        return $limited;
    }

    public function remove(int $variantId): void
    {
        $cart = $this->raw();
        unset($cart[$variantId]);
        $this->save($cart);
    }
}
