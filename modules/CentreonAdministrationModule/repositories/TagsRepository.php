<?php
/*
 * Copyright 2005-2014 CENTREON
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
        'hosttemplate',
        'service',
        'hostgroup',
        'servicegroup',
        'ba'
    );

    /**
     * Add a tag to a resource
     *
     */
    public static function add($tagName, $resourceName, $resourceId, $bGlobal = false)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        if ($bGlobal === false) {
            $userId = $_SESSION['user']->getId();
        } else {
            $userId = NULL;
        }
        $dbconn = Di::getDefault()->get('db_centreon');
        /* Get or create a tagname */
        try {
            $tagId = static::getTagId($tagName);
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
     * @param type $resourceId
     * @param type $bGlobaux
     * @return array
     * @throws Exception
     */
    public static function getList($resourceName, $resourceId, $bGlobaux = false)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        $userId = $_SESSION['user']->getId();
        $dbconn = Di::getDefault()->get('db_centreon');        

         $query = "SELECT t.tag_id, t.tagname
                FROM cfg_tags t LEFT JOIN cfg_tags_" . $resourceName . "s r ON t.tag_id = r.tag_id
                WHERE ";
         
        if ($bGlobaux === false) {
            $query .= " t.user_id = :user_id";
        } else {
            $query .= " t.user_id is null ";
        }
         
        if ($resourceId > 0) {
            $query .= " AND r.resource_id = :resource_id";
        }

        
        $stmt = $dbconn->prepare($query);
        
        if ($resourceId > 0) {
            $stmt->bindParam(':resource_id', $resourceId, \PDO::PARAM_INT);
        }
        if ($bGlobaux === false) {
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $tags = array();
        
        while ($row = $stmt->fetch()) {
            if ($bGlobaux === false) {
                $sField = $row['tag_id'];
            } else {
                $sField = $row['tagname'];
            }
            $tags[] = array('id' => $sField, 'text' => $row['tagname']);
        }
        return $tags;
    }

    /**
     * Get the tag id
     *
     * @param string $tagName The tag
     * @return int
     */
    public static function getTagId($tagName)
    {
        $userId = $_SESSION['user']->getId();
        $tag = Tag::getList(
            'tag_id',
            1,
            0,
            null,
            'ASC',
            array(
                'user_id' => $userId,
                'tagname' => $tagName
            ),
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
        
        $aListIdToNotDelete = array();
        /*
        echo "<pre>";
        print_r($submittedValues);
        echo "</pre>";
        die;
          
         */

        foreach ($submittedValues as $s => $tagName) {
            
            self::add($tagName, $resourceName, $objectId, true);
                        
            /*
            if (!empty($sTag)) {
                //array_push($aListIdToNotDelete, $customTag['id']);
                Tag::update($sTag['id'], array('tagname' => $sTag));
            } else {
                self::add($sTag, $resourceName, $objectId, true);
            }
             
             */
        }
/*
        if (($aListIdToNotDelete) > 0) {
            $sListIdToNotDelete = implode(", ",$aListIdToNotDelete);
            
            $stmtTpl = $dbconn->query("DELETE FROM cfg_tags_" . $resourceName . "s WHERE
                     resource_id = ".$objectId." AND tag_id NOT IN (".$sListIdToNotDelete.")");            
            if (!static::isUsed($tagId)) {
                Tag::delete($tagId);
            }
        }
        */
      
    }
}
