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
use CentreonAdministration\Repository\AuthResourcesInfoRepository;
use Centreon\Internal\Utils\String\CamelCaseTransformation;
/**
 * Description of LdapController
 *
 * @author bsauveton
 */
class AuthController extends FormController{
    
    protected $objectDisplayName = 'Authentication';
    public static $objectName = 'auth';
    public static $enableDisableFieldName = 'ar_enable';
    protected $datatableObject = '\CentreonAdministration\Internal\AuthDatatable';
    protected $objectBaseUrl = '/centreon-administration/auth';
    protected $objectClass = '\CentreonAdministration\Models\AuthResources';
    protected $repository = '\CentreonAdministration\Repository\AuthResourcesRepository';
    public static $authInfosFields = array('ldap_contact_tmpl','protocol_version','ldap_template');

    
    public static $relationMap = array(

    );
    
    public static $isDisableable = true;
    
    /**
     * 
     * @method get
     * @route /{object}/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array(), $defaultValues = array())
    {
        
        $requestParam = $this->getParams('named');
        $auth_id = $requestParam['id'];
        $infos = AuthResourcesInfoRepository::getList(
            $fields = '*',
            $count = -1,
            $offset = 0,
            $order = null,
            $sort = 'asc',
            $filters = array('ar_id' => $auth_id)
        );

        foreach($infos as $info){
            $defaultValues['auth_info['.$info["ari_name"].']'] = $info["ari_value"];
        }
        
        parent::editAction($additionnalParamsForSmarty,$defaultValues);
    }
    
    
    /**
     * 
     * @method get
     * @route /auth/[i:id]/[a:name]
     */
    public function getDefaultAuthValuesAction(){
        $requestParam = $this->getParams('named');
        $auth_id = $requestParam['id'];
        $param_name = strtolower(CamelCaseTransformation::camelCaseToCustom($requestParam['name'],'_'));
        $data = array();
        if(in_array($param_name, self::$authInfosFields)){
            $contact_template = AuthResourcesInfoRepository::getInfosFromName($param_name,$auth_id);
            if(!empty($contact_template)){
                $data = array('id' => $contact_template['ar_id'], 'text' => $contact_template['ari_value']);
            }
        }
        return $this->router->response()->json($data);
        
    }
    
    /**
     * 
     * @method get
     * @route /auth/[i:id]/contactTemplate/listValues
     */
    public function getContactTemplateListValuesAction(){
        $data = array('id' => '', 'text' => '');
        return $this->router->response()->json($data);
        
    }
    
    
    
    
    
    
    
    //put your code here
}
