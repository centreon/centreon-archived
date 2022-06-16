<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

function get_memory_usage()
{
    $mem_usage = memory_get_usage(true);

    if ($mem_usage < 1024) {
        return $mem_usage . " bytes";
    }
    if ($mem_usage < 1048576) {
        return round($mem_usage / 1024, 2) . " kilobytes";
    }

    return round($mem_usage / 1048576, 2) . " megabytes";
}

$time_start = microtime(true);
*/
$useOldVersion = false;
if ($useOldVersion) {
    require_once realpath(__DIR__ . "/data.old.php");
} else {
    require_once realpath(__DIR__ . "/data.new.php");
}
/*
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "Time spent: $time seconds\n";
echo "Memory used : " . get_memory_usage();
*/
