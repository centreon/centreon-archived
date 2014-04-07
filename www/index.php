<?php
require_once "../bootstrap.php";

new \Centreon\Internal\Session();
session_start();

/* Dispatch route */
$router = \Centreon\Internal\Di::getDefault()->get('router');
try {
    $router->dispatch();
} catch (\Exception $e) {
    $router->response()->code(500);
    if ("dev" === \Centreon\Internal\Di::getDefault()->get('config')->get('global', 'env')) {
        echo '<pre>';
        echo $e->getMessage();
        var_dump(debug_backtrace());
        echo '</pre>';
    } else {
        $tmpl = \Centreon\Internal\Di::getDefault()->get('template'); 
        $router->response()->body($tmpl->fetch('500.tpl'));
    }
    $router->response()->send();
}
