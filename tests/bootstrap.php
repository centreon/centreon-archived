<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root_dir = realpath(__DIR__ . '/../');

set_include_path(
    $root_dir . '/application/class' . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . get_include_path()
);

require $root_dir . '/vendor/autoload.php';
