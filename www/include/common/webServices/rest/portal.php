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

    require_once "@CENTREON_ETC@/centreon.conf.php";
    require_once $centreon_path . 'www/class/centreonSession.class.php';
    require_once $centreon_path . 'www/class/centreon.class.php';
    require_once dirname(__FILE__) . '/exceptions.php';

    ini_set("session.gc_maxlifetime", "31536000");

    CentreonSession::start();

    /*
     * Check autologin here
     */
    if (!isset($_SESSION["centreon"])) {
        if (!isset($_GET['autologin'])) {
            header("Location: index.php?disconnect=1");
        } else {
            $args = NULL;
            foreach ($_GET as $key=>$value) { 
                $args ? $args .= "&".$key."=".$value : $args = $key."=".$value;
            }
            header("Location: index.php?".$args."");
        }
    }

    /*
     * Define Oreon var alias
     */
    if (isset($_SESSION["centreon"])) {
        $centreon = $_SESSION["centreon"];
        $oreon = $centreon;
    }
    if (!isset($centreon) || !is_object($centreon) || !isset($_GET['object']) || !isset($_GET['action'])) {
        echo json_encode(array());
        return;
    }

    require_once $centreon_path . "/www/class/centreonDB.class.php";
    require_once "./webService.class.php";

    $action = 'get' . ucfirst($_GET['action']);

    $webServices = new CentreonWebService();
    $webService = $webServices->getWebService($_GET['object'], $action);

    if (!count($webService)) {
        echo json_encode(array());
        return;
    }

    require_once($webService['path']);

    $object = new $webService['class']();

    $args = $_GET;
    unset($args['action']);
    unset($args['object']);

    if (method_exists($object, $action)) {
        header('Content-Type: application/json');
        try {
            $object->$action($args);
        } catch (RestException $e) {
            echo json_encode(array());
            return;
        } catch (RestExeption $e) {
            echo json_encode(array());
            return;
        }
    }
?>
