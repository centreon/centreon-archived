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
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get data of a criticality object
     * 
     * @param int $critId 
     * @return array
     */
    public function getData($critId, $service = false) {
        if ($service === false) {
            return $this->getDataForHosts($critId);
        }
        return $this->getDataForServices($critId);
    }

    /**
     * Get data of a criticality object for hosts
     * 
     * @param int $critId 
     * @return array
     */
    public function getDataForHosts($critId) {
        static $data = array();
        
        if (!isset($data[$critId])) {
            $sql = "SELECT hc_id, hc_name, level, icon_id, hc_comment
                    FROM hostcategories 
                    WHERE level IS NOT NULL
                    ORDER BY level DESC";
            $res = $this->db->query($sql);
            while ($row = $res->fetchRow()) {
                if (!isset($data[$row['hc_id']])) {
                    $row['name'] = $row['hc_name'];
                    $data[$row['hc_id']] = $row;
                }                
            }            
        }
        if (isset($data[$critId])) {
            return $data[$critId];
        }
        return null;
    }
    
    /**
     * Get data of a criticality object for services
     * 
     * @param int $critId 
     * @return array
     */
    public function getDataForServices($critId) {
        static $data = array();
        
        if (!isset($data[$critId])) {
            $sql = "SELECT sc_id, sc_name, level, icon_id, sc_description
                    FROM service_categories 
                    WHERE level IS NOT NULL
                    ORDER BY level DESC";
            $res = $this->db->query($sql);
            while ($row = $res->fetchRow()) {
                if (!isset($data[$row['sc_id']])) {
                    $row['name'] = $row['sc_name'];
                    $data[$row['sc_id']] = $row;
                }                
            }            
        }
        if (isset($data[$critId])) {
            return $data[$critId];
        }
        return null;
    }
    
    /**
     * Get list of criticality
     * 
     * @param string $searchString
     * @param string $orderBy
     * @param string $sort
     * @param int $offset
     * @param int $limit
     * @paaram bool $service
     * @return array
     */
    public function getList($searchString = null, $orderBy = "level", $sort = 'ASC', $offset = null, $limit = null, $service = false) {
        if ($service === false) {
            $elements = $this->getListForHosts(
                    $searchString, 
                    $orderBy, 
                    $sort, 
                    $offset, 
                    $limit
                    );
        } else {
            $elements = $this->getListForServices(
                    $searchString, 
                    $orderBy, 
                    $sort, 
                    $offset, 
                    $limit
                    );
        }
        return $elements;
    }
    
    /**
     * Get list of host criticalities
     * 
     * @param type $searchString
     * @param type $orderBy
     * @param type $sort
     * @param type $offset
     * @param type $limit
     * @return type
     */
    protected function getListForHosts($searchString = null, $orderBy = "level", $sort = 'ASC', $offset = null, $limit = null) {
        $sql = "SELECT hc_id, hc_name, level, icon_id, hc_comment
                FROM hostcategories 
                WHERE level IS NOT NULL ";
        if (!is_null($searchString) && $searchString != "") {
            $sql .= " AND hc_name LIKE '%".$this->db->escape($searchString)."%' ";
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
            $elements[$row['hc_id']] = array();
            $elements[$row['hc_id']]['hc_name'] = $row['hc_name'];
            $elements[$row['hc_id']]['level'] = $row['level'];
            $elements[$row['hc_id']]['icon_id'] = $row['icon_id'];
            $elements[$row['hc_id']]['comments'] = $row['hc_comment'];
        }
        return $elements;
    }
    
    /**
     * Get list of service criticalities
     * 
     * @param type $searchString
     * @param type $orderBy
     * @param type $sort
     * @param type $offset
     * @param type $limit
     * @return type
     */
    protected function getListForServices($searchString = null, $orderBy = "level", $sort = 'ASC', $offset = null, $limit = null) {
        $sql = "SELECT sc_id, sc_name, level, icon_id, sc_description
                FROM service_categories 
                WHERE level IS NOT NULL ";
        if (!is_null($searchString) && $searchString != "") {
            $sql .= " AND sc_name LIKE '%".$this->db->escape($searchString)."%' ";
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
            $elements[$row['sc_id']] = array();
            $elements[$row['sc_id']]['sc_name'] = $row['sc_name'];
            $elements[$row['sc_id']]['level'] = $row['level'];
            $elements[$row['sc_id']]['icon_id'] = $row['icon_id'];
            $elements[$row['sc_id']]['description'] = $row['sc_description'];
        }
        return $elements;
    }
}
