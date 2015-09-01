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

namespace CentreonConfiguration\Internal\Poller;

/**
 * Description of LiteTemplate
 *
 * @author lionel
 */
class LiteTemplate
{
    /**
     *
     * @var string 
     */
    private $name;
    
    /**
     *
     * @var string 
     */
    private $enginePath;
    
    /**
     *
     * @var string 
     */
    private $brokerPath;
    
    /**
     * 
     * @param string $name
     * @param string $enginePath
     * @param string $brokerPath
     */
    public function __construct($name, $enginePath = "", $brokerPath = array())
    {
        $this->name = $name;
        $this->enginePath = $enginePath;
        if (count($brokerPath) > 0) {
            $this->brokerPath[] = $brokerPath;
        }
    }
    
    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        return array('name', 'enginePath', 'brokerPath');
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * 
     * @param string $newName
     */
    public function setName($newName)
    {
        $this->name = $newName;
    }
    
    /**
     * 
     * @return string
     */
    public function getEnginePath()
    {
        return $this->enginePath;
    }
    
    /**
     * 
     * @param string $newEnginePath
     */
    public function setEnginePath($newEnginePath)
    {
        $this->enginePath = $newEnginePath;
    }
    
    /**
     * 
     * @return string
     */
    public function getBrokerPath()
    {
        return $this->brokerPath;
    }
    
    /**
     * 
     * @param string $newBrokerPath
     */
    public function setBrokerPath($newBrokerPath)
    {
        $this->brokerPath[] = $newBrokerPath;
    }
    
    /**
     * 
     * @return \CentreonConfiguration\Internal\Poller\Template
     */
    public function toFullTemplate()
    {
        $fullTemplate = new Template($this->name, $this->enginePath, $this->brokerPath);
        return $fullTemplate;
    }
}
