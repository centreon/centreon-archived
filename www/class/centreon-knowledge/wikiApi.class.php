<?php
/*
 * Copyright 2005-2017 Centreon
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

require_once realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon-knowledge/wiki.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon-knowledge/procedures.class.php";

class WikiApi
{
    private $db;
    private $wikiObj;
    private $wikiUrl;

    /**
     * WikiApi constructor.
     */
    public function __construct()
    {
        $this->db = new CentreonDB();
        $this->wikiObj = new Wiki();
        $this->wikiUrl = $this->wikiObj->getWikiUrl();
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function getChangedPages($count = 50)
    {
        // Connecting to Mediawiki API
        $apiUrl = $this->wikiUrl . '/api.php?format=json&action=query&list=recentchanges' .
            '&rclimit=' . $count . '&rcprop=title&rctype=new|edit';

        // Sending request
        $result = json_decode(file_get_contents($apiUrl));

        return $result->query->recentchanges;
    }

    /**
     * @return array
     */
    public function detectCentreonObjects()
    {
        $pages = $this->getChangedPages();

        $hosts = array();
        $hostsTemplates = array();
        $services = array();
        $servicesTemplates = array();

        $count = count($pages);
        for ($i = 0; $i < $count; $i++) {
            $objectFlag = explode(':', $pages[$i]->title);
            $type = trim($objectFlag[0]);
            switch ($type) {
                case 'Host':
                    if (!in_array($pages[$i]->title, $hosts)) {
                        $hosts[] = $pages[$i]->title;
                    }
                    break;

                case 'Host-Template':
                    if (!in_array($pages[$i]->title, $hostsTemplates)) {
                        $hostsTemplates[] = $pages[$i]->title;
                    }
                    break;

                case 'Service':
                    if (!in_array($pages[$i]->title, $services)) {
                        $services[] = $pages[$i]->title;
                    }
                    break;

                case 'Service-Template':
                    if (!in_array($pages[$i]->title, $servicesTemplates)) {
                        $servicesTemplates[] = $pages[$i]->title;
                    }
                    break;
            }
        }

        return array(
            'hosts' => $hosts,
            'hostTemplates' => $hostsTemplates,
            'services' => $services,
            'serviceTemplates' => $servicesTemplates,
        );
    }

    /**
     *
     */
    public function synchronize()
    {
        // Get all pages title that where changed
        $listOfObjects = $this->detectCentreonObjects();

        foreach ($listOfObjects as $categorie => $object) {
            switch ($categorie) {
                case 'hosts':
                    foreach ($object as $entity) {
                        $objName = str_replace('Host : ', '', $entity);
                        $objName = str_replace(' ', '_', $objName);
                        $this->updateLinkForHost($objName);
                    }
                    break;

                case 'hostTemplates':
                    foreach ($object as $entity) {
                        $objName = str_replace('Host-Template : ', '', $entity);
                        $objName = str_replace(' ', '_', $objName);
                        $this->updateLinkForHost($objName);
                    }
                    break;

                case 'services':
                    foreach ($object as $entity) {
                        $objName = str_replace('Service : ', '', $entity);
                        $objName = str_replace(' ', '_', $objName);
                        if (preg_match('#(.+)_/_(.+)#', $objName, $matches)) {
                            $this->updateLinkForService($matches[1], $matches[2]);
                        }
                    }
                    break;

                case 'serviceTemplates':
                    foreach ($object as $entity) {
                        $objName = str_replace('Service-Template : ', '', $entity);
                        $objName = str_replace(' ', '_', $objName);
                        $this->updateLinkForServiceTemplate($objName);
                    }
                    break;
            }
        }
    }

    /**
     * @param $hostName
     */
    public function updateLinkForHost($hostName)
    {
        $querySelect = "SELECT host_id FROM host WHERE host_name LIKE '" . $hostName . "'";
        $resHost = $this->db->query($querySelect);
        $tuple = $resHost->fetchRow();

        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$';
        $queryUpdate = "UPDATE extended_host_information "
            . "SET ehi_notes_url = '" . $valueToAdd . "' "
            . "WHERE host_host_id = '" . $tuple['host_id'] . "'";
        $this->db->query($queryUpdate);
    }

    /**
     * @param $hostName
     * @param $serviceDescription
     */
    public function updateLinkForService($hostName, $serviceDescription)
    {
        $query = "SELECT service_id " .
            "FROM service, host, host_service_relation " .
            "WHERE host.host_name LIKE '" . $hostName . "' " .
            "AND service.service_description LIKE '" . $serviceDescription . "' " .
            "AND host_service_relation.host_host_id = host.host_id " .
            "AND host_service_relation.service_service_id = service.service_id ";
        $resService = $this->db->query($query);
        $tuple = $resService->fetchRow();

        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?' .
            'host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
        $queryUpdate = "UPDATE extended_service_information " .
            "SET esi_notes_url = '" . $valueToAdd . "' " .
            "WHERE service_service_id = '" . $tuple['service_id'] . "' ";
        $this->db->query($queryUpdate);
    }

    /**
     * @param $serviceName
     */
    public function updateLinkForServiceTemplate($serviceName)
    {
        $query = "SELECT service_id FROM service WHERE service_description LIKE '" . $serviceName . "' ";
        $resService = $this->db->query($query);
        $tuple = $resService->fetchRow();

        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?' .
            'host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
        $queryUpdate = "UPDATE extended_service_information " .
            "SET esi_notes_url = '" . $valueToAdd . "' " .
            "WHERE service_service_id = '" . $tuple['service_id'] . "' ";
        $this->db->query($queryUpdate);
    }
}
