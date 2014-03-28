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

namespace Controllers\Api;

/**
 * Login controller
 * @authors Julien Mathis
 * @package Centreon
 * @subpackage Controllers
 */
class HostController extends \Centreon\Core\Controller
{
    /**
     * Action for listing hosts
     *
     * @method GET
     * @route /api/1.0/host
     */
    public function listAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');

        /*
         * Fields that we want to display
         */
        $params = 'host_id,host_name,host_alias,host_address,host_activate';
        
        $hostList = \Models\Configuration\Host::getList($params, -1, 0, null, "ASC", array("host_register" => '1'));
        
        $router->response()->json(
                                  array(
                                        "api-version" => 1,
                                        "status" => true,
                                        "data" => $hostList
                                        )
                                  );
    }

    /**
     * Action to get info a specific host
     *
     * @method GET
     * @route /api/1.0/host/[i:id]
     */
    public function listHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');

        /* 
         * Get parameters
         */
        $param = $router->request()->paramsNamed();

        /*
         * Query parameter
         */
        $params = array(
                        "host_id" => $param['id'], 
                        "host_register" => '1'
                        );
        
        /*
         * Get host informations
         */
        $hostList = \Models\Configuration\Host::getList('*', -1, 0, null, "ASC", $params, "AND");

        $router->response()->json(
                                  array(
                                        "api-version" => 1,
                                        "status" => true,
                                        "data" => $hostList
                                        )
                                  );
    }

    /**
     * Action for update 
     *
     * @method PUT
     * @route /api/1.0/host/[i:id]
     */
    public function updateAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for add
     *
     * @method POST
     * @route /api/1.0/host/
     */
    public function addAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for delete
     *
     * @method DELETE
     * @route /api/1.0/host/[i:id]
     */
    public function deleteAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for duplicate
     *
     * @method PUT
     * @route /api/1.0/host/duplicate/[i:id]
     */
    public function duplicateAction()
    {
        print "Not implemented yet";
    }

}
