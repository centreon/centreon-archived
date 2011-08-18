<?php
/*
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
 * SVN : $URL: $
 * SVN : $Id: $
 *
 */

 /*
  *  Class for manage dependencies
  */
class CentreonDependency
{
    protected $db = null;
    
    /**
 	 * Constructor
 	 *
 	 * @param CentreonDB $db
 	 */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Get the service service dependency
     * 
     * @param bool $withSg If use servicegroup relation
     * @return array
     */
    public function getServiceService($withSg = False)
    {
        $query = 'SELECT dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
        	FROM dependency_serviceParent_relation dsp, dependency_serviceChild_relation dsc
        	WHERE dsp.dependency_dep_id = dsc.dependency_dep_id';
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[] = $row;
        }
        if ($withSg) {
            $querySg = 'SELECT dsgp.servicegroup_sg_id as parent_sg, dsgc.servicegroup_sg_id as child_sg
            	FROM dependency_servicegroupParent_relation dsgp, dependency_servicegroupChild_relation dsgc 
            	WHERE dsgp.dependency_dep_id = dsgc.dependency_dep_id';
            $res = $this->db->query($querySg);
            if (PEAR::isError($res)) {
                return $listServices;
            }
            $sgObj = new CentreonServicegroups($this->db);
            while ($row = $res->fetchRow()) {
                $sgps = $sgObj->getServiceGroupServices($row['parent_sg']);
                $sgcs = $sgObj->getServiceGroupServices($row['child_sg']);
                foreach ($sgps as $sgp) {
                    foreach ($sgcs as $sgc) {
                        $listServices[] = array('parent_host_id' => $sgp[0],
                                                'parent_service_id' => $sgp[1],
                                                'child_host_id' => $sgc[0],
                                                'child_service_id' => $sgc[1]);
                    }
                }
            }
        }
        return array_unique($listServices);
    }
    
    /**
     * Get the host host dependency
     * 
     * @param bool $withHg If use hostgroup relation
     * @return array
     */
    public function getHostHost($withHg = False)
    {
        $query = 'SELECT dhp.host_host_id as parent_host_id, dhc.host_host_id as child_host_id
        	FROM dependency_hostParent_relation dhp, dependency_hostChild_relation dhc
        	WHERE dhp.dependency_dep_id = dhc.dependency_dep_id';
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHosts = array();
        while ($row = $res->fetchRow()) {
            $listHosts[] = $row;
        }
        if ($withHg) {
            $queryHg = 'SELECT dhgp.hostgroup_hg_id as parent_hg, dhgc.hostgroup_hg_id as child_hg 
            	FROM dependency_hostgroupParent_relation dhgp, dependency_hostgroupChild_relation dhgc
            	WHERE dhgp.dependency_dep_id = dhgc.dependency_dep_id';
            $res = $this->db->query($queryHg);
            if (PEAR::isError($res)) {
                return $listHosts;
            }
            $hgObj = new CentreonHostgroups($this->db);
            while ($row = $res->fetchRow()) {
                $hgps = $hgObj->getHostGroupHosts($row['parent_hg']);
                $hgcs = $hgObj->getHostGroupHosts($row['child_hg']);
                foreach ($hgps as $hgp) {
                    foreach ($hgcs as $hgc) {
                        $listHosts[] = array('parent_host_id' => $hgp,
                                             'child_host_id' => $hgc);
                    }
                }
            }
        }
        return array_unique($listHosts);
    }
}
 ?>