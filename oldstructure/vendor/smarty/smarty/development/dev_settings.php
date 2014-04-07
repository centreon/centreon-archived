<?php

if (file_exists(dirname(__FILE__)."/local_dev_settings.php")) {
  require_once(dirname(__FILE__)."/local_dev_settings.php");
}

if (!isset($smarty_dev_php_cli_bin)) {
  $smarty_dev_php_cli_bin = "php";
}
