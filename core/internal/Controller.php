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

namespace Centreon\Internal;

use Centreon\Internal\Di;

abstract class Controller extends HttpCore
{
    /**
     *
     * @var type 
     */
    protected $tpl;

    /**
     * 
     */
    protected function __construct($request)
    {
        parent::__construct($request);
        $this->tpl = Di::getDefault()->get('template');
        $this->init();
    }
    
    /**
     * 
     * @param string $varname
     * @param mixed $value
     */
    protected function assignVarToTpl($varname, $value)
    {
        $this->tpl->assign($varname, $value);
    }
    
    /**
     * 
     * @param string $cssFile
     * @param string $origin
     */
    protected function addCssToTpl($cssFile, $origin = 'current')
    {
        $this->tpl->addCss($cssFile);
    }
    
    /**
     * 
     * @param string $jsFile
     * @param string $origin
     */
    protected function addJsToTpl($jsFile, $origin = 'current')
    {
        $this->tpl->addJs($jsFile);
    }
    
    /**
     * 
     * @param string $tplFile
     */
    protected function display($tplFile)
    {
        $tplDirectory = 'file:['. static::$moduleName . 'Module]';
        $this->tpl->display($tplDirectory . $tplFile);
    }
    
    /**
     *
     */
    protected function init()
    {
        $userName = "";
        $userEmails = array();

        if (isset($_SESSION['user'])) {
            try {
                $userName = $_SESSION['user']->getName();
                $userEmails = $_SESSION['user']->getEmail();
            } catch (Exception $e) {
                ;
            }
        }

        $this->tpl->assign("userName", $userName);
        $this->tpl->assign("userEmails", $userEmails);
    }
}
