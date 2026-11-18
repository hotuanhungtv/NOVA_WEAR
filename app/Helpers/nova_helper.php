<?php

/**
 * Hàm tiện ích dùng chung cho NOVA Wear (nạp bằng helper('nova')).
 */

use App\Libraries\CartService;

if (! function_exists('e')) {
    /** Escape HTML ngắn gọn. */
    function e($value): string
    {
        return esc((string) $value);
    }
}

if (! function_exists('money')) {
    function money($value): string
    {
        return number_format((float) $value, 0, ',', '.') . ' đ';
    }
}

if (! function_exists('order_status_label')) {
    function order_status_label(string $status): string
    {
        return [
            'Pending'   => 'Chờ xác nhận',
            'Confirmed' => 'Đã xác nhận',
            'Shipping'  => 'Đang giao',
            'Completed' => 'Hoàn thành',
            'Cancelled' => 'Đã hủy',
        ][$status] ?? $status;
    }
}

if (! function_exists('order_status_badge')) {
    function order_status_badge(string $status): string
    {
        return [
            'Pending'   => 'bg-warning text-dark',
            'Confirmed' => 'bg-primary',
            'Shipping'  => 'bg-info text-dark',
            'Completed' => 'bg-success',
            'Cancelled' => 'bg-secondary',
        ][$status] ?? 'bg-secondary';
    }
}

if (! function_exists('product_img')) {
    /** URL ảnh sản phẩm trong public/uploads/products. */
    function product_img(?string $file): string
    {
        return $file ? base_url('uploads/products/' . rawurlencode($file)) : '';
    }
}

if (! function_exists('stars')) {
    /** Hiển thị 5 ngôi sao (làm tròn tới 0,5). */
    function stars($rating, string $class = ''): string
    {
        $rating = max(0, min(5, round(((float) $rating) * 2) / 2));
        $html   = '<span class="stars ' . esc($class, 'attr') . '" role="img" aria-label="'
            . esc(number_format($rating, 1, ',', '')) . ' trên 5 sao">';

        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= '<i class="bi bi-star-fill"></i>';
            } elseif ($rating >= $i - 0.5) {
                $html .= '<i class="bi bi-star-half"></i>';
            } else {
                $html .= '<i class="bi bi-star"></i>';
            }
        }

        return $html . '</span>';
    }
}

if (! function_exists('mask_name')) {
    /** "Nguyễn Minh Anh" -> "Nguyễn M.A." (không lộ tên đầy đủ trên trang công khai). */
    function mask_name(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];

        if (count($parts) <= 1) {
            return $name;
        }

        $first    = array_shift($parts);
        $initials = '';

        foreach ($parts as $p) {
            $initials .= mb_strtoupper(mb_substr($p, 0, 1)) . '.';
        }

        return $first . ' ' . $initials;
    }
}

if (! function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }

        try {
            $then = new DateTime($datetime);
            $diff = (new DateTime('now'))->getTimestamp() - $then->getTimestamp();
        } catch (Throwable $e) {
            return $datetime;
        }

        if ($diff < 60) {
            return 'Vừa xong';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' phút trước';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' giờ trước';
        }
        if ($diff < 86400 * 30) {
            return floor($diff / 86400) . ' ngày trước';
        }

        return $then->format('d/m/Y');
    }
}

if (! function_exists('safe_local_path')) {
    /** Chỉ cho phép chuyển hướng tới đường dẫn nội bộ ("/abc"), chặn "//evil.com". */
    function safe_local_path(?string $path, string $fallback = '/'): string
    {
        if ($path && $path[0] === '/' && ! str_starts_with($path, '//') && ! str_contains($path, '\\')) {
            return $path;
        }

        return $fallback;
    }
}

if (! function_exists('nova_nav')) {
    /**
     * Dữ liệu dùng chung cho header: danh mục + số lượng/tổng tiền giỏ hàng.
     * Kết quả được nhớ trong 1 request nên gọi nhiều lần không tốn thêm truy vấn.
     */
    function nova_nav(): array
    {
        static $nav = null;

        if ($nav === null) {
            $categories = \Config\Database::connect()
                ->table('categories')
                ->select('CategoryID, CategoryName')
                ->orderBy('CategoryID')
                ->get()->getResultArray();

            $cart = new CartService();

            $nav = [
                'categories' => $categories,
                'cartCount'  => $cart->count(),
                'cartTotal'  => $cart->total(),
            ];
        }

        return $nav;
    }
}

if (! function_exists('review_requires_purchase')) {
    /** Bật bằng biến môi trường nova.reviewRequirePurchase = true */
    function review_requires_purchase(): bool
    {
        $v = env('nova.reviewRequirePurchase', env('nova_reviewRequirePurchase', false));

        return $v === true || $v === 'true' || $v === '1' || $v === 1;
    }
}
