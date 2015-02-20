<?php

/*
 * Copyright 2005-2014 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace Centreon\Internal\Form\Generator;

/**
 * Description of Generator
 *
 * @author lionel
 */
abstract class Generator
{
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
    private $formRedirect;
    
    /**
     *
     * @var string 
     */
    private $formRedirectRoute;
    
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
    private $firstSection;
    
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
    }
    /**
     * 
     * @return string
     */
    protected function getValidationScheme()
    {
        $mandatory = array('name');
        $fieldScheme = array(
            'name' => array(
                'string' => 'minlength=3,maxlength=255',
                'unique' => 'object=CentreonConfiguration:Host'
            )
        );
        
        $validationScheme = array('mandatory' => $mandatory, 'fieldScheme' => $fieldScheme);
        return $validationScheme;
    }
    
    /**
     * 
     * @param type $datas
     * @throws Exception
     */
    public function validateDatas($datas)
    {
        $success = true;
        $errors = array();
        $validationScheme = $this->getValidationScheme();
        
        // If not all mandatory parameters are in the dataset, throw an exception
        $datasKeys = array_keys($datas);
        if (count(array_diff($datasKeys, $validationScheme['mandatory'])) > 0) {
            throw new \Exception("missing parameters", $code, $previous);
        }
        
        // Validate each field according to its validators
        foreach ($datas as $key => $value) {
            foreach ($validationScheme['fieldScheme'][$key] as $fieldValidatorName => $fieldValidatorParams) {
                $result = $fieldValidatorName::validate($value, $fieldValidatorParams);
                if ($result['success'] === false) {
                    $errors[] = $result['error'];
                }
            }
        }
        
        if (count($errors) > 0) {
            $this->raiseValidationException($errors);
        }
    }
    
    /**
     * 
     * @param type $errors
     * @throws \Exception
     */
    private function raiseValidationException($errors)
    {
        $message = "";
        $code = 401;
        
        throw new \Exception($message, $code);
    }
}
