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
 */

namespace CentreonBam\Commands;

use Centreon\Api\Internal\BasicCrudCommand;
use CentreonBam\Repository\IndicatorRepository;
use CentreonBam\Repository\BooleanIndicatorRepository;
use CentreonBam\Models\Indicator;
/**
 * Description of KpiCommand
 *
 * @author bsauveton
 */
class IndicatorCommand extends BasicCrudCommand
{
    /**
     *
     * @var type 
     */
    public $objectName = 'indicator';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @cmdForm /centreon-bam/indicator/update required 
     * @cmdParam boolean|false disable required disable 
     */
    public function createAction($params){
        IndicatorRepository::transco($params);
        $params['object'] = $this->objectName;
        $id = IndicatorRepository::createIndicator($this->parseObjectParams($params), 'api', '/centreon-bam/indicator/update');

        // show slug of boolean indicator only
        if(!is_null($id)){
            $slug = BooleanIndicatorRepository::getSlugNameById($id);
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($slug, true, 'green');
        }
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully created", true, 'green');
    }
    
    /**
     * 
     * @cmdForm /centreon-bam/indicator/update optional 
     * @cmdObject string ba the ba
     * @cmdObject string indicator-ba the indicator-ba kpi
     * @cmdObject string service the service kpi
     * @cmdObject string boolean the boolean kpi 
     * @cmdParam none service-tags optional
     * @cmdParam boolean|false disable optional disable 
     * @cmdParam boolean|true enable optional enable 
     */
    public function updateAction($object,$params = null){
        IndicatorRepository::transco($params);
        IndicatorRepository::transco($object);
        $params['object'] = $this->objectName;
        IndicatorRepository::updateIndicator(
                $this->parseObjectParams($params), 
                'api', 
                '/centreon-bam/indicator/update',
                false,
                $this->parseObjectParams($object)
        );
        
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully updated", true, 'green');
    }
    
    /**
     * @cmdObject string ba the ba
     * @cmdObject string indicator-ba the indicator-ba kpi
     * @cmdObject string service the service kpi
     * @cmdObject string boolean the boolean kpi 
     */
    public function deleteAction($object){
        IndicatorRepository::transco($object);
        $kpi = Indicator::getKpi($this->parseObjectParams($object));
        IndicatorRepository::delete(array($kpi['kpi_id']));
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully deleted", true, 'green');
    }
    
    /**
     * @cmdForm /centreon-bam/indicator/update map
     * @cmdObject string ba the ba
     * @cmdObject string indicator-ba the indicator-ba kpi
     * @cmdObject string service the service kpi
     * @cmdObject string boolean the boolean kpi 
     */
    public function showAction($object){
        IndicatorRepository::transco($object);
        $kpi = Indicator::getKpi($this->parseObjectParams($object));
        $this->normalizeSingleSet($kpi);
        $result = '';
        foreach ($kpi as $key => $value) {
            $result .= $key . ': ' . $value . "\n";
        }
        
        echo $result;
    }
    
    /**
     * 
     */
    public function getSlugAction($object=null){
        \Centreon\Internal\Utils\CommandLine\InputOutput::display('Not implemented Yet', true, 'red');
        
    }
    
    
}

