<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Api\Rest;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\ConfigApplyRepository;
use Centreon\Internal\Controller;

/**
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Controllers                                   
 */
class ConfigApplyApi extends Controller
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
     * @route /applycfg/[i:id]
     */
    public function applyAction()
    {
        return $this->genericAction("apply");
    }


    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /restartcfg/[i:id]
     */
    public function restartAction()
    {
        return $this->genericAction("restart");
    }

    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /reloadcfg/[i:id]
     */
    public function reloadAction()
    {
        return $this->genericAction("reload");
    }

    /**
     * Action for Testing configuration files
     *
     * @method GET
     * @route /forcereloadcfg/[i:id]
     */
    public function forcereloadAction()
    {
        return $this->genericAction("forcereload");
    }
}
