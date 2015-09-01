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

use CentreonConfiguration\Models\Contact;
use Centreon\Controllers\FormController;

class ContactTemplateController extends FormController
{
    protected $objectDisplayName = 'Contact Template';
    public static $objectName = 'contacttemplate';
    public static $enableDisableFieldName = 'contact_activate';
    protected $objectBaseUrl = '/centreon-configuration/contacttemplate';
    protected $objectClass = '\CentreonConfiguration\Models\Contact';
    public static $relationMap = array(
        'contact_contactgroups' => '\Models\Configuraton\Relation\Contact\Contactgroup',
        'contact_hostcommands' => '\CentreonConfiguration\Models\Relation\Contact\Hostcommand',
        'contact_servicecommands' => '\CentreonConfiguration\Models\Relation\Contact\Servicecommand'
    );
    
    public static $isDisableable = true;

    /**
     * Get contact template for a specific contact
     *
     * @method get
     * @route /contacttemplate/[i:id]/contacttemplate
     */
    public function contactTemplateForContactAction()
    {
        parent::getSimpleRelation('contact_template_id', '\CentreonConfiguration\Models\Contact');
    }
   
    /**
     * Get contact group for a specific contact template
     *
     * @method get
     * @route /contacttemplate/[i:id]/contactgroup
     */
    public function contactGroupForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_contactgroups']);
    }
}
