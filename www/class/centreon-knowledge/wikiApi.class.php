<?php
/*
 * Copyright 2005-2019 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once realpath(__DIR__ . "/../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon-knowledge/wiki.class.php";

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
    private $noSslCertificate;

    public const PROXY_URL = './include/configuration/configKnowledge/proxy/proxy.php';

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
        $this->noSslCertificate = $config['kb_wiki_certificate'];
        $this->curl = $this->getCurl();
        $this->version = $this->getWikiVersion();
        $this->cookies = array();
    }

    private function getCurl()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, '');
        if ($this->noSslCertificate == 1) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        return $curl;
    }

    public function getWikiVersion()
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
            'action' => 'query',
            'meta' => 'tokens',
            'format' => 'json',
            'type' => 'login'
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        if ($result === false) {
            throw new \Exception("curl error");
        }
        $decoded = json_decode($result, true);

        $token = $decoded['query']['tokens']['logintoken'];

        // Launch Connection
        $postfields = [
            'action' => 'login',
            'lgname' => $this->username,
            'format' => 'json'
        ];

        $postfields['lgpassword'] = $this->password;
        $postfields['lgtoken'] = $token;

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        if ($result === false) {
            throw new \Exception("curl error");
        }

        $decoded = json_decode($result, true);
        $resultLogin = $decoded['login']['result'];

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

    /**
     * API Endpoint for deleting Knowledgebase Page
     * @param string $title
     * @return bool
     */
    public function deletePage($title = '')
    {
        $tries = 0;
        $deleteResult = $this->deleteMWPage($title);
        while ($tries < 5 && isset($deleteResult->error)) {
            $deleteResult = $this->deleteMWPage($title);
            $tries++;
        }

        if (isset($deleteResult->error)) {
            return false;
        } elseif (isset($deleteResult->delete)) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllPages()
    {
        $postfields = array(
            'format' => 'json',
            'action' => 'query',
            'list' => 'allpages',
            'aplimit' => '200'
        );

        $pages = array();
        do {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
            $result = curl_exec($this->curl);
            $result = json_decode($result);

            foreach ($result->query->allpages as $page) {
                $pages[] = $page->title;
            }

            // Get next page if exists
            $continue = false;
            if ($this->version >= 1.31) {
                if (isset($result->{'continue'}->apcontinue)) {
                    $postfields['apfrom'] = $result->{'continue'}->apcontinue;
                    $continue = true;
                }
            } elseif (isset($result->{'query-continue'}->allpages->apcontinue)) {
                $postfields['apfrom'] = $result->{'query-continue'}->allpages->apcontinue;
                $continue = true;
            }
        } while ($continue === true);

        return $pages;
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function getChangedPages($count = 50)
    {
        // Connecting to Mediawiki API
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
        $resHost = $this->db->query(
            "SELECT host_id FROM host WHERE host_name LIKE '" . $hostName . "'"
        );

        $hostRow = $resHost->fetch();

        if ($hostRow !== false) {
            $url = self::PROXY_URL . '?host_name=$HOSTNAME$';
            $statement = $this->db->prepare(
                "UPDATE extended_host_information " .
                "SET ehi_notes_url = :url " .
                "WHERE host_host_id = :hostId"
            );
            $statement->bindValue(':url', $url, \PDO::PARAM_STR);
            $statement->bindValue(':hostId', $hostRow['host_id'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }

    /**
     * @param $hostName
     * @param $serviceDescription
     */
    public function updateLinkForService($hostName, $serviceDescription)
    {
        $resService = $this->db->query(
            "SELECT service_id " .
            "FROM service, host, host_service_relation " .
            "WHERE host.host_name LIKE '" . $hostName . "' " .
            "AND service.service_description LIKE '" . $serviceDescription . "' " .
            "AND host_service_relation.host_host_id = host.host_id " .
            "AND host_service_relation.service_service_id = service.service_id "
        );
        $serviceRow = $resService->fetch();

        if ($serviceRow !== false) {
            $url = self::PROXY_URL . '?host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
            $statement = $this->db->prepare(
                "UPDATE extended_service_information " .
                "SET esi_notes_url = :url " .
                "WHERE service_service_id = :serviceId"
            );
            $statement->bindValue(':url', $url, \PDO::PARAM_STR);
            $statement->bindValue(':serviceId', $serviceRow['service_id'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }

    /**
     * @param $serviceName
     */
    public function updateLinkForServiceTemplate($serviceName)
    {
        $resService = $this->db->query(
            "SELECT service_id FROM service " .
            "WHERE service_description LIKE '" . $serviceName . "' "
        );
        $serviceTemplateRow = $resService->fetch();

        if ($serviceTemplateRow !== false) {
            $url = self::PROXY_URL . '?host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
            $statement = $this->db->prepare(
                "UPDATE extended_service_information " .
                "SET esi_notes_url = :url " .
                "WHERE service_service_id = :serviceId"
            );
            $statement->bindValue(':url', $url, \PDO::PARAM_STR);
            $statement->bindValue(':serviceId', $serviceTemplateRow['service_id'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }

    /**
     * make a call to mediawiki api to delete a page
     * @param string $title
     * @return object
     */
    private function deleteMWPage($title = '')
    {
        $this->login();

        $token = $this->getMethodToken('delete', $title);

        $postfields = array(
            'action' => 'delete',
            'title' => $title,
            'token' => $token,
            'format' => 'json'
        );
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($this->curl);
        return json_decode($result);
    }
}
