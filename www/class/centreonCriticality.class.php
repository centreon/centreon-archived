<?php
/*
 * Copyright 2005-2012 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/**
 * Class for managing criticality object
 * 
 * @author Sylvestre Ho 
 */
class CentreonCriticality {
    /**
     * @var CentreonDB
     */
    protected $db;
    /**
     * @var array
     */
    protected $properties;
    
    public function __construct($db) {
        $this->db = $db;
        $this->properties = array('criticality_id', 'name', 'level', 'icon_id', 'comments');
    }
    
    /**
     * Insert new criticality object
     * 
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function insert($params = array()) {
        $sql = "INSERT INTO criticality (name, level, comments, icon_id) VALUES 
                ('".$this->db->escape($params['name'])."', 
                  ".$this->db->escape($params['level']).", 
                 '".$this->db->escape($params['comments'])."',
                  ".$this->db->escape($params['icon_id']).")";
        $this->db->query($sql);        
        $res = $this->db->query("SELECT MAX(criticality_id) as last_id FROM criticality WHERE name = '".$this->db->escape($params['name'])."'");
        if (!$res->numRows()) {
            throw new Exception('Criticality not found');
        }
        $row = $res->fetchRow();
        return $row['last_id'];
    }
    
    /**
     * Update existing criticality object
     * 
     * @param int $criticalityId
     * @param array $params
     * @return void
     */
    public function update($criticalityId, $params = array()) {
        $sql = "UPDATE criticality SET ";
        $first = true;
        foreach ($params as $key => $value) {
            if ($key == 'criticality_id' || !in_array($key, $this->properties)) {
                continue;
            }
            if ($first == false) {
                $sql .= ", ";                
            } else {
                $first = false;
            }
            $sql .= $key . " = '" .$this->db->escape($value)."'";
        }
        $sql .= " WHERE criticality_id = " . $this->db->escape($criticalityId);
        $this->db->query($sql);
    }
    
    /**
     * Delete criticality object
     * 
     * @param array $criticalityIds
     * @return void
     */
    public function delete($criticalityIds = array()) {
        if (count($criticalityIds)) {
            $sql = "DELETE FROM criticality WHERE criticality_id IN (".implode(",", array_keys($criticalityIds)).")";
            $this->db->query($sql);
        }
    }
    
    /**
     * Get list of criticality
     * 
     * @param string $searchString
     * @param string $orderBy
     * @param string $sort
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getList($searchString = null, $orderBy = "level", $sort = 'ASC', $offset = null, $limit = null) {
        $sql = "SELECT criticality_id, name, level, icon_id, comments 
                FROM criticality";
        if (!is_null($searchString) && $searchString != "") {
            $sql .= " WHERE name LIKE '%".$this->db->escape($searchString)."%' ";
        }
        if (!is_null($orderBy) && !is_null($sort)) {
            $sql .= " ORDER BY $orderBy $sort ";
        }
        if (!is_null($offset) && !is_null($limit)) {
            $sql .= " LIMIT $offset,$limit";
        }
        $res = $this->db->query($sql);
        $elements = array();
        while ($row = $res->fetchRow()) {
            $elements[$row['criticality_id']] = array();
            $elements[$row['criticality_id']]['name'] = $row['name'];
            $elements[$row['criticality_id']]['level'] = $row['level'];
            $elements[$row['criticality_id']]['icon_id'] = $row['icon_id'];
            $elements[$row['criticality_id']]['comments'] = $row['comments'];
        }
        return $elements;
    }
    
    /**
     * Get data of a criticality object
     * 
     * @param int $critId 
     * @return array
     */
    public function getData($critId) {
        $sql = "SELECT criticality_id, name, level, icon_id, comments 
                FROM criticality 
                WHERE criticality_id = " . $this->db->escape($critId);
        $res = $this->db->query($sql);
        $row = $res->fetchRow();
        return $row;
    }
    
    /**
     * Get level of a given criticality
     * 
     * @param int $critId
     * @return int
     */
    public function getLevel($critId) {
        static $levels = array();
        
        if (!isset($levels[$critId])) {
            $res = $this->db->query("SELECT criticality_id, level FROM criticality");
            while ($row = $res->fetchRow()) {
                $levels[$row['criticality_id']] = $row['level'];
            }
        }
        if (isset($levels[$critId])) {
            return $levels[$critId];
        }
        return 0;
    }
}