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

namespace CentreonBam\Repository;

use CentreonMain\Repository\FormRepository;
use CentreonBam\Models\Relation\Aclresource\BusinessActivityTag as AclresourceBusinessactivitytagRelation;
use CentreonAdministration\Models\Tag;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class BusinessActivityTagRepository extends FormRepository
{
    public static $objectClass = '\CentreonBam\Models\BusinessActivityTag';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'host' => 'cfg_tags_bas,tag_id'
        ),
    );

    /**
     * update Business activity tag acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $baTagIds
     */
    public static function updateBusinessActivityTagAcl($action, $objectId, $baTagIds)
    {
        if ($action === 'update') {
            AclresourceBusinessactivitytagRelation::delete($objectId);
            foreach ($baTagIds as $baTagId) {
                AclresourceBusinessactivitytagRelation::insert($objectId, $baTagId);
            }
        }
    }

    /**
     * get Business activity tags by acl id
     *
     * @param int $aclId
     */
    public static function getBusinessActivityTagsByAclResourceId($aclId)
    {
        $baTagIdList = AclresourceBusinessactivitytagRelation::getTargetIdFromSourceId(
            'tag_id',
            'acl_resource_id',
            $aclId
        );

        $tagList = array();
        if (count($baTagIdList) > 0) {
            $tagList = Tag::getParameters($baTagIdList, 'tagname');
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
