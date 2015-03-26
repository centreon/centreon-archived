<?php

/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
    public function getSteps(&$steps)
    {
        foreach ($this->setUp as $singleSetUp) {
            $singleSetUp->genForm($steps);
        }
    }
}
