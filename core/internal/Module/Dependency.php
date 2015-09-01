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

namespace Centreon\Internal\Module;

use Centreon\Internal\Utils\Dependency\Graph;

/**
 * Dependencies resolver for entreon modules
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Dependency
{
    /**
     *
     * @var \Centreon\Internal\Utils\Dependency\Graph() 
     */
    private $dependencyGraph;
    
    /**
     *
     * @var array 
     */
    private $modules  = array();
    
    /**
     *
     * @var type 
     */
    private $moduleNameList  = array();
    
    /**
     *
     * @var type 
     */
    private $modulesOrderInstall = array();
    
    /**
     * 
     * @param array $modules
     */
    public function __construct($modules)
    {
        foreach($modules as $n => $module) {
            $this->modules[$n] = $module['infos']['dependencies'];
        }
        $this->moduleNameList = array_keys($this->modules);
        $this->dependencyGraph = new Graph();
        $this->buildDependenciesGraph();
    }
    
    /**
     * 
     * @return array
     */
    public function resolve()
    {
        $seen = array();
        $mList = $this->moduleNameList;
        
        while (count(array_diff($this->moduleNameList, $this->modulesOrderInstall)) > 0) {
            $currentModule = array_pop($mList);
            $this->dependencyGraph->resolve($currentModule, $this->modulesOrderInstall, $seen);
        }
        
        return $this->modulesOrderInstall;
    }
    
    /**
     * 
     */
    private function buildDependenciesGraph()
    {
        foreach ($this->modules as $mName => $mDep) {
            $this->dependencyGraph->addNode($mName, $mDep);
        }
    }
}
