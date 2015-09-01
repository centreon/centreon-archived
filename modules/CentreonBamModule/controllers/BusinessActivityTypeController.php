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

namespace CentreonBam\Controllers;

use Centreon\Internal\Di;
#use CentreonBam\Models\BusinessActivityType;
use Centreon\Controllers\FormController;

class BusinessActivityTypeController extends FormController
{
    protected $objectDisplayName = 'BusinessActivityType';
    public static $objectName = 'businessactivitytype';
    protected $objectBaseUrl = '/centreon-bam/businessactivitytype';
    public static $relationMap = array();
#    protected $datatableObject = '\CentreonConfiguration\Internal\TimeperiodDatatable';
    protected $objectClass = '\CentreonBam\Models\BusinessActivityType';
    protected $repository = '\CentreonBam\Repository\BusinessActivityTypeRepository';
	
	/**
     *
     * @method get
     * @route /businessactivitytype/configuration
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('hogan-3.0.0.min.js');
        $urls = array();
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }

}
