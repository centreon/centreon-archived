<?php
require_once "../bootstrap.php";

new \Centreon\Core\Session();
session_start();

/* Dispatch route */
$router = Di::getDefault()->get('router');
$router->dispatch();
