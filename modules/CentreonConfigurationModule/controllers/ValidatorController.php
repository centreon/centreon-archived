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
use Centreon\Internal\Controller;
use Centreon\Internal\Form\Validators\Validator;

use CentreonMain\Forms\Validators\Unique;
use CentreonMain\Forms\Validators\CircularDependency;

class ValidatorController extends Controller
{
    public static $sContext  = 'client';
    
    /**
     * 
     *
     * @method post
     * @route /validator/unique
     */
    public function uniqueAction()
    {
       
        $params = $this->getParams('post')->all();
        
        $value = '';
        $aParams = array('object' => $params['object'], 'extraParams' => $params);
        
        $oValidator = new Unique();
        echo json_encode($oValidator->validate($value, $aParams, static::$sContext));
        
       
    }
    
    /**
     * 
     *
     * @method post
     * @route /validator/circular
     */
    public function circularAction()
    {
        $params = $this->getParams('post')->all();
        
        $value = '';
           
        $oValidator = new CircularDependency();
        echo json_encode($oValidator->validate($value, $params, static::$sContext));

    }
}
