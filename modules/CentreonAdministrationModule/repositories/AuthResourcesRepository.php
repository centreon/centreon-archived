<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CentreonAdministration\Repository;

use CentreonMain\Repository\FormRepository;
use CentreonAdministration\Repository\AuthResourcesInfoRepository;
use CentreonAdministration\Repository\AuthResourcesServersRepository;





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
        $id = parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
        self::insertInfos($id,$givenParameters);
        self::insertServer($id,$givenParameters);
        return $id;
    }
    
    public static function insertInfos($id,$givenParameters){
        AuthResourcesInfoRepository::deleteAllForArId($id);
        $auth_infos = $givenParameters['auth_info'];
        foreach($auth_infos as $name=>$auth_info){
            try{
                AuthResourcesInfoRepository::create(array('ar_id' => $id,'ari_name' => $name, 'ari_value' => $auth_info),"","",false);
            } catch (\Exception $e) {
                //parent::delete(array($id));
                throw $e;
            }
        }
    }
    
    public static function insertServer($id,$givenParameters){
        
        $auth_servers = $givenParameters['auth_server'];
        AuthResourcesServersRepository::deleteAllForArId($id);
        $cnt = 0;
        //print_r($auth_servers);
        //die;
            foreach($auth_servers as $key=>$auth_server){
                if(!empty($auth_server['server_address']) ){
                    $use_ssl = 0;
                    if(!empty($auth_server['use_ssl'])){
                        $use_ssl = 1;
                    }
                    
                    $use_tls = 0;
                    if(!empty($auth_server['use_tls'])){
                        $use_tls = 1;
                    }
                    
                    $server_port = null;
                    if(isset($auth_server['server_port'])){
                        $server_port = $auth_server['server_port'];
                    }

                    $server_address = null;
                    if(isset($auth_server['server_address'])){
                        $server_address = $auth_server['server_address'];
                    }
                    
                    try{
                        
                        AuthResourcesServersRepository::create(
                                array('auth_resource_id'=>$id,
                                      'server_address'=>$server_address,
                                      'server_port'=>$server_port,
                                      'use_ssl'=>$use_ssl,
                                      'use_tls'=>$use_tls,
                                      'server_order'=>$cnt
                                ),"","",false
                            );
                    } catch (\Exception $e) {
                        //parent::delete(array($id));
                        throw $e;
                    }
                   $cnt = $cnt + 1;
                }
            }
    }
    
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        parent::update($givenParameters, $origin , $route , false );
        self::insertInfos($givenParameters['object_id'],$givenParameters);
        self::insertServer($givenParameters['object_id'],$givenParameters);
       
    }

}
