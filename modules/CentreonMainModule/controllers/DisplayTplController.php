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

/**
 * Description of DisplayTplControler
 *
 * @author bsauveton
 */
class DisplayTplController extends Controller
{
    private function validate_alpha($str) 
    {
        return preg_match('/^[a-zA-Z0-9_]+$/',$str);
    }
    
    
    /**
     * 
     * @method get
     * @route /viewtpl/[:module]/[:file]
     */
    public function displayTplAction()
    {
        $requestParam = $this->getParams('named');
        
        if (!$this::validate_alpha($requestParam['module']) || !$this::validate_alpha($requestParam['file'])){
            return false;
        }
        $tplName = 'modules/' . $requestParam['module'] . '/views/slideMenu/' . $requestParam['file'] . '.tpl';
        $config = Di::getDefault()->get('config');
        $centreon_path = rtrim($config->get('global', 'centreon_path'), '/');

        if (file_exists($centreon_path . '/' . $tplName)) {
            echo file_get_contents($centreon_path . '/' . $tplName);
        }
    }    
}
