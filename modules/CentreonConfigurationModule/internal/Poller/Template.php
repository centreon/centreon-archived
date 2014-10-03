<?php

/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Internal\Poller;

use \CentreonConfiguration\Internal\Poller\Template\Engine;
use \CentreonConfiguration\Internal\Poller\Template\Broker;
use Centreon\Internal\Form;

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
     */
    public function __construct($name, $enginePath = "", $brokerPath = "")
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
     * @return \CentreonConfiguration\Internal\Poller\Template\Broker
     */
    public function getBrokerPart()
    {
        return $this->brokerPart;
    }
    
    /**
     * 
     */
    public function genForm()
    {
       return $this->loadSteps();
    }
    
    /**
     * 
     * @param array $steps
     */
    private function loadSteps()
    {
        $steps = array();
        
        $rStep = array(
            'engine' => false,
            'broker' => false,
            'steps' => array()
        );
        
        if (!is_null($this->enginePart)) {
            $rStep['engine'] = true;
            $this->enginePart->getSteps($steps);
        }
        
        if (!is_null($this->brokerPart)) {
            $rStep['broker'] = true;
            $this->brokerPart->getSteps($steps);
        }
        
        foreach ($steps as $stepName => $step) {
            $fName = array();
            $fields = "<div>";
            $fComponents = $this->buildFormComponents($step, $fName);
            
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
     * @return array
     */
    private function buildFormComponents($step, &$fName)
    {
        $myForm = new Form('pollerTemplate');
        foreach ($step as $field) {
            $fName[] = $field['name'];
            $formField = array(
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'mandatory' => 1,
                'attributes' => json_encode($field['attributes'])
            );
            $myForm->addStatic($formField);
        }
        $formComponents = $myForm->toSmarty();
        unset($myForm);
        
        return $formComponents;
    }
}
