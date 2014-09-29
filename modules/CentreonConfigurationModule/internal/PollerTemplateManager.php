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

namespace CentreonConfiguration\Internal;

use CentreonConfiguration\Internal\PollerTemplate\BrokerSetUp;
use CentreonConfiguration\Internal\PollerTemplate\EngineSetUp;
use Centreon\Internal\Form;

/**
 * Description of PollerTemplateManager
 *
 * @author lionel
 */
class PollerTemplateManager
{
    /**
     *
     * @var type 
     */
    private $templateFilePath;
    
    /**
     *
     * @var type 
     */
    private $templateName;
    
    /**
     *
     * @var type 
     */
    private $engine;
    
    /**
     *
     * @var type 
     */
    private $broker;
    
    /**
     * 
     * @param type $templateFilePath
     * @throws Exception
     */
    public function __construct($templateFilePath)
    {
        if (!file_exists($templateFilePath)) {
            throw new Exception("Poller Template FIle does not exist");
        }
        
        $this->engine = array();
        $this->broker = array();
        
        $this->templateFilePath = $templateFilePath;
        $this->parseTemplateFile();
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->templateName;
    }
    
    /**
     * 
     */
    private function parseTemplateFile()
    {
        $engineLoaded = false;
        $brokerLoaded = false;
        
        $tempTemplateContent = json_decode(file_get_contents($this->templateFilePath), true);
        $this->templateName = $tempTemplateContent['name'];
        
        foreach ($tempTemplateContent['content'] as $section) {
            switch ($section['type']) {
                default:
                    break;
                case 'engine':
                    if (!$engineLoaded) {
                        $this->initEngineSetUp($section);
                        $engineLoaded = true;
                    }
                    break;
                case 'broker':
                    if (!$brokerLoaded) {
                        $this->initBrokerSetUp($section);
                        $brokerLoaded = true;
                    }
                    break;
            }
        }
    }
    
    /**
     * 
     * @param type $engineSection
     */
    private function initEngineSetUp($engineSection)
    {
        foreach($engineSection['setup'] as $section) {
            $this->engine[$section['name']] = new EngineSetUp($section);
        }
    }
    
    /**
     * 
     * @param type $brokerSection
     */
    private function initBrokerSetUp($brokerSection)
    {
        foreach($brokerSection['setup'] as $section) {
            $this->broker[$section['name']] = new BrokerSetUp($section);
        }
    }
    
    /**
     * 
     * @param type $steps
     */
    private function prepareSteps(&$steps)
    {
        foreach ($this->engine as $engine) {
            $engine->genForm($steps);
        }
        
        foreach ($this->broker as $broker) {
            $broker->genForm($steps);
        }
    }
    
    /**
     * 
     * @param type $step
     * @return type
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
    
    /**
     * 
     * @return array
     */
    public function generateFormForTemplate()
    {
        $steps = array();
        $this->prepareSteps($steps);
        
        $rStep = array(
            'engine' => false,
            'broker' => false
        );
        
        if (count($this->engine) > 0) {
            $rStep['engine'] = true;
        }
        if (count($this->broker) > 0) {
            $rStep['broker'] = true;
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
}
