<?php
require_once "../bootstrap.php";

new \Centreon\Core\Session();
session_start();

/* Dispatch route */
$router = \Centreon\Core\Di::getDefault()->get('router');
try {
    $router->dispatch();
} catch (\Exception $e) {
    echo '<pre>'; echo $e->getMessage(); echo '</pre>';
    $tmpl = \Centreon\Core\Di::getDefault()->get('template'); 
    $router->response()->code(500);
    $router->response()->body($tmpl->fetch('500.tpl'));
    $router->response()->send();
}
