<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonAdministration\Hooks;

use \CentreonAdministration\Repository\TagsRepository;

/**
 * Hook for list the tags
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonAdministrition
 */
class DisplayTagList
{
    /**
     * Execute the hook
     *
     * @param array The parameters for the hook
     */
    public static function execute($params)
    {
        $rows = array();
        if (isset($params['_ids'])) {
            foreach ($params['_ids'] as $id) {
                $tagList = TagsRepository::getList($params['resourceType'], $id);
                $rows[$id] = '';
                foreach ($tagList as $tagId => $tagName) {
                    $rows[$id] .= static::getTag($params['resourceType'], $id, $tagId, $tagName);
                }
                $rows[$id] .= static::getAddTag($params['resourceType'], $id);
            }
        }
        return array(
            'columnName' => 'Tags',
            'values' => $rows
        );
    }

    private static function getTag($resourceType, $resourceId, $tagId, $tagName)
    {
        $html = '<div class="tag" data-resourceid="' . $resourceId . '" data-resourcetype="'  . $resourceType .'" data-tagid="' . $tagId . '">
            <div class="title">' . $tagName . '</div>
            <div class="remove"><a href="#">&times;</a></div>
        </div> ';
        return $html;
    }

    private static function getAddTag($resourceType, $resourceId)
    {
        $html = '<div class="tag addtag" data-resourceid="' . $resourceId . '" data-resourcetype="'  . $resourceType .'">
            <div class="title"><input type="text" style="width: 0;"></div>
            <div class="remove noborder"><a href="#">+</a></div>
        </div>';
        return $html;
    }
}
