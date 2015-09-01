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

class MenuController extends Controller
{
    /**
     * Get menu
     *
     * @method get
     * @route /menu/getmenu/
     */
    public function getmenuAction()
    {
        $params = $this->getParams("get");
        $menu = Di::getDefault()->get('menu');
        $menu_id = null;
        if (isset($params->menu_id)) {
            $menu_id = $params->menu_id;
        }
        $menudata = $menu->getMenu($menu_id);
        $result = array(
            'success' => 1,
            'menu' => isset($menudata['children']) ? $menudata['children'] : array()
        );
        echo json_encode($result);
    }
}
