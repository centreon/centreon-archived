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

    /**
     * Generate the file for correlation
     * 
     * @param unknown_type $file
     */
    function generateCentreonBrokerCorrelation($cbObj, $file, $db)
    {
        $hostObj = new CentreonHost($db);
        $depObj = new CentreonDependency($db);
        $xml = new CentreonXML(true);
        $xml->startElement('conf');
        /*
         * Add host
         */
        $hosts = $hostObj->getList(true);
        foreach (array_keys($hosts) as $host_id) {
            $xml->startElement('host');
            $xml->writeAttribute('id', $host_id);
            $xml->endElement(); /* host */
        }
        /*
         * Add host parent relation
         */
        $hostTree = $hostObj->getHostRelationTree(true);
        foreach ($hostTree as $hostParent => $hosts) {
            foreach (array_keys($hosts) as $host) {
                $xml->startElement('parent');
                $xml->writeAttribute('parent', $hostParent);
                $xml->writeAttribute('host', $host);
                $xml->endElement(); /* parent */
            }
        }
        /*
         * Host / Service relation
         */
        $hostServiceRelation = $hostObj->getHostServiceRelationTree(true);
        foreach ($hostServiceRelation as $host => $services) {
            foreach (array_keys($services) as $service) {
                $xml->startElement('service');
                $xml->writeAttribute('id', $service);
                $xml->writeAttribute('host', $host);
                $xml->endElement(); /* service */
            }
        }
        /*
         * Hosts dependencies
         */
        $hostDependencies = $depObj->getHostHost(true);
        foreach ($hostDependencies as $hostDependency) {
            $xml->startElement('dependency');
            $xml->writeAttribute('dependent_host', $hostDependency['parent_host_id']);
            $xml->writeAttribute('host_id', $hostDependency['child_host_id']);
            $xml->endElement(); /* dependency */
        }
        /*
         * Services dependencies
         */
        $serviceDependencies = $depObj->getServiceService(true);
        foreach ($serviceDependencies as $serviceDependency) {
            $xml->startElement('dependency');
            $xml->writeAttribute('dependent_host', $serviceDependency['parent_host_id']);
            $xml->writeAttribute('dependent_service', $serviceDependency['parent_service_id']);
            $xml->writeAttribute('host_id', $serviceDependency['child_host_id']);
            $xml->writeAttribute('service_id', $serviceDependency['child_service_id']);
            $xml->endElement(); /* dependency */
        }
        $xml->endElement(); /* conf */
        ob_start();
        $xml->output();
        file_put_contents($file, ob_get_contents());
        ob_end_clean();
    }
?>