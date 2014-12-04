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

namespace CentreonConfiguration\Api\Rest;

/**
 * Login controller
 * @authors Julien Mathis
 * @package Centreon
 * @subpackage Controllers
 */
class ServiceGroupApi extends \Centreon\Internal\Controller
{
    /**
     * Action for listing hosts
     *
     * @method GET
     * @route /servicegroup
     */
    public function listAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');

        /*
         * Fields that we want to display
         */
        $params = 'sg_id,sg_name';
        
        $cmdList = \CentreonConfiguration\Models\Servicegroup::getList($params);
        
        $router->response()->json(
            array(
                "api-version" => 1,
                "status" => true,
                "data" => $cmdList
            )
        );
    }

    /**
     * Action to get info a specific host
     *
     * @method GET
     * @route /servicegroup/[i:id]
     */
    public function listServicegroupAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');

        /*
         * Get parameters
         */
        $param = $router->request()->paramsNamed();

        /*
         * Query parameter
         */
        $params = array("sg_id" => $param['id']);
        
        /*
         * Get host informations
         */
        $hostList = \CentreonConfiguration\Models\Servicegroup::getList('*', -1, 0, null, "ASC", $params);

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
     * @route /servicegroup/[i:id]
     */
    public function updateAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for add
     *
     * @method POST
     * @route /servicegroup
     */
    public function addAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for delete
     *
     * @method DELETE
     * @route /servicegroup/[i:id]
     */
    public function deleteAction()
    {
        print "Not implemented yet";
    }

    /**
     * Action for duplicate
     *
     * @method PUT
     * @route /servicegroup/duplicate/[i:id]
     */
    public function duplicateAction()
    {
        print "Not implemented yet";
    }
}
