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

namespace Centreon\Internal\Form\Validators;

/**
 * Description of String
 *
 * @author lionel
 */
class String extends RespectValidationAbstract
{
    /**
     *
     * @var type 
     */
    protected $validators = array(
        'alnum',
        'alpha',
        'between',
        'charset',
        'cntrl',
        'consonant',
        'contains',
        'digit',
        'endsWith',
        'graph',
        'in',
        'length',
        'lowercase',
        'notEmpty',
        'noWhitespace',
        'prnt',
        'punct',
        'regex',
        'slug',
        'space',
        'startsWith',
        'uppercase',
        'version',
        'vowel',
        'xdigit',
    );
    
    /**
     *
     * @var type 
     */
    protected static $sMessageError = "The value is incorrect.";

    /**
     * 
     * @param type $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->contextCall = 'string';
        $this->sMessageError = self::$sMessageError;
    }

    /**
     * Prepare custom arguments
     */
    protected function prepareArguments()
    {
        $length = array();
        foreach ($this->submittedValidators as $stringValidator) {
            $stringValidator = json_decode($stringValidator, true);
            //$validatorParamsAndValue = explode('=', $stringValidator);

            foreach ($stringValidator as $myKey => $myValue) {
                $length[$myKey] = $myValue;
            }
            /*if ($validatorParamsAndValue[0] == 'minlength' || $validatorParamsAndValue[0] == 'maxlength') {
                $length[$validatorParamsAndValue[0]] = $validatorParamsAndValue[1];
            }*/
        }
        if (isset($length['minlength']) && isset($length['maxlength'])) {
            $this->submittedValidators[] = 'length=' . $length['minlength'] . ',' . $length['maxlength'];
        }
    }
}
