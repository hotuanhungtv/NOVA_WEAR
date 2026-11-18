<?php

namespace Config;

use App\Filters\AdminOnly;
use App\Filters\CustomerAuth;
use App\Filters\StaffAuth;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * Tên ngắn (alias) của các filter.
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,

        // ---- NOVA Wear ----
        'customer'      => CustomerAuth::class,
        'staff'         => StaffAuth::class,
        'adminonly'     => AdminOnly::class,
    ];

    /**
     * Filter luôn chạy (đầu / cuối mỗi request).
     *
     * @var array<string, list<string>>
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * Filter áp dụng cho mọi request.
     * CSRF bảo vệ toàn bộ form POST (mọi form đều có csrf_field()).
     *
     * @var array<string, array<string, array<string, string>>|list<string>>
     */
    public array $globals = [
        'before' => [
            'csrf',
        ],
        'after' => [],
    ];

    /** @var array<string, list<string>> */
    public array $methods = [];

    /** @var array<string, array<string, list<string>>> */
    public array $filters = [];
}
