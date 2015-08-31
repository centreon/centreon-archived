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
 * Description of Generator
 *
 * @author lionel
 */
abstract class Generator
{
    /**
     *
     * @var type 
     */
    protected $dbconn;
    
    /**
     *
     * @var string 
     */
    protected $formName = '';
    
    /**
     *
     * @var string 
     */
    protected $formRoute;
    
    /**
     *
     * @var string 
     */
    protected $formRedirect;
    
    /**
     *
     * @var string 
     */
    protected $formRedirectRoute;
    
    /**
     *
     * @var array 
     */
    protected $formComponents = array();
    
    /**
     *
     * @var array 
     */
    protected $formDefaults = array();
    
    /**
     *
     * @var \Centreon\Internal\Form 
     */
    protected $formHandler;
    
    /**
     *
     * @var type 
     */
    protected $firstSection;
    
    /**
     *
     * @var array 
     */
    protected $extraParams;

    /**
     * The product version
     *
     * @var string
     */
    protected $productVersion = '';
    
    /**
     * 
     * @param type $formRoute
     * @param type $extraParams
     * @param type $productVersion
     */
    public function __construct($formRoute, $extraParams = array(), $productVersion = '')
    {
        $this->formRoute = $formRoute;
        $this->extraParams = $extraParams;
        $this->productVersion = $productVersion;
        $di = Di::getDefault();
        $this->dbconn = $di->get('db_centreon');
    }
    
    /**
     * 
     * @return array
     */
    public function getValidationScheme()
    {
        $validatorsScheme = $this->getValidators();
        $validatorsScheme['mandatory'] = $this->getMandatoryFields();
        $validationScheme = array('mandatory' => $validatorsScheme['mandatory'], 'fieldScheme' => $validatorsScheme['fieldScheme']);
        return $validationScheme;
    }
    
    /**
     * 
     * @return type
     */
    public function getMandatoryFields()
    {
        $mandatoryQuery = "SELECT name FROM cfg_forms_fields WHERE mandatory = '1' "
            . "AND field_id IN (
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
                        f.route = :route
            )";
        $stmt = $this->dbconn->prepare($mandatoryQuery);
        $stmt->bindParam(':route', $this->formRoute, \PDO::PARAM_STR);
        $stmt->execute();
        $mandatoryFieldList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_column($mandatoryFieldList, 'name');
    }
}
