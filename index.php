<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(static function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = str_replace('\\', '/', substr($class, $len));
    $file = $base_dir . $relative_class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Include routes
require __DIR__ . '/routes/api.php';
