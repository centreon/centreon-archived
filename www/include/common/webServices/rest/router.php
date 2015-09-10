<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (false === defined('WS_INIT')) {
    die();
}

$fromBody = false;
$httpParams = array();
/* Get request method type */
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $httpParams = $_GET;
        $methodPrefix = 'get';
        break;
    case 'POST':
        $fromBody = true;
        $methodPrefix = 'post';
        break;
    case 'PUT':
        $fromBody = true;
        $methodPrefix = 'put';
        break;
    case 'PATCH':
        $fromBody = true;
        $methodPrefix = 'patch';
        break;
    case 'DELETE':
        $methodPrefix = 'delete';
        break;
    default:
        CentreonWebService::sendJson("Bad request", 400);
        break;
}

if ($fromBody) {
    try {
        $httpParams = json_decode(file_get_contents('php://input'));
    } catch (Exception $e) {
        CentreonWebService::sendJson("Bad parameters", 400);
    }
}

if (false === isset($httpParams['object']) || false === isset($httpParams['action'])) {
    CentreonWebService::sendJson("Bad parameters", 400);
}

$action = $methodPrefix . ucfirst($httpParams['action']);

$webServices = new CentreonWebService();
$webService = $webServices->getWebService($httpParams['object'], $action);

if (!count($webService)) {
    CentreonWebService::sendJson("Method not found", 404);
}

require_once($webService['path']);

$object = new $webService['class']();

$args = $httpParams;
unset($args['action']);
unset($args['object']);

if (false === method_exists($object, $action)) {
    CentreonWebService::sendJson("Method not found", 404);
}

try {
    $data = $object->$action($args);
    CentreonWebService::sendJson($data);
} catch (RestException $e) {
    CentreonWebService::sendJson($e->getMessage(), $e->getCode());
} catch (Exeption $e) {
    CentreonWebService::sendJson("Internal server error", 500);
}