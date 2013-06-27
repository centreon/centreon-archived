<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 * @author Sylvestre Ho <sho@merethis.com>
 */
class CentreonMeta
{
    protected $db;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Return host id
     * 
     * @return int
     */
    public function getRealHostId() {
        static $hostId = null;
        
        if (is_null($hostId)) {
            $sql = "SELECT host_id 
                FROM host 
                WHERE host_name = '_Module_Meta' 
                LIMIT 1";
            $res = $this->db->query($sql);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $hostId = $row['host_id'];
            } else {
                $hostId = 0;
            }
        }
        return $hostId;
    }
    
    /**
     * Return service id
     * 
     * @param int $metaId
     * @return int
     */
    public function getRealServiceId($metaId) {
        static $services = null;
        
        if (is_null($services)) {
            $sql = "SELECT s.service_id, s.service_description 
                FROM service s, host_service_relation hsr
                WHERE s.service_id = hsr.service_service_id
                AND hsr.host_host_id = {$this->getRealHostId()}";
            $res = $this->db->query($sql);
            if ($res->numRows()) {
                while ($row = $res->fetchRow()) {
                    if (preg_match('/meta_(\d+)/', $row['service_description'], $matches)) {
                        $services[$matches[1]] = $row['service_id'];
                    }
                }
            }
        }
        if (isset($services[$metaId])) {
            return $services[$metaId];
        }
        return 0;
    }
}