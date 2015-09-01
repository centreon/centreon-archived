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
namespace Centreon\Internal\Form\Generator;

use Centreon\Internal\Di;

/**
 * Description of Api
 *
 * @author lionel
 */
class Api extends Generator
{
    /**
     * 
     * @param string $formRoute
     * @param array $extraParams
     * @param string $productVersion
     */
    public function __construct($formRoute, $extraParams = array(), $productVersion = '')
    {
        parent::__construct($formRoute, $extraParams, $productVersion);
    }
    
    /**
     * 
     * @return array
     */
    public function getValidators()
    {
        $validatorsQuery = $this->buildValidatorsQuery();
        
        $stmt = $this->dbconn->query($validatorsQuery);
        $validatorsRawList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $validatorsFinalList = array();
        
        foreach ($validatorsRawList as $validator) {
            $validatorsFinalList[$validator['field_name']][] = array(
                'call' => $validator['validator_name'],
                'params' => $validator['params']
            );
        }
        
        return array('fieldScheme' => $validatorsFinalList);
    }
    
    /**
     * 
     */
    protected function buildValidatorsQuery()
    {
        $validatorsQuery = "SELECT
                fv.`name` as validator_name, `route` as `validator`, ffv.`params` as `params`,
                ff.`name` as `field_name`, ff.`label` as `field_label`
            FROM
                cfg_forms_validators fv, cfg_forms_fields_validators_relations ffv, cfg_forms_fields ff
            WHERE
                ffv.validator_id = fv.validator_id
            AND
                ffv.server_side = '1'
            AND
                ff.field_id = ffv.field_id
            AND
                ffv.field_id IN (
                    SELECT
                        fi.field_id
                    FROM
                        cfg_forms_fields fi, cfg_forms_blocks fb, cfg_forms_blocks_fields_relations fbf, cfg_forms_sections fs, cfg_forms f
                    WHERE
                        fi.field_id = fbf.field_id
                    AND
                        fbf.block_id = fb.block_id
                    AND
                        fb.section_id = fs.section_id
                    AND
                        fs.form_id = f.form_id
                    AND
                        f.route = '$this->formRoute'
            );";
        
        return $validatorsQuery;
    }
}
