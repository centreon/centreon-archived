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

namespace CentreonAdministration\Hooks;

use CentreonAdministration\Repository\TagsRepository;

/**
 * Hook for list the tags
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
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
                $tagList = TagsRepository::getList($params['resourceType'], $id, 2);
                
                $rows[$id] = '';
                foreach ($tagList as $tagId => $tagName) {
                    $rows[$id] .= static::getTag($params['resourceType'], $id, $tagName['id'], $tagName['text'], $tagName['user_id']);
                }
                $rows[$id] .= static::getAddTag($params['resourceType'], $id);
            }
        }
        return array(
            'columnName' => 'Tags',
            'width'      => '150px',
            'max-width'  => '150px',
            'searchable' => true,
            'values'     =>  $rows
        );
    }

    private static function getTag($resourceType, $resourceId, $tagId, $tagName, $iUserId)
    {
        if ($iUserId != '') {
            $sClass = 'tag';
            $sDivRemove = '<div class="remove"><a href="#">&times;</a></div>';
        } else {
            $sClass = 'tagGlobal';
            $sDivRemove = '';
        }
        $html = '<div class="'.$sClass.'" data-resourceid="' . $resourceId . '" data-resourcetype="'
            . $resourceType .'" data-tagid="' . $tagId . '">
            <div class="tagname">' . $tagName . '</div>
            '.$sDivRemove.'
        </div> ';
        return $html;
    }

    private static function getAddTag($resourceType, $resourceId)
    {
        $html = '<div class="tag addtag" data-resourceid="' . $resourceId . '" data-resourcetype="'
            . $resourceType .'">
            <div class="title"><input type="text" style="width: 0;" maxlength="30"></div>
            <div class="remove noborder"><a href="#">+</a></div>
        </div>';
        return $html;
    }
}
