<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
require_once "../bootstrap.php";

$requestUri = explode('/', filter_input(INPUT_SERVER, 'REQUEST_URI'));

if (file_exists("install.php") && (isset($requestUri[2]) && ($requestUri[2] != 'static'))) {
    
    $sectionToInit = array(
        'configuration',
        'template'
    );
    $bootstrap = new \Centreon\Internal\Bootstrap();
    $bootstrap->init($sectionToInit);
    $baseUrl = rtrim(\Centreon\Internal\Di::getDefault()->get('config')->get('global', 'base_url'), '/');
    header('Location: '.$baseUrl . '/install.php');
    
} else {
    
    try {
        $bootstrap = new \Centreon\Internal\Bootstrap();
        $bootstrap->init();
    } catch (\Exception $e) {
        echo $e;
    }

    new \Centreon\Internal\Session();
    session_start();

    /* Dispatch route */
    $router = \Centreon\Internal\Di::getDefault()->get('router');
    try {
        $router->dispatch();
    } catch (\Exception $e) {
        // Something wrong happens during request processing
        // If we are in "dev" environment, we are dumping a raw text-only stacktrace full screen
        // If we are in "prod" environment, we are loading a TPL to output a "nice" error page
        $tmpl = \Centreon\Internal\Di::getDefault()->get('template');
        $router->response()->code(500);
        if ("dev" === \Centreon\Internal\Di::getDefault()->get('config')->get('global', 'env')) {
            $tmpl->assign("exceptionMessage", $e->getMessage());
            $tmpl->assign("strace", var_export(debug_backtrace(), true));
            $router->response()->body($tmpl->fetch('500-devel.tpl'));
        } else {
            $router->response()->body($tmpl->fetch('500.tpl'));
        }
        $router->response()->send();
    }
}
