<?php

/**
 * Chép các file cấu hình chuẩn của CodeIgniter 4 (app/Config, app/Language,
 * app/Views/errors, writable, spark...) từ vendor/codeigniter4/framework vào dự án.
 *
 * - KHÔNG ghi đè file đã có, nên các file NOVA Wear tự viết (Routes.php, Filters.php...) được giữ nguyên.
 * - Tự chạy sau `composer install` / `composer update`; có thể chạy tay: php bin/setup.php
 */

$root   = dirname(__DIR__);
$source = $root . '/vendor/codeigniter4/framework';

if (! is_dir($source)) {
    fwrite(STDERR, "Chưa có vendor/codeigniter4/framework. Hãy chạy: composer install\n");
    exit(0);
}

function copy_missing(string $from, string $to): int
{
    $copied = 0;

    if (is_file($from)) {
        if (! file_exists($to)) {
            @mkdir(dirname($to), 0775, true);
            copy($from, $to);
            $copied++;
        }

        return $copied;
    }

    if (! is_dir($from)) {
        return 0;
    }

    @mkdir($to, 0775, true);

    foreach (scandir($from) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $copied += copy_missing($from . '/' . $item, $to . '/' . $item);
    }

    return $copied;
}

$map = [
    'app/Config'       => 'app/Config',
    'app/Language'     => 'app/Language',
    'app/Views/errors' => 'app/Views/errors',
    'app/Common.php'   => 'app/Common.php',
    'writable'         => 'writable',
    'spark'            => 'spark',
    'public/robots.txt'  => 'public/robots.txt',
    'public/favicon.ico' => 'public/favicon.ico',
];

$total = 0;

foreach ($map as $from => $to) {
    $total += copy_missing($source . '/' . $from, $root . '/' . $to);
}

// Tự sửa Paths.php nếu lỡ chép bản của gói framework (system nằm sai chỗ)
$pathsFile = $root . '/app/Config/Paths.php';
if (is_file($pathsFile)) {
    $code = file_get_contents($pathsFile);
    if (str_contains($code, "'/../../system'")) {
        file_put_contents($pathsFile, str_replace("'/../../system'", "'/../../vendor/codeigniter4/framework/system'", $code));
        echo "Đã sửa đường dẫn system trong app/Config/Paths.php\n";
    }
}

@chmod($root . '/spark', 0755);

foreach (['writable/cache', 'writable/logs', 'writable/session', 'writable/uploads', 'writable/debugbar', 'public/uploads/products'] as $dir) {
    if (! is_dir($root . '/' . $dir)) {
        @mkdir($root . '/' . $dir, 0775, true);
    }
}

echo "NOVA Wear setup: đã chép thêm {$total} file cấu hình mặc định của CodeIgniter.\n";
