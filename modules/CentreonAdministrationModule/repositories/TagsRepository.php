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

namespace CentreonAdministration\Repository;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use CentreonAdministration\Models\Tag;

/**
 * Repository tags
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @package CentreonAdministration
 */
class TagsRepository
{
    /**
     * The list of resource who can have a tag
     * @var array
     */
    private static $resourceType = array(
        'host',
        //'hosttemplate',
        'service',
        //'servicetemplate',
        'ba',
        'contact'
    );

    /**
     * Add a tag to a resource
     *
     */
    public static function add($tagName, $resourceName, $resourceId, $bGlobal = 0)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        if ($bGlobal == 0) {
            $userId = $_SESSION['user']->getId();
        } else {
            $userId = NULL;
        }
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Get or create a tagname */
        try {
            $tagId = static::getTagId($tagName, $bGlobal);
        } catch (Exception $e) {
            $tagId = Tag::insert(
                array(
                    'user_id' => $userId,
                    'tagname' => $tagName
                )
            );
        }
        /* Insert relation tag */
        $query = "INSERT INTO cfg_tags_" . $resourceName . "s (tag_id, resource_id)
            VALUES (:tag_id, :resource_id)";
                
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':tag_id', $tagId, \PDO::PARAM_INT);
        $stmt->bindParam(':resource_id', $resourceId, \PDO::PARAM_INT);
        $stmt->execute();
        return $tagId;
    }

    /**
     * Delete the relation between a tag and a resource
     *
     */
    public static function delete($tagId, $resourceName, $resourceId)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        $dbconn = Di::getDefault()->get('db_centreon');
        
        if (!is_numeric($tagId)) {
            $tagId = static::getTagId($tagId, 1);
        }
        /* Get current user id */
        $query = "DELETE FROM cfg_tags_" . $resourceName . "s WHERE
            tag_id = :tag_id
            AND resource_id = :resource_id";
        
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':tag_id', $tagId, \PDO::PARAM_INT);
        $stmt->bindParam(':resource_id', $resourceId, \PDO::PARAM_INT);
        $stmt->execute();
        /* Get if tag is used */
        if (!static::isUsed($tagId)) {
            Tag::delete($tagId);
        }
        
    }

    /**
     * Return the list of tags for a resource
     * 
     * @param type $resourceName
     * @param int $resourceId
     * @param int $bGlobaux
     * @return array
     * @throws Exception
     */
    public static function getList($resourceName, $resourceId, $bGlobaux = 0)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        if (empty($resourceId)) {
            return array();
        }
        $dbconn = Di::getDefault()->get('db_centreon');        

         $query = "SELECT t.tag_id, t.tagname, user_id
                FROM cfg_tags t LEFT JOIN cfg_tags_" . $resourceName . "s r ON t.tag_id = r.tag_id
                WHERE ";
         
        if ($bGlobaux == 0) {//only tag for user
            $query .= " t.user_id = :user_id";
        } elseif ($bGlobaux == 1) {//only global tag
            $query .= " t.user_id is null ";
        } else {//tag user + global tag
            $query .= " (t.user_id is null or t.user_id = :user_id)";
        }
         
        if ($resourceId > 0) {
            $query .= " AND r.resource_id = :resource_id";
        }

        $query .= " ORDER BY tagname ASC";
        
        $stmt = $dbconn->prepare($query);
        
        if ($resourceId > 0) {
            $stmt->bindParam(':resource_id', $resourceId, \PDO::PARAM_INT);
        }
        if ($bGlobaux == 0 || $bGlobaux == 2) {
            $userId = $_SESSION['user']->getId();
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $tags = array();
        
        //echo $query;
        
        while ($row = $stmt->fetch()) {
            if ($bGlobaux == 0) {
                $sField = $row['tag_id'];
            } else {
                $sField = $row['tagname'];
            }
            $tags[] = array('id' => $sField, 'text' => $row['tagname'], 'user_id' => $row['user_id']);
        }
        return $tags;
    }

    /**
     * Get the tag id
     *
     * @param string $tagName The tag
     * @param int $bGlobal
     * @return int
     */
    public static function getTagId($tagName, $bGlobal = 0)
    {
        if ($bGlobal == 0) {
           $userId = $_SESSION['user']->getId();
           $aFilter = array(
                'user_id' => $userId,
                'tagname' => $tagName
            );
        } else {
            $aFilter = array(
                'tagname' => $tagName
            );
        }
        $tag = Tag::getList(
            'tag_id',
            1,
            0,
            null,
            'ASC',
            $aFilter,
            'AND'
        );
        
        if (count($tag) === 0) {
            throw new Exception("The tag is not found for user");
        }
        return $tag[0]['tag_id'];
    }

    /**
     * Return the list of resource who can have 
     *
     * @return array
     */
    public static function getListResource()
    {
        return static::$resourceType;
    }

    /**
     * Return if a tag is used
     *
     * @param int $tagId The tag id
     * @return bool
     */
    protected static function isUsed($tagId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        foreach (static::$resourceType as $resource) {
            $query = "SELECT COUNT(*) as nb FROM cfg_tags_" . $resource . "s WHERE tag_id = :tag_id";
            $stmt = $dbconn->prepare($query);
            $stmt->bindParam(':tag_id', $tagId, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row['nb'] > 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @param type $objectId
     * @param type $submittedValues
     */
    public static function saveTagsForResource($resourceName, $objectId, $submittedValues)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }

        $dbconn = Di::getDefault()->get('db_centreon');
        $aTagNotDelete = array();
        foreach ($submittedValues as $s => $tagName) {
            if (is_numeric($tagName)) {
                array_push($aTagNotDelete, $tagName);
            }
        }
        if (count($aTagNotDelete) > 0) {
            $sLisTagNotDelete = implode(",", $aTagNotDelete);
        }
        $sQuery = "DELETE FROM cfg_tags_" . $resourceName . "s WHERE resource_id = ".$objectId." "
                . "AND tag_id in (select tag_id from cfg_tags where user_id is null)";
        /*
        if (isset($sLisTagNotDelete)) {
            $sQuery .= " AND tag_id NOT IN (".$sLisTagNotDelete.")";
        }
         */

        $dbconn->query($sQuery);  

        foreach ($submittedValues as $s => $tagName) {
            if (!is_numeric($tagName)) {
                self::add($tagName, $resourceName, $objectId, 1);
            } else {

                $query = "INSERT INTO cfg_tags_" . $resourceName . "s (tag_id, resource_id)
                    VALUES (:tag_id, :resource_id)";

                $stmt = $dbconn->prepare($query);
                $stmt->bindParam(':tag_id', $tagName, \PDO::PARAM_INT);
                $stmt->bindParam(':resource_id', $objectId, \PDO::PARAM_INT);
                $stmt->execute();
            }
        }
      
    }
    public static function getTag($resourceType, $resourceId, $tagId, $tagName, $iUserId)
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

    public static function getAddTag($resourceType, $resourceId)
    {
        $html = '<div class="tag addtag" data-resourceid="' . $resourceId . '" data-resourcetype="'
            . $resourceType .'">
            <div class="title"><input type="text" style="width: 0;"></div>
            <div class="remove noborder"><a href="#">+</a></div>
        </div>';
        return $html;
    }
    
    
    /**
     * Return the list of tags for all resources
     * @param string $sSearch String to search
     * @param int $sType Type of search
     * @return array
     * @throws Exception
     */
    public static function getAllList($sSearch, $sType = 1)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $userId = $_SESSION['user']->getId();
        
        $sSearch = trim($sSearch);
        
        if ($sType == 1) {
            $query = "SELECT tag_id, tagname FROM cfg_tags where user_id is null ".(!empty($sSearch) ? ' AND tagname LIKE "%'.$sSearch.'%"' : '')." ORDER BY tagname ASC ";   
        } else {
            $query = "SELECT tag_id, tagname FROM cfg_tags where user_id =".$userId." ".(!empty($sSearch) ? ' AND tagname LIKE "%'.$sSearch.'%"' : '')." ORDER BY tagname ASC ";   
        }
        
       
        $stmt = $dbconn->prepare($query);

        $stmt->execute();
        $tags = array();
        
        while ($row = $stmt->fetch()) {
            $tags[] = array('id' => $row['tag_id'], 'text' => $row['tagname']);
        }
        return $tags;
    }
    
    
    /**
     * Delete the global tags
     * @param type $aDatas
     * @return type
     */
    public static function deleteGlobal($aDatas)
    {

        $dbconn = Di::getDefault()->get('db_centreon');
        
        if (!is_array($aDatas) || count($aDatas) == 0) {
            return;
        }
        
        $sIdTags = implode(",", $aDatas);
                
        foreach (static::$resourceType as $resource) {
            $sQuery = "DELETE FROM cfg_tags_" . $resource . "s WHERE tag_id IN (".$sIdTags.")";
            $oSmt = $dbconn->prepare($sQuery);
            $oSmt->execute();
        }
        
        $sQueryDelete = "DELETE FROM cfg_tags WHERE tag_id IN (".$sIdTags.")";
        $oSmtDelete = $dbconn->prepare($sQueryDelete);
        $oSmtDelete->execute();      
    }
    /**
     * 
     * @param type $tagId
     * @param type $tagName
     * @return type
     */
    public static function update($tagId, $tagName)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        if (empty($tagId)|| empty($tagName)) {
            return;
        }
        
        $query = "UPDATE cfg_tags SET tagname = :tagname WHERE tag_id = :tag_id";

        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':tag_id', $tagId, \PDO::PARAM_INT);
        $stmt->bindParam(':tagname', $tagName, \PDO::PARAM_STR);
        $stmt->execute();

    }
    
    /**
     * Get the tag id
     * @param string $tagName The tag
     * @return int
     */
    public static function isExist($tagName)
    {
        if (empty($tagName)) {
            return;
        }
        
        $aFilter = array(
            'tagname' => $tagName
        );

        $tag = Tag::getList(
            'tag_id',
            1,
            0,
            null,
            'ASC',
            $aFilter,
            'AND'
        );
        if (isset($tag[0]['tag_id'])) {
            $iReturn = $tag[0]['tag_id'];
        } else {
            $iReturn = -1;
        }
        return $iReturn;
    }
    
}
