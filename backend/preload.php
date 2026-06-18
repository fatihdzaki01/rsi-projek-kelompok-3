<?php

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/vendor/laravel/framework/src'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        try {
            opcache_compile_file($file->getPathname());
        } catch (\Throwable) {
        }
    }
}

foreach ([
    __DIR__ . '/app/Helpers/ApiResponse.php',
    __DIR__ . '/app/Models/User.php',
    __DIR__ . '/app/Http/Middleware/RoleMiddleware.php',
    __DIR__ . '/bootstrap/app.php',
] as $file) {
    if (file_exists($file)) {
        opcache_compile_file($file);
    }
}
