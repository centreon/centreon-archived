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

require_once _CENTREON_PATH_ . '/www/include/configuration/configKnowledge/functions.php';
require_once _CENTREON_PATH_ . '/www/class/centreonHost.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonService.class.php';

class procedures_Proxy
{
    private $DB;
    private $hflag;
    private $sflag;
    private $proc;
    private $wikiUrl;
    private $hostObj;
    private $serviceObj;

    /**
     * procedures_Proxy constructor.
     * @param $pearDB
     */
    public function __construct($pearDB)
    {
        $this->DB = $pearDB;
        $this->hflag = 0;
        $this->sflag = 0;
        $this->hostObj = new CentreonHost($this->DB);
        $this->serviceObj = new CentreonService($this->DB);

        $conf = getWikiConfig($this->DB);
        $this->wikiUrl = $conf['kb_wiki_url'];
        $this->proc = new procedures($this->DB);
    }

    /**
     * @param string $hostName
     * @return int
     */
    private function getHostId($hostName)
    {
        $result = $this->DB->query(
            "SELECT host_id FROM host WHERE host_name LIKE '" . $hostName . "' "
        );
        $row = $result->fetch();
        $hostId = 0;
        if ($row["host_id"]) {
            $hostId = $row["host_id"];
        }
        return $hostId;
    }

    /**
     * @param string $hostName
     * @param string $serviceDescription
     * @return mixed
     */
    private function getServiceId($hostName, $serviceDescription)
    {
        /*
         * Get Services attached to hosts
         */
        $result = $this->DB->query(
            "SELECT s.service_id " .
            "FROM host h, service s, host_service_relation hsr " .
            "WHERE hsr.host_host_id = h.host_id " .
            "AND hsr.service_service_id = service_id " .
            "AND h.host_name LIKE '" . $hostName . "' " .
            "AND s.service_description LIKE '" . $serviceDescription . "' "
        );
        while ($row = $result->fetch()) {
            return $row["service_id"];
        }
        $result->closeCursor();
        /*
         * Get Services attached to hostgroups
         */
        $result = $this->DB->query(
            "SELECT s.service_id " .
            "FROM hostgroup_relation hgr, host h, service s, host_service_relation hsr " .
            "WHERE hgr.host_host_id = h.host_id " .
            "AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id " .
            "AND h.host_name LIKE '" . $hostName . "' " .
            "AND service_id = hsr.service_service_id " .
            "AND service_description LIKE '" . $serviceDescription . "' "
        );
        while ($row = $result->fetch()) {
            return $row["service_id"];
        }
        $result->closeCursor();
    }

    /**
     * @param string $hostName
     */
    public function getHostUrl($hostName)
    {
        $this->proc->setHostInformations();

        $procList = $this->proc->getProcedures();

        /*
         * Check if host has a procedure directly on Host
         */
        if (isset($procList["Host_:_" . $hostName])) {
            return $this->wikiUrl . "/index.php?title=Host_:_" . $hostName;
        }

        /*
         * Check if host can get a procedure on templates
         */
        $hostId = $this->getHostId($hostName);
        $templates = $this->hostObj->getTemplateChain($hostId);
        foreach ($templates as $template) {
            $templateName = $template['host_name'];
            if (isset($procList["Host-Template_:_" . $templateName])) {
                return $this->wikiUrl . "/index.php?title=Host-Template_:_" . $templateName;
            }
        }
    }

    /**
     * @param string $hostName
     * @param string $serviceDescription
     */
    public function getServiceUrl($hostName, $serviceDescription)
    {
        if ($this->hflag != 0) {
            $this->proc->setHostInformations();
        }
        $this->proc->setServiceInformations();
        $this->sflag;

        $procList = $this->proc->getProcedures();

        /*
         * Check Service
         */
        $serviceDescription = str_replace(' ', '_', $serviceDescription);

        if (isset($procList["Service_:_" . trim($hostName . "_/_" . $serviceDescription)])) {
            return $this->wikiUrl . "/index.php?title=Service_:_" . $hostName . "_/_" . $serviceDescription;
        }

        /*
         * Check service Template
         */
        $serviceId = $this->getServiceId($hostName, $serviceDescription);
        $templates = $this->serviceObj->getTemplatesChain($serviceId);
        foreach ($templates as $templateId) {
            $templateDescription = $this->serviceObj->getServiceDesc($templateId);
            if (isset($procList["Service-Template_:_" . $templateDescription])) {
                return $this->wikiUrl . "/index.php?title=Service-Template_:_" . $templateDescription;
            }
        }

        return $this->getHostUrl($hostName);
    }
}
