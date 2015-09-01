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

namespace CentreonConfiguration\Forms\Validators;

use Centreon\Internal\Form\Validators\ValidatorInterface;
use Centreon\Internal\Exception;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class TemplateField implements ValidatorInterface
{
    /**
     * validate custom field for a template
     *
     * @param string $value The field value
     * @param array $params The field params
     */
    public function validate($value, $params = array())
    {
        self::validateMandatory($value, $params);
    }

    /**
     * validate mandatory custom field for a template
     *
     * @param string $value The field value
     * @param array $params The field params
     */
    private function validateMandatory($value, $params = array())
    {
        $resultError = _($params['name'] . ' is mandatory');

        if ($params['require'] && (!isset($value) || trim($value) == "")) {
            throw new Exception($resultError, 255);
        }
    }
}
