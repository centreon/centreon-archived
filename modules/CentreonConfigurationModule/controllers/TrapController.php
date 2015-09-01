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

namespace CentreonConfiguration\Controllers;

use Centreon\Controllers\FormController;

class TrapController extends FormController
{
    protected $objectDisplayName = 'Trap';
    public static $objectName = 'trap';
    protected $objectBaseUrl = '/centreon-configuration/trap';
    protected $objectClass = '\CentreonConfiguration\Models\Trap';
    protected $datatableObject = '\CentreonConfiguration\Internal\TrapDatatable';
    protected $repository = '\CentreonConfiguration\Repository\TrapRepository';
    public static $relationMap = array(
        'trap_services' => '\CentreonConfiguration\Models\Relation\Trap\Service',
        'trap_servicetemplates' => '\CentreonConfiguration\Models\Relation\Trap\Servicetemplate',
        'trap_manufacturer' => '\CentreonConfiguration\Models\Relation\Trap\Manufacturer'
    );

    /**
     * Manufacturer for specific trap
     *
     * @method get
     * @route /trap/[i:id]/manufacturer
     */
    public function manufacturerForTrapAction()
    {
        parent::getSimpleRelation('manufacturer_id', '\CentreonConfiguration\Models\Manufacturer');
    }

    /**
     * Services for specific trap
     *
     * @method get
     * @route /trap/[i:id]/service
     */
    public function serviceForTrapAction()
    {
        parent::getRelations(static::$relationMap['trap_services']);
    }

    /**
     * Service templates for specific trap
     *
     * @method get
     * @route /trap/[i:id]/servicetemplate
     */
    public function servicetemplateForTrapAction()
    {
        parent::getRelations(static::$relationMap['trap_servicetemplates']);
    }
}
