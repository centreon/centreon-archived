<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CentreonAdministration\Repository;

use CentreonMain\Repository\FormRepository;
use CentreonAdministration\Repository\AuthResourcesInfoRepository;






/**
 * Description of AuthRessourceRepository
 *
 * @author bsauveton
 */
class AuthResourcesRepository extends FormRepository
{
    public static $objectClass = '\CentreonAdministration\Models\AuthResources';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'auth_resources' => 'cfg_auth_resources,ar_id,ar_name'
        ),
    );
    
    
    /**
     * Host create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        print_r($givenParameters);
        die;
        
        $id = parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
        foreach($givenParameters['auth_info'] as $name=>$auth_info){
            try{
                AuthResourcesInfoRepository::create(array('ar_id' => $id,'ari_name' => $name, 'ari_value' => $auth_info));
            } catch (\Exception $e) {
                parent::delete(array($id));
                throw $e;
                break;
            }
        }
        
        $auth_servers = $givenParameters['auth_server'];
        
        
        if(!empty($auth_servers['host_adresse'])){
            foreach($auth_servers['host_adresse'] as $key=>$host_adresse){
               if($key != "#index#"){
                   
               }
            }
        }
        
        
        return $id;
    }
    
    //put your dick here
}
