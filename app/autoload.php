<?php
spl_autoload_register(function ($class) {
    $relative = preg_replace('/^App\\\\/', '', ltrim($class, '\\'));
    $relativePath = str_replace('\\', '/', $relative);

    $candidates = [
        __DIR__ . '/' . $relativePath . '.php',
        __DIR__ . '/Models/' . basename($relativePath) . '.php',
        __DIR__ . '/Services/' . basename($relativePath) . '.php',
        __DIR__ . '/Support/' . basename($relativePath) . '.php',
    ];

    foreach ($candidates as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
