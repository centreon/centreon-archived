<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
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
    public function getServiceService($withSg = false)
    {
        $query = 'SELECT dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id,
            dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
        	FROM dependency_serviceParent_relation dsp, dependency_serviceChild_relation dsc
        	WHERE dsp.dependency_dep_id = dsc.dependency_dep_id';
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServices = array();
        while ($row = $res->fetchRow()) {
            $listServices[$row['parent_host_id'] . ";" . $row['parent_service_id'] . ";" . $row['child_host_id'] . ";" . $row['child_service_id']] = $row;
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
                        $listServices[$sgp[0] . ";" . $sgp[1] . ";" . $sgc[0] . ";" . $sgc[1]] = array(
                            'parent_host_id' => $sgp[0],
                            'parent_service_id' => $sgp[1],
                            'child_host_id' => $sgc[0],
                            'child_service_id' => $sgc[1]
                        );
                    }
                }
            }
        }
        return $listServices;
    }

    /**
     * Get the host host dependency
     *
     * @param bool $withHg If use hostgroup relation
     * @return array
     */
    public function getHostHost($withHg = false)
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
            $listHosts[$row['parent_host_id'] . ';' . $row['child_host_id']] = $row;
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
                        $listHosts[$hgp . ";" . $hgc] = array('parent_host_id' => $hgp,
                                             'child_host_id' => $hgc);
                    }
                }
            }
        }
        return $listHosts;
    }

    /**
     * Parent Host
     * Dependent Service
     *
     * @return array
     */
    public function getHostService()
    {
        $query = "SELECT dhp.host_host_id as parent_host_id, dsc.host_host_id as child_host_id,
            dsc.service_service_id as child_service_id
        	FROM dependency_hostParent_relation dhp, dependency_serviceChild_relation dsc
            WHERE dhp.dependency_dep_id = dsc.dependency_dep_id";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listHostService = array();
        while ($row = $res->fetchRow()) {
            $listHostService[] = $row;
        }
        return $listHostService;
    }

    /**
     * Parent Service
     * Dependent Host
     *
     * @return array
     */
    public function getServiceHost()
    {
        $query = "SELECT dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id,
            dhc.host_host_id as child_host_id
            FROM dependency_serviceParent_relation dsp, dependency_hostChild_relation dhc
            WHERE dsp.dependency_dep_id = dhc.dependency_dep_id";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $listServiceHost = array();
        while ($row = $res->fetchRow()) {
            $listServiceHost[] = $row;
        }
        return $listServiceHost;
    }

    /**
     * Purge obsolete dependencies that are no longer used
     *
     * @param CentreonDB $db
     */
    public static function purgeObsoleteDependencies($db)
    {
        $sql = "DELETE FROM dependency WHERE dep_id NOT IN (
            SELECT DISTINCT dep.dependency_dep_id from (
                SELECT dependency_dep_id FROM dependency_hostChild_relation
                UNION
                SELECT dependency_dep_id FROM dependency_hostParent_relation
                UNION
                SELECT dependency_dep_id FROM dependency_hostgroupChild_relation
                UNION
                SELECT dependency_dep_id FROM dependency_hostgroupParent_relation
                UNION
                SELECT dependency_dep_id FROM dependency_metaserviceChild_relation
                union
                SELECT dependency_dep_id FROM dependency_metaserviceParent_relation
                union
                SELECT dependency_dep_id FROM dependency_serviceChild_relation
                union
                SELECT dependency_dep_id FROM dependency_serviceParent_relation
                union
                SELECT dependency_dep_id FROM dependency_servicegroupChild_relation
                union
                SELECT dependency_dep_id FROM dependency_servicegroupParent_relation
            ) dep
        )";
        $db->query($sql);
    }
    
    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'host';
        $parameters['currentObject']['id'] = 'host_id';
        $parameters['currentObject']['name'] = 'host_name';
        $parameters['currentObject']['comparator'] = 'host_id';

        switch ($field) {
            case 'dep_hostParents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'dependency_hostParent_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_hostChilds':
            case 'dep_hHostChi':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'dependency_hostChild_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_hSvPar':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['relationObject']['table'] = 'dependency_serviceParent_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['additionalField'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_hSvChi':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['relationObject']['table'] = 'dependency_serviceChild_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['additionalField'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_hgParents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHostgroups';
                $parameters['externalObject']['table'] = 'hostgroup';
                $parameters['externalObject']['id'] = 'hg_id';
                $parameters['externalObject']['name'] = 'hg_name';
                $parameters['externalObject']['comparator'] = 'hg_id';
                $parameters['relationObject']['table'] = 'dependency_hostgroupParent_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_hgChilds':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHostgroups';
                $parameters['externalObject']['table'] = 'hostgroup';
                $parameters['externalObject']['id'] = 'hg_id';
                $parameters['externalObject']['name'] = 'hg_name';
                $parameters['externalObject']['comparator'] = 'hg_id';
                $parameters['relationObject']['table'] = 'dependency_hostgroupChild_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_sgParents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicegroups';
                $parameters['externalObject']['table'] = 'servicegroup';
                $parameters['externalObject']['id'] = 'sg_id';
                $parameters['externalObject']['name'] = 'sg_name';
                $parameters['externalObject']['comparator'] = 'sg_id';
                $parameters['relationObject']['table'] = 'dependency_servicegroupParent_relation';
                $parameters['relationObject']['field'] = 'servicegroup_sg_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_sgChilds':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicegroups';
                $parameters['externalObject']['table'] = 'servicegroup';
                $parameters['externalObject']['id'] = 'sg_id';
                $parameters['externalObject']['name'] = 'sg_name';
                $parameters['externalObject']['comparator'] = 'sg_id';
                $parameters['relationObject']['table'] = 'dependency_servicegroupChild_relation';
                $parameters['relationObject']['field'] = 'servicegroup_sg_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_msParents':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonMeta';
                $parameters['externalObject']['table'] = 'meta_service';
                $parameters['externalObject']['id'] = 'meta_id';
                $parameters['externalObject']['name'] = 'meta_name';
                $parameters['externalObject']['comparator'] = 'meta_id';
                $parameters['relationObject']['table'] = 'dependency_metaserviceParent_relation';
                $parameters['relationObject']['field'] = 'meta_service_meta_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
            case 'dep_msChilds':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonMeta';
                $parameters['externalObject']['table'] = 'meta_service';
                $parameters['externalObject']['id'] = 'meta_id';
                $parameters['externalObject']['name'] = 'meta_name';
                $parameters['externalObject']['comparator'] = 'meta_id';
                $parameters['relationObject']['table'] = 'dependency_metaserviceChild_relation';
                $parameters['relationObject']['field'] = 'meta_service_meta_id';
                $parameters['relationObject']['comparator'] = 'dependency_dep_id';
                break;
        }
        
        return $parameters;
    }
}
