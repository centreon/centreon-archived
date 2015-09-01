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

use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator\Web\Full;
use CentreonAdministration\Models\Options;
use CentreonAdministration\Repository\OptionRepository;
use Centreon\Controllers\FormController;

/**
 * Description of OptionsController
 *
 * @author lionel
 */
class OptionsController extends FormController
{
    protected $objectDisplayName = 'Options';
    public static $objectName = 'options';
    protected $objectBaseUrl = '/centreon-administration/options/centreon';
    protected $objectClass = '\CentreonAdministration\Models\Options';

    public static $moduleName = 'centreon-administration';
    protected $repository = '\CentreonAdministration\Repository\OptionRepository';
    public static $relationMap = array();

    /**
     *
     * @method get
     * @route /options/centreon
     */
    public function editAction($additionnalParamsForSmarty = array(), $defaultValues = array())
    {
        $defaultValues = Options::getList();
        parent::editAction($additionnalParamsForSmarty, $defaultValues);
    }


    /**
     * Update centreon options
     *
     *
     * @method post
     * @route /options/centreon/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
}
