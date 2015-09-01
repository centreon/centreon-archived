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

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationExceptionInterface;
/**
 * Description of RespectValidationAbstract
 *
 * @author lionel
 */
abstract class RespectValidationAbstract implements ValidatorInterface
{
    /**
     *
     * @var type 
     */
    protected $validators = array();
    
    /**
     *
     * @var type 
     */
    protected $submittedValidators;
    
    /**
     *
     */
    protected $contextCall = '';
    
    
    protected static $sMessageError = "";


    /**
     * 
     * @param type $params
     */
    protected function __construct($params)
    {
        $this->submittedValidators = explode(';', $params);
        $this->prepareArguments();
    }

    /**
     * 
     * @param type $value
     * @return type
     */
    public function validate($value, $params = array(), $label = "")
    {
        $validators = $this->buildValidationChain();
        $callStr = $this->contextCall;
        
        $obj = \Respect\Validation\Validator::$callStr();
        foreach ($validators as $func => $param) {
            $obj = call_user_func_array(array($obj, $func), $param);
        }
        


        $errorMessage = "";
        $response = false;
        $errors = array();
        try{
            $obj->setName($label)->assert($value);
            $response = true;
        } catch (NestedValidationExceptionInterface $exception) {
            $errors = $exception->findMessages(array_keys($validators));
            foreach($errors as $error){
                $errorMessage .= $error.'<br/>';
            }
            
            
            //$errors = $exception->setName($label)->getFullMessage();
        }
        
        
          
        if ($response) {
            $result = array('success' => true);
        } else {
            $result = array(
                'success' => false,
                'error' => $errorMessage
            );
        }

        return $result;
    }

    /**
     * 
     * @return string
     */
    protected function buildValidationChain()
    {
        $validationChainCall = array();
        foreach ($this->submittedValidators as $stringValidator) {
            $validatorParamsAndValue = explode('=', $stringValidator);
            if (in_array($validatorParamsAndValue[0], $this->validators)) {
                $validationChainCall[$validatorParamsAndValue[0]] = explode(',' ,$validatorParamsAndValue[1]);
            }
        }
        return $validationChainCall;
    }
    
    /**
     * Prepare custom arguments
     */
    protected function prepareArguments()
    {
    }
}
