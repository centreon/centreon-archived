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

namespace CentreonAdministration\Repository;

use CentreonMain\Repository\FormRepository;
/**
 * Description of AuthResourceInforepository
 *
 * @author bsauveton
 */
class AuthResourcesInfoRepository extends FormRepository
{
    
    public static $objectClass = '\CentreonAdministration\Models\AuthResourcesInfo';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'auth_resources_info' => 'cfg_auth_resources_info,ar_id,ari_name'
        ),
    );
    
    
    /**
     * 
     * @param array $givenParameters
     */
    public static function create($givenParameters){
        $curObj = static::$objectClass;
        $curObj::create($givenParameters);
    }
    
    /**
     * 
     * @param int $id
     */
    public static function deleteAllForArId($id){
        $curObj = static::$objectClass;
        $curObj::deleteAllForArId($id);
    }
    
    
    /**
     * 
     * @param string $name
     * @param int $id
     */
    public static function getInfosFromName($name,$id){
        $curObj = static::$objectClass;
        return $curObj::getInfosFromName($name,$id);
    }
    
    
    //put your code here
}
