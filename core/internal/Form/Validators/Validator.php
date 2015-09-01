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

use Centreon\Internal\Form\Generator\Web\Full;
use Centreon\Internal\Form\Generator\Web\Wizard;
use Centreon\Internal\Form\Generator\Api;
use Centreon\Internal\Form\Generator\Cli;
use Centreon\Internal\Form\Exception\InvalidTokenException;
use Centreon\Internal\Exception;
use Centreon\Internal\Exception\Validator\MissingParameterException;
use Centreon\Internal\Utils\String\CamelCaseTransformation;
use \Centreon\Internal\Exception\Http\BadRequestException;

/**
 * Description of Validator
 *
 * @author lionel
 */
class Validator
{
    /**
     *
     * @var type 
     */
    private $formType;
    
    /**
     *
     * @var type 
     */
    private $formGenerator;
    
    /**
     * 
     * @param type $formType
     * @param type $formInfo
     */
    public function __construct($formType, $formInfo)
    {
        $this->getFormGenerator($formType, $formInfo);
        $this->formType = $formType;
    }
    
    /**
     * 
     * @param type $formType
     * @param type $formInfo
     */
    private function getFormGenerator($formType, $formInfo = array())
    {
        switch(strtolower($formType)) {
            case 'form':
                $this->formGenerator = new Full($formInfo['route'], $formInfo['params'], $formInfo['version']);
                break;
            case 'wizard':
                $this->formGenerator = new Wizard($formInfo['route'], $formInfo['params']);
                break;
            case 'api':
                $this->formGenerator = new Api($formInfo['route'], $formInfo['params'], $formInfo['version']);
                break;
            case 'cli':
                $this->formGenerator = new Cli($formInfo['route'], $formInfo['params'], $formInfo['version']);
                break;
        }
    }
    
    /**
     * 
     * @param type $submittedDatas
     */
    public function validate($submittedDatas, $validateMandatory = true)
    {
        $validationScheme = $this->formGenerator->getValidationScheme(array_keys($submittedDatas));
        $this->validateDatas($validationScheme, $submittedDatas, $validateMandatory);
    }
    
    /**
     * 
     * @param type $validationScheme
     * @param type $submittedDatas
     */
    public function customValidate($validationScheme, $submittedDatas, $validateMandatory = true)
    {
        $this->validateDatas($validationScheme, $submittedDatas, $validateMandatory);
    }
    
    /**
     * 
     * @param type $validationScheme
     * @param type $submittedDatas
     * @throws \Exception
     */
    private function validateDatas($validationScheme, $submittedDatas, $validateMandatory = true)
    {
        $errors = array();
        if ($validateMandatory) {
            // If not all mandatory parameters are in the dataset, throw an exception
            $missingKeys = array();
            foreach ($validationScheme['mandatory'] as $mandatoryField) {
                if (!isset($submittedDatas[$mandatoryField]) || trim($submittedDatas[$mandatoryField]) == "") {
                    $missingKeys[] = $mandatoryField;
                }
            }
            if (count($missingKeys) > 0) {
                $errorMessage = _("The following mandatory parameters are missing") . " :\n    - ";
                $errorMessage .= implode("\n    - ", $missingKeys);
                throw new MissingParameterException($errorMessage);
            }
        }

        $objectParams = array();
        if (isset($submittedDatas['object'])) {
            $objectParams['object'] = $submittedDatas['object'];
        }
        if (isset($submittedDatas['object_id'])) {
            $objectParams['object_id'] = $submittedDatas['object_id'];
        }

        // Validate each field according to its validators
        foreach ($submittedDatas as $key => $value) {
            
            if (isset($validationScheme['fieldScheme'][$key])) {
                
                foreach ($validationScheme['fieldScheme'][$key] as $validatorElement) {
                    
                    // Getting Validator Class to be called
                    $call = $this->parseValidatorName($validatorElement['call']);
                    $validator = new $call($validatorElement['params']);
                    $validatorParams = array_merge($objectParams, json_decode($validatorElement['params'], true));
                    $validatorParams['extraParams'] = $submittedDatas;
                    $labelElement = $key;
                    if (isset($validatorElement['label'])) {
                        $labelElement = $validatorElement['label'];
                    }

                    // Launch validation
                    $result = $validator->validate($value, $validatorParams, $labelElement);

                    //If field is not mandatory and the value is empty ==> when can validate
                    if (!in_array($key, $validationScheme['mandatory'])
                            && $validatorElement['call'] == 'core.String'
                            && strpos($validatorElement['params'], 'minlength')
                            && empty($value)
                            ) {
                            $result['success'] = true;
                    }
                    
                    if ($result['success'] === false) {
                        $errors[] = $result['error'];
                    }
                    
                }
            }
        }
        // If we got error, we throw Exception
        if (count($errors) > 0) {
            $this->raiseValidationException($errors);
        }
    }

    /**
     * 
     * @param type $validatorName
     * @return type
     */
    protected function parseValidatorName($validatorName)
    {
        $call = "";
        $parsedValidator = explode('.', $validatorName);

        if ($parsedValidator[0] === 'core') {
            $call .= '\\Centreon\\Internal\\Form\\Validators\\';
        } else {
            $call .= '\\' . CamelCaseTransformation::customToCamelCase($parsedValidator[0], '-')
                . '\\Forms\\Validators\\';
        }
        
        for ($i = 1; $i < count($parsedValidator); $i++) {
            $call .= ucfirst($parsedValidator[$i]);
        }

        return $call;
    }
    
    /**
     * 
     * @param type $errors
     * @throws \Exception
     */
    private function raiseValidationException($errors)
    {
        throw new BadRequestException('Validation error', $errors);
    }
}
