<?php
$useOldVersion = false;
if ($useOldVersion) {
    require_once realpath(__DIR__ . "/data.old.php");
} else {
    require_once realpath(__DIR__ . "/data.new.php");
}
