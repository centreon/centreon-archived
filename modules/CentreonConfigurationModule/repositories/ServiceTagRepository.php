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

use CentreonConfiguration\Repository\Repository;
use CentreonConfiguration\Models\Relation\Aclresource\Servicetag as AclresourceServicetagRelation;
use CentreonAdministration\Models\Tag;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceTagRepository extends Repository
{
    public static $objectClass = '\CentreonConfiguration\Models\Servicetag';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'service' => 'cfg_tags_services,tag_id'
        ),
    );

    /**
     * update Service tag acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $serviceTagId
     */
    public static function updateServiceTagAcl($action, $objectId, $serviceTagIds)
    {
        if (($action === 'create') || ($action === 'update')) {
            AclresourceServicetagRelation::delete($objectId);
            foreach ($serviceTagIds as $serviceTagId) {
                AclresourceServicetagRelation::insert($objectId, $serviceTagId);
            }
        }
    }

    /**
     * get Service tags by acl id
     *
     * @param int $aclId
     */
    public static function getServiceTagsByAclResourceId($aclId)
    {
        $serviceTagIdList = AclresourceServicetagRelation::getTargetIdFromSourceId(
            'tag_id',
            'acl_resource_id',
            $aclId
        );

        $tagList = array();
        if (count($serviceTagIdList) > 0) {
            $tagList = Tag::getParameters($serviceTagIdList, 'tagname');
        }

        $finalTagList = array();
        foreach ($tagList as $tag) {
            $finalTagList[] = array(
                "id" => $tag['tag_id'],
                "text" => $tag['tagname']
            );
        }

        return $finalTagList;
    }
}
