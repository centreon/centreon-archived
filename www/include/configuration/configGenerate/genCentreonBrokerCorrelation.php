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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

    require_once $centreon_path . '/www/class/centreonHost.class.php';
    require_once $centreon_path . '/www/class/centreonServicegroups.class.php';
    require_once $centreon_path . '/www/class/centreonDependency.class.php';

    function getHostFormHg($db, $hgId, $instanceId=null) {
        $hostList = array();
	$query = "SELECT hgr.host_host_id
	     FROM hostgroup_relation hgr, 
	     WHERE hgr.hostgroup_hg_id = " . $hgId;
        if (!is_null($instanceId)) {
	    $query .= " AND nhr.host_host_id = hgr.host_host_id
                AND nhr.nagios_server_id = " . $instanceId;
        }
	$res = $db->query($query);
	if (PEAR::isError($res)) {
            return array();
        }
	while ($row = $res->fetchRow()) {
	   $hostList[] = $row['host_host_id'];
        }
        $res->free();
	return $hostList;
    }

    function getServiceFromSg($db, $sgId, $instanceId=null) {
        $serviceList = array();
	$query = "SELECT sgr.host_host_id, sgr.service_service_id
	    FROM servicegroup_relation sgr, ns_host_relation nhr
	    WHERE sgr.servicegroup_relation = " . $sgId;
	if (!is_null($instanceId)) {
             $query .= " AND sgr.host_host_id = nhr.host_host_id
                 AND nhr.nagios_server_id = " . $instanceId;
	}
	$query .= " UNION SELECT hgr.host_host_id, hsr.service_service_id
	    FROM servicegroup_relation sgr, host_service_relation hsr, hostgroup_relation hgr, ns_host_relation nhr
	    WHERE sgr.hostgroup_hg_id = hsr.hostgroup_hg_id
	    AND hsr.service_service_id = sgr.service_service_id
	    AND sgr.hostgroup_hg_id = hgr.hostgroup_hg_id
	    AND sgr.servicegroup_sg_id = " . $sgId;
	if (!is_null($instanceId)) {
             $query .= " AND hgr.host_host_id = nhr.host_host_id
                 AND nhr.nagios_server_id = " . $instanceId;
        }
	$res = $db->query($query);
	if (PEAR::isError($res)) {
            return array();
	}
	while ($row = $res->fetchRow()) {
	    $serviceList[] = $row;
	}
	$res->free();
    }


    function getListHostHostDependency($db, $instanceId) {
	$hostDependencies = array();
	$query = "SELECT dhp.host_host_id as parent_host_id, dhc.host_host_id as child_host_id
            FROM dependency_hostParent_relation dhp, dependency_hostChild_relation dhc, ns_host_relation nhr
	    WHERE dhp.dependency_dep_id = dhc.dependency_dep_id
	       AND dhp.host_host_id = nhr.host_host_id
	       AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
	    return array();
	}
	while ($row = $res->fetchRow()) {
            $hostDependencies[] = $row;
	}
	$res->free();
	$queryHg = "SELECT dhgp.hostgroup_hg_id as parent_hg, dhgc.hostgroup_hg_id as child_hg
            FROM dependency_hostgroupParent_relation dhgp, dependency_hostgroupChild_relation dhgc
            WHERE dhgp.dependency_dep_id = dhgc.dependency_dep_id";
	$res = $db->query($queryHg);
	if (PEAR::isError($res)) {
            return $hostDependencies;
        }
	$hgDeps = array();
	while ($row = $res->fetchRow()) {
	    if (!isset($hgDeps[$row['parent_hg']])) {
                $hgDeps[$row['parent_hg']] = array();
	    }
	    $hgDeps[$row['parent_hg']][] = $row['child_hg'];
	}
	$res->free();
	foreach ($hgDeps as $hgParentId => $hgChilds) {
	    $hostParents = getHostFormHg($db, $hgParentId, $instanceId);
	    foreach ($hgChilds as $hgChild) {
		$hostChilds = getHostFormHg($db, $hgParentId);
                foreach ($hostParents as $parentId) {
		    foreach ($hostChilds as $childId) {
			    $hostDependencies[] = array(
				    'parent_host_id' => $parentId,
				    'child_host_id' => $childId
			    );
		    }
		}
	    }
	}
	return $hostDependencies;
    }

    function getListServiceServiceDependency($db, $instanceId) {
        $serviceDependencies = array();
	$query = "SELECT dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
	    FROM dependency_serviceParent_relation dsp, dependency_serviceChild_relation dsc, ns_host_relation nhr
	    WHERE dsp.dependency_dep_id = dsc.dependency_dep_id
	        AND dsp.host_host_id = nhr.host_host_id
		AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
            return array();
	}
	while ($row = $res->fetchRow()) {
	    $serviceDependencies[] = $row;
	}
	$res->free();
	$querySg = "SELECT dsgp.servicegroup_sg_id as parent_sg, dsgc.servicegroup_sg_id as child_sg
	    FROM dependency_servicegroupParent_relation dsgp, dependency_servicegroupChild_relation dsgc
	    WHERE dsgp.dependency_dep_id = dsgc.dependency_dep_id";
	$res = $db->query($querySg);
	if (PEAR::isError($res)) {
	    return $serviceDependencies;
	}
	while ($row = $res->fetchRow()) {
		if (!isset($sgDep[$row['parent_sg']])) {
		    $sgDep[$row['parent_sg']] = array();
		}
		$sgDep[$row['parent_sg']][] = $row['child_sg'];
	}
	$res->free();
	foreach ($sgDeps as $sgParentId => $sgChilds) {
	    $serviceParents = getServiceFromSg($db, $sgParentId, $instanceId);
	    foreach ($sgChilds as $sgChild) {
		$serviceChilds = getServiceFromSg($db, $sgParentId);
                foreach ($serviceParents as $parentId) {
		    foreach ($serviceChilds as $childId) {
			    $serviceDependencies[] = array(
				    'parent_host_id' => $parentId['host_host_id'],
				    'parent_service_id' => $parentId['service_service_id'],
				    'child_host_id' => $childId['host_host_id'],
				    'child_service_id' => $childId['service_service_id']
			    );
		    }
		}
	    }
	}
	return $serviceDependencies;
    }

    /**
     * Generate the file for correlation
     *
     * @param unknown_type $file
     */
    function generateCentreonBrokerCorrelation($cbObj, $dir, $instanceId, $db)
    {
        /*$hostObj = new CentreonHost($db);
	$depObj = new CentreonDependency($db);*/
        $xml = new CentreonXML(true);
        $xml->startElement('conf');
        /*
         * Add host
         */
	$query = "SELECT h.host_id
            FROM host h, ns_host_relation nhr
	    WHERE h.host_register = '1'
	        AND h.host_activate = '1'
	        AND nhr.host_host_id = h.host_id
	        AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
	    // @TODO manage error
	    return;
	}
        while ($row = $res->fetchRow()) {
            $xml->startElement('host');
            $xml->writeAttribute('id', $row['host_id']);
	    $xml->writeAttribute('instance_id', $instanceId);
            $xml->endElement(); /* host */
        }
	$res->free();
        /*
         * Add host parent relation
         */
	$query = "SELECT hp.host_parent_hp_id, h.host_id
            FROM host h, host_hostparent_relation hp, ns_host_relation nhr
	    WHERE h.host_register = '1'
	        AND h.host_activate = '1'
		AND nhr.host_host_id = hp.host_parent_hp_id
		AND hp.host_host_id = h.host_id
		AND nhr.nagios_server_id = %d";
        $res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
	    return;
	}
        while ($row = $res->fetchRow()) {
            $xml->startElement('parent');
            $xml->writeAttribute('parent', $row['host_parent_hp_id']);
            $xml->writeAttribute('host', $row['host_id']);
	    $xml->writeAttribute('instance_id', $instanceId);
            $xml->endElement(); /* parent */
        }
	$res->free();
        /*
         * Host / Service relation
         */
	$query = "SELECT hsr.host_host_id, hsr.service_service_id
		FROM host h, host_service_relation hsr, ns_host_relation nhr, service s
		WHERE s.service_register = '1'
		AND s.service_activate = '1'
		AND h.host_register = '1'
		AND h.host_activate = '1'
		AND h.host_id = hsr.host_host_id
		AND nhr.host_host_id = h.host_id
		AND s.service_id = hsr.service_service_id
		AND nhr.nagios_server_id = %d
		UNION SELECT hgr.host_host_id, s.service_id
		FROM service s, host_service_relation hsr, host h, hostgroup_relation hgr, ns_host_relation nhr
		WHERE s.service_register = '1'
		AND s.service_activate = '1'
		AND h.host_register = '1'
		AND h.host_activate = '1'
		AND hgr.host_host_id = h.host_id
		AND s.service_id = hsr.service_service_id
		AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
		AND nhr.host_host_id = h.host_id
		AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId, $instanceId));
	if (PEAR::isError($res)) {
	    return;
	}
        while ($row = $res->fetchRow()) {
            $xml->startElement('service');
            $xml->writeAttribute('id', $row['service_service_id']);
            $xml->writeAttribute('host', $row['host_host_id']);
	    $xml->writeAttribute('instance_id', $instanceId);
            $xml->endElement(); /* service */
        }
	$res->free();
        /*
         * Hosts dependencies
         */
	$hostDependencies = getListHostHostDependency($db, $instanceId);
        foreach ($hostDependencies as $hostDependency) {
            $xml->startElement('dependency');
            $xml->writeAttribute('host', $hostDependency['parent_host_id']);
            $xml->writeAttribute('dependent_host', $hostDependency['child_host_id']);
            $xml->endElement(); /* dependency */
        }
        /*
         * Services dependencies
         */
        $serviceDependencies = getListServiceServiceDependency($db, $instanceId);
        foreach ($serviceDependencies as $serviceDependency) {
            $xml->startElement('dependency');
            $xml->writeAttribute('host', $serviceDependency['parent_host_id']);
            $xml->writeAttribute('service', $serviceDependency['parent_service_id']);
            $xml->writeAttribute('dependent_host', $serviceDependency['child_host_id']);
            $xml->writeAttribute('dependent_service', $serviceDependency['child_service_id']);
            $xml->endElement(); /* dependency */
        }

        /*
         * Host Service Dependencies
         */
	$query = "SELECT dhp.host_host_id as parent_host_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
	    FROM dependency_hostParent_relation dhp, dependency_serviceChild_relation dsc, ns_host_relation nhr
	    WHERE dhp.dependency_dep_id = dsc.dependency_dep_id
	        AND dhp.host_host_id = nhr.host_host_id
		AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
	    return;
	}
        while ($dep = $res->fetchRow()) {
            $xml->startElement('dependency');
            $xml->writeAttribute('host', $dep['parent_host_id']);
            $xml->writeAttribute('dependent_host', $dep['child_host_id']);
            $xml->writeAttribute('dependent_service', $dep['child_service_id']);
            $xml->endElement(); /* dependency */
        }
	$res->free();

        /*
         * Service Host Dependencies
         */
	$query = "SELECT dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dhc.host_host_id as child_host_id
	    FROM dependency_serviceParent_relation dsp, dependency_hostChild_relation dhc, ns_host_relation nhr
	    WHERE dsp.dependency_dep_id = dhc.dependency_dep_id
	        AND dsp.host_host_id = nhr.host_host_id
		AND nhr.nagios_server_id = %d";
	$res = $db->query(sprintf($query, $instanceId));
	if (PEAR::isError($res)) {
	    return;
	}
        while ($dep = $res->fetchRow()) {
            $xml->startElement('dependency');
            $xml->writeAttribute('host', $dep['parent_host_id']);
            $xml->writeAttribute('service', $dep['parent_service_id']);
            $xml->writeAttribute('dependent_host', $dep['child_host_id']);
            $xml->endElement(); /* dependency */
        }

        $xml->endElement(); /* conf */
	if (!is_dir($dir)) {
            mkdir($dir);
	}
        ob_start();
        $xml->output();
        file_put_contents($dir . '/correlation_' . $instanceId . '.xml', ob_get_contents());
        ob_end_clean();
    }
?>
