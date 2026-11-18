<?php

namespace Config;

/**
 * Đường dẫn của ứng dụng. Lưu ý: thư mục "system" nằm trong vendor/
 * (khác với bản chép từ gói framework, nơi system nằm cùng cấp với app).
 */
class Paths
{
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    public string $appDirectory = __DIR__ . '/..';

    public string $writableDirectory = __DIR__ . '/../../writable';

    public string $testsDirectory = __DIR__ . '/../../tests';

    public string $viewDirectory = __DIR__ . '/../Views';
}
