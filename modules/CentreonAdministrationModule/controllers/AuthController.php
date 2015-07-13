<?php

/*
 * Copyright 2005-2015 CENTREON
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
