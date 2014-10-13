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

namespace CentreonAdministration\Repository;

use \Centreon\Internal\Exception;
use \Centreon\Internal\Di;
use \CentreonAdministration\Models\Tag;

/**
 * Repository tags
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
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
        'service',
        'hostgroup',
        'servicegroup',
        'ba'
    );

    /**
     * Add a tag to a resource
     *
     */
    public static function add($tagName, $resourceName, $resourceId)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        $userId = $_SESSION['user']->getId();
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
     * @return array
     */
    public static function getList($resourceName, $resourceId)
    {
        if (!in_array($resourceName, static::$resourceType)) {
            throw new Exception("This resource type does not support tags.");
        }
        $userId = $_SESSION['user']->getId();
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT t.tag_id, t.tagname
            FROM cfg_tags t, cfg_tags_" . $resourceName . "s r
            WHERE t.tag_id = r.tag_id
                AND r.resource_id = :resource_id
                AND t.user_id = :user_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':resource_id', $resourceId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $tags = array();
        while ($row = $stmt->fetch()) {
            $tags[$row['tag_id']] = $row['tagname'];
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
}
