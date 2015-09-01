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

use CentreonConfiguration\Internal\Poller\Template\Engine;
use CentreonConfiguration\Internal\Poller\Template\Broker;
use Centreon\Internal\Form;
use Centreon\Internal\Di;
use CentreonConfiguration\Events\BrokerPollerConf as BrokerPollerConfEvent;

/**
 * Description of Template
 *
 * @author lionel
 */
class Template
{
    /**
     *
     * @var string Name of the poller template
     */
    private $name;
    
    /**
     *
     * @var \CentreonConfiguration\Internal\Poller\Template\Engine 
     */
    private $enginePart;
    
    /**
     *
     * @var \CentreonConfiguration\Internal\Poller\Template\Broker 
     */
    private $brokerPart;
    
    /**
     * 
     * @param string $name
     * @param string $enginePath
     * @param string $brokerPath
     */
    public function __construct($name, $enginePath = "", $brokerPath = array())
    {
        $this->name = $name;
        
        if (!empty($enginePath)) {
            $this->enginePart = new Engine($enginePath);
        }
        
        if (!empty($brokerPath)) {
            $this->brokerPart = new Broker($brokerPath);
        }
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
     * @param string $newName New name of the poller template
     */
    public function setName($newName)
    {
        $this->name = $newName;
    }
    
    /**
     * 
     * @return \CentreonConfiguration\Internal\Poller\Template\Engine
     */
    public function getEnginePart()
    {
        return $this->enginePart;
    }
    
    /**
     * 
     * @param \CentreonConfiguration\Internal\Poller\Template\Engine $newEnginePart
     */
    public function setEnginePart(\CentreonConfiguration\Internal\Poller\Template\Engine $newEnginePart)
    {
        $this->enginePart = $newEnginePart;
    }
    
    /**
     * 
     * @return \CentreonConfiguration\Internal\Poller\Template\Broker
     */
    public function getBrokerPart()
    {
        return $this->brokerPart;
    }
    
    /**
     * 
     * @param \CentreonConfiguration\Internal\Poller\Template\Broker $newBrokerPart
     */
    public function setBrokerPart(\CentreonConfiguration\Internal\Poller\Template\Broker $newBrokerPart)
    {
        $this->brokerPart = $newBrokerPart;
    }
    
    /**
     * Generate form for a template
     * 
     * @param int $pollerId The poller id for load the value in edition
     * @return array
     */
    public function genForm($pollerId = null)
    {
        $values = array();
        if (!is_null($pollerId)) {
            $events = Di::getDefault()->get('events');
            $eventParams = new BrokerPollerConfEvent($pollerId, $values);
            $events->emit('centreon-configuration.broker.poller.conf', array($eventParams));
            $values = $eventParams->getValues();
        }
        return $this->loadSteps($values);
    }

    /**
     * Load steps for a template
     * 
     * @param array $pollerValues The default values
     * @return array
     */
    private function loadSteps($values)
    {
        $steps = array();
        
        $rStep = array(
            'engine' => false,
            'broker' => false,
            'steps' => array()
        );
        
        if (!is_null($this->enginePart)) {
            $rStep['engine'] = true;
            $this->enginePart->genSteps($steps);
        }
        
        if (!is_null($this->brokerPart)) {
            $rStep['broker'] = true;
            $this->brokerPart->genSteps($steps);
        }
        
        foreach ($steps as $stepName => $step) {
            $fName = array();
            $fields = "<div>";
            $fComponents = $this->buildFormComponents($step, $fName, $values);
            
            foreach($fName as $field) {
                $fields .= $fComponents[$field]['html'];
            }
            $fields .= "</div>";
            $rStep['steps'][] = array(
                'name' => $stepName,
                'html' => $fields
            );
        }
        
        return $rStep;
    }
    
    /**
     * 
     * @param array $step
     * @param array $fName
     * @param array $value List of default values
     * @return array
     */
    private function buildFormComponents($step, &$fName, $values)
    {
        $myForm = new Form('pollerTemplate');
        foreach ($step as $field) {
            $fName[] = $field['name'];
            $attributes = array();
            if (isset($field['attributes'])) {
                $attributes = json_encode($field['attributes']);
            }
            $mandatory = 1;
            if (isset($field['require']) && false === $field['require']) {
                $mandatory = 0;
            }
            $formField = array(
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'mandatory' => $mandatory,
                'attributes' => $attributes
            );
            $myForm->addStatic($formField);
        }
        $myForm->setDefaults($values);
        $formComponents = $myForm->toSmarty();
        unset($myForm);
        
        return $formComponents;
    }
}
