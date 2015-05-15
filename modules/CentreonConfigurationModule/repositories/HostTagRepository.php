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
        if ($action === 'update') {
            AclresourceHosttagRelation::delete($objectId);
            foreach ($hostTagIds as $hostTagId => $hostTagName) {
                AclresourceHostTagRelation::insert($objectId, $hostTagId);
            }
        }
    }

    /**
     * get Host tags by acl id
     *
     * @param int $aclId
     */
    public static function getHostTagByAclResourceId($aclId)
    {
        $hostTagIdList = AclresourceHosttagRelation::getTargetIdFromSourceId(
            'tag_id',
            'acl_resource_id',
            $aclId
        );

        $tagList = Tag::getParameters($hostTagIdList, 'tagname');

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
