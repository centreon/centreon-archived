<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root_dir = realpath(__DIR__ . '/../');

set_include_path(
    $root_dir . '/application/class' . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . get_include_path()
);

require $root_dir . '/vendor/autoload.php';

define('DATA_DIR', __DIR__ . '/data');

spl_autoload_register(function ($classname) use ($root_dir) {
    $filename = $root_dir . '/application/class/' . str_replace('\\', '/', $classname) . '.php';
    if (file_exists($filename)) {
        require $filename;
    }
});
