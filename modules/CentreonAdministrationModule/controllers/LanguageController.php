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

namespace CentreonAdministration\Controllers;

use Centreon\Controllers\FormController;

class LanguageController extends FormController
{
    protected $objectDisplayName = 'Language';
    public static $objectName = 'language';
    protected $objectBaseUrl = '/centreon-administration/language';
    protected $objectClass = '\CentreonAdministration\Models\Language';
    protected $repository = '\CentreonAdministration\Repository\LanguageRepository';
    
    public static $relationMap = array();
    
    protected $datatableObject = '\CentreonAdministration\Internal\LanguageDatatable';
    public static $isDisableable = true;
}
