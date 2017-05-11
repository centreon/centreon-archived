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
    private $url;
    private $username;
    private $password;
    private $version;
    private $curl;
    private $loggedIn;
    private $tokens;

    /**
     * WikiApi constructor.
     */
    public function __construct()
    {
        $this->db = new CentreonDB();
        $this->wikiObj = new Wiki();
        $config = $this->wikiObj->getWikiConfig();
        $this->url = $config['kb_wiki_url'] . '/api.php';
        $this->username = $config['kb_wiki_account'];
        $this->password = $config['kb_wiki_password'];
        $this->curl = $this->getCurl();
        $this->version = $this->getWikiVersion();
    }

    private function getCurl()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);

        return $curl;
    }

    function getWikiVersion()
    {
        $postfields = array(
            'action' => 'query',
            'meta' => 'siteinfo',
            'format' => 'json',
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        $result = json_decode($result, true);

        $version = $result['query']['general']['generator'];
        $version = explode(' ', $version);
        if (isset($version[1])) {
            return (float)$version[1];
        } else {
            throw new \Exception("An error occured, please check your Knowledge base configuration");
        }
    }

    public function login()
    {
        if ($this->loggedIn) {
            return $this->loggedIn;
        }

        // Get Connection Cookie/Token
        $postfields = array(
            'action' => 'login',
            'format' => 'json',
            'lgname' => $this->username,
            'lgpassword' => $this->password
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        $result = json_decode($result, true);
        $token = $result['login']['lgtoken'];

        // Launch Connection
        $postfields['lgtoken'] = $token;

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        $result = curl_exec($this->curl);
        curl_setopt($this->curl, CURLOPT_HEADER, false);

        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        // Get cookies
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
        $cookies = implode('; ', $matches[1]);
        curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);

        $result = json_decode($body, true);
        $resultLogin = $result['login']['result'];

        $this->loggedIn = false;
        if ($resultLogin == 'Success') {
            $this->loggedIn = true;
        }

        return $this->loggedIn;
    }

    public function logout()
    {
        $postfields = array(
            'action' => 'logout'
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_exec($this->curl);
    }

    public function getMethodToken($method = 'delete', $title = '')
    {
        if (isset($this->tokens[$method])) {
            return $this->tokens[$method];
        }

        if ($this->version >= 1.24) {
            $postfields = array(
                'action' => 'query',
                'meta' => 'tokens',
                'type' => 'csrf',
                'format' => 'json'
            );
        } elseif ($this->version >= 1.20) {
            $postfields = array(
                'action' => 'tokens',
                'type' => $method,
                'format' => 'json'
            );
        } else {
            $postfields = array(
                'action' => 'query',
                'prop' => 'info',
                'intoken' => $method,
                'format' => 'json',
                'titles' => $title
            );
        }

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        $result = json_decode($result, true);

        if ($this->version >= 1.24) {
            $this->tokens[$method] = $result['query']['tokens']['csrftoken'];
        } elseif ($this->version >= 1.20) {
            $this->tokens[$method] = $result['tokens'][$method . 'token'];
        } else {
            $page = array_pop($result['query']['pages']);
            $this->tokens[$method] = $page[$method . 'token'];
        }

        return $this->tokens[$method];
    }

    public function movePage($oldTitle = '', $newTitle = '')
    {
        $this->login();

        $token = $this->getMethodToken('move', $oldTitle);

        $postfields = array(
            'action' => 'move',
            'from' => $oldTitle,
            'to' => $newTitle,
            'token' => $token
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_exec($this->curl);

        return true;
    }

    public function deletePage($title = '')
    {
        $this->login();

        $token = $this->getMethodToken('delete', $title);

        $postfields = array(
            'action' => 'delete',
            'title' => $title,
            'token' => $token
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_exec($this->curl);

        return true;
    }

    public function getAllPages()
    {
        $postfields = array(
            'format' => 'json',
            'action' => 'query',
            'list' => 'allpages'
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        $result = json_decode($result);

        $pages = array();
        foreach ($result->query->allpages as $page) {
            $pages[] = $page->title;
        }

        return $pages;
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function getChangedPages($count = 50)
    {
        // Connecting to Mediawiki API
        $apiUrl = $this->url . '/api.php?format=json&action=query&list=recentchanges' .
            '&rclimit=' . $count . '&rcprop=title&rctype=new|edit';

        $postfields = array(
            'format' => 'json',
            'action' => 'query',
            'list' => 'recentchanges',
            'rclimit' => $count,
            'rcprop' => 'title',
            'rctype' => 'new|edit'
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        $result = json_decode($result);

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
        $querySelect = 'SELECT host_id FROM host WHERE host_name LIKE ?';
        $stmt = $this->db->prepare($querySelect);
        $resHost = $this->db->execute($stmt, array((string)$hostName));
        $tuple = $resHost->fetchRow();


        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$';
        $queryUpdate = "UPDATE extended_host_information "
            . "SET ehi_notes_url = ? "
            . "WHERE host_host_id = ?";
        $stmt = $this->db->prepare($queryUpdate);
        $this->db->execute($stmt, array((string)$valueToAdd, (int)$tuple['host_id']));
    }

    /**
     * @param $hostName
     * @param $serviceDescription
     */
    public function updateLinkForService($hostName, $serviceDescription)
    {
        $query = "SELECT service_id " .
            "FROM service, host, host_service_relation " .
            "WHERE host.host_name LIKE ? " .
            "AND service.service_description LIKE ? " .
            "AND host_service_relation.host_host_id = host.host_id " .
            "AND host_service_relation.service_service_id = service.service_id ";


        $stmt = $this->db->prepare($query);
        $resService = $this->db->execute($stmt, array((string)$hostName, (string)$serviceDescription));
        $tuple = $resService->fetchRow();

        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?' .
            'host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
        $queryUpdate = "UPDATE extended_service_information " .
            "SET esi_notes_url = ? " .
            "WHERE service_service_id = ? ";
        $stmt = $this->db->prepare($queryUpdate);
        $this->db->execute($stmt, array((string)$valueToAdd, (int)$tuple['service_id']));

    }

    /**
     * @param $serviceName
     */
    public function updateLinkForServiceTemplate($serviceName)
    {
        $query = "SELECT service_id FROM service WHERE service_description LIKE ? ";
        $stmt = $this->db->prepare($query);
        $resService = $this->db->execute($stmt, array((string)$serviceName));
        $tuple = $resService->fetchRow();

        $valueToAdd = './include/configuration/configKnowledge/proxy/proxy.php?' .
            'host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
        $queryUpdate = "UPDATE extended_service_information " .
            "SET esi_notes_url = ? " .
            "WHERE service_service_id = ? ";
        $stmt = $this->db->prepare($queryUpdate);
        $this->db->execute($stmt, array((string)$valueToAdd, (int)$tuple['service_id']));

    }
}
