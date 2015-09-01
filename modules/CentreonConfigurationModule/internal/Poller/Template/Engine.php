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

namespace CentreonConfiguration\Internal\Poller\Template;

use CentreonConfiguration\Internal\Poller\Template\SetUp\Engine as EngineSetUp;

/**
 * Description of Engine
 *
 * @author lionel
 */
class Engine
{
    /**
     *
     * @var string 
     */
    private $enginePath;
    
    /**
     *
     * @var array 
     */
    private $setUp;
    
    /**
     * 
     * @param string $enginePath
     */
    public function __construct($enginePath)
    {
        $this->enginePath = $enginePath;
        $this->getEnginePart();
    }
    
    /**
     * 
     * @throws Exception
     */
    private function getEnginePart()
    {
        $tplContent = json_decode(file_get_contents($this->enginePath), true);
        if (!isset($tplContent['content']['engine'])) {
            throw new \Exception("No Engine Part Found");
        }
        foreach($tplContent['content']['engine']['setup'] as $section) {
            $this->setUp[] = new EngineSetUp($section);
        }
    }
    
    /**
     * 
     * @param array $steps
     */
    public function genSteps(&$steps)
    {
        foreach ($this->setUp as $singleSetUp) {
            $singleSetUp->genForm($steps);
        }
    }
}
