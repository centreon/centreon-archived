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

use \Centreon\Internal\Di,
    \CentreonConfiguration\Repository\ConfigApplyRepository;

/**
 * @authors Julien Mathis
 * @package Centreon
 * @subpackage Controllers                                   
 */
class ConfigApplyApi extends \Centreon\Internal\Controller
{
    /**
     * Generic function
     *
     */
    public function genericAction($action)
    {
        $di = Di::getDefault();

        $router = $di->get('router');

        $param = $router->request()->paramsNamed();

        $obj = new ConfigApplyRepository($param["id"]);
        $obj->action($action);

        $router->response()->json(
            array(
                "status" => $obj->getStatus(),
                "output" => $obj->getOutput()
            )
        );
    }

    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /api/configuration/[a:version]/applycfg/[i:id]
     */
    public function applyAction()
    {
        return $this->genericAction("apply");
    }


    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /api/configuration/[a:version]/restartcfg/[i:id]
     */
    public function restartAction()
    {
        return $this->genericAction("restart");
    }

    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /api/configuration/[a:version]/reloadcfg/[i:id]
     */
    public function reloadAction()
    {
        return $this->genericAction("reload");
    }

    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /api/configuration/[a:version]/forcereloadcfg/[i:id]
     */
    public function forcereloadAction()
    {
        return $this->genericAction("forcereload");
    }
}
