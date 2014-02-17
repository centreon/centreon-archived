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
 */
namespace Controllers;

/**
 * Tools controller
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Controllers
 */
class ToolsController extends \Centreon\Core\Controller
{
    /**
     * Action for compile LESS
     *
     * @method GET
     * @route @\.css$
     */
    public function lessAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $route = $router->request()->pathname();
        $response = $router->response();
        /* Get path to  */
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $route = str_replace($baseUrl, '/', $route);
        $route = str_replace('css', 'less', $route);
	/* Remove min */
	$route = str_replace('.min.', '.', $route);
        $centreonPath = realpath(__DIR__ . '/../../www/');
        if (false === file_exists($centreonPath . $route)) {
            $response->redirect('404', 404);
            return;
        }
        
        /* Response compiled CSS */
        $response->header('Content-Type', 'text/css');
	$less = new \Less_Parser();
	$less->parseFile($centreonPath . $route);
        $response->body($less->getCss());
    }
}
