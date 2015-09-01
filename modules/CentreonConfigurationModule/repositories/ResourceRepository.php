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

namespace CentreonConfiguration\Repository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ResourceRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_resources';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Resource';
    
    public static $objectClass = '\CentreonConfiguration\Models\Resource';
    
    
    /**
     *
     * @var type 
     */   
    public static $unicityFields = array(
        'fields' => array(
            'resources' => 'cfg_resources, resource_id, resource_name',
            'poller' => 'cfg_pollers, poller_id, cfg_pollers.name',
            ),
        'joint' => 'cfg_resources_instances_relations',
        'jointCondition' => 'cfg_resources.resource_id = cfg_resources_instances_relations.resource_id AND cfg_pollers.poller_id = cfg_resources_instances_relations.instance_id'
    );
    
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     */ 
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $id = parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
        return $id;
    }
    
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     */
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {       
        parent::update($givenParameters, $origin, $route, $validateMandatory);
    }
}
