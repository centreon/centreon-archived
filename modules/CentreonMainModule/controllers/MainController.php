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

namespace CentreonMain\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;
use CentreonMain\Events\Status;

/**
 * Home controller
 * @authors Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class MainController extends Controller
{
    public static $moduleName = 'CentreonMain';
    
    /**
     * Action for home page
     *
     * @method GET
     * @route /
     */
    public function indexAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $backUrl = $router->getPathFor('/centreon-realtime/service');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * Action for home page
     *
     * @method GET
     * @route /home
     */
    public function homeAction()
    {
        $this->display('home.tpl');
    }

    /**
     * Route for getting refresh information
     *
     * @method GET
     * @route /status
     */
    public function statusAction()
    {
        $router = Di::getDefault()->get('router');
        $events = Di::getDefault()->get('events');
        $status = array();

        $statusEvent = new Status($status);

        $events->emit('centreon-main.status', array($statusEvent));
        $status['success'] = true;
        $router->response()->json($status);
    }
}
