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
use CentreonConfiguration\Models\Relation\Aclresource\Hosttag as AclresourceHosttagRelation;
use CentreonAdministration\Models\Tag;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostTagRepository extends Repository
{
    public static $objectClass = '\CentreonConfiguration\Models\Hosttag';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'host' => 'cfg_tags_hosts,tag_id'
        ),
    );

    /**
     * update Host tag acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $hostTagId
     */
    public static function updateHostTagAcl($action, $objectId, $hostTagIds)
    {
        if (($action === 'create') || ($action === 'update')) {
            AclresourceHosttagRelation::delete($objectId);
            foreach ($hostTagIds as $hostTagId) {
                AclresourceHosttagRelation::insert($objectId, $hostTagId);
            }
        }
    }

    /**
     * get Host tags by acl id
     *
     * @param int $aclId
     */
    public static function getHostTagsByAclResourceId($aclId)
    {
        $hostTagIdList = AclresourceHosttagRelation::getTargetIdFromSourceId(
            'tag_id',
            'acl_resource_id',
            $aclId
        );

        $tagList = array();
        if (count($hostTagIdList) > 0) {
            $tagList = Tag::getParameters($hostTagIdList, 'tagname');
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
