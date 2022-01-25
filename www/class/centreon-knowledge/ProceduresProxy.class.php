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
require_once _CENTREON_PATH_ . '/www/class/centreon-knowledge/wikiApi.class.php';

class ProceduresProxy
{
    private $DB;
    private $wikiUrl;
    private $hostObj;
    private $serviceObj;

    /**
     * ProceduresProxy constructor.
     * @param $pearDB
     */
    public function __construct($pearDB)
    {
        $this->DB = $pearDB;
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
        $statement = $this->DB->prepare("SELECT host_id FROM host WHERE host_name LIKE :hostName");
        $statement->bindValue(':hostName', $hostName, \PDO::PARAM_STR);
        $statement->execute();
        $hostId = 0;
        if ($row = $statement->fetch()) {
            $hostId = $row["host_id"];
        }
        return $hostId;
    }

    /**
     * Get service id from hostname and service description
     *
     * @param string $hostName
     * @param string $serviceDescription
     * @return int|null
     */
    private function getServiceId($hostName, $serviceDescription): ?int
    {
        /*
         * Get Services attached to hosts
         */
        $statement = $this->DB->prepare(
            "SELECT s.service_id FROM host h, service s, host_service_relation hsr " .
            "WHERE hsr.host_host_id = h.host_id " .
            "AND hsr.service_service_id = service_id " .
            "AND h.host_name LIKE :hostName " .
            "AND s.service_description LIKE :serviceDescription "
        );
        $statement->bindValue(':hostName', $hostName, \PDO::PARAM_STR);
        $statement->bindValue(':serviceDescription', $serviceDescription, \PDO::PARAM_STR);
        $statement->execute();
        if ($row = $statement->fetch()) {
            return (int) $row["service_id"];
        }
        $statement->closeCursor();

        /*
         * Get Services attached to hostgroups
         */
        $statement = $this->DB->prepare(
            "SELECT s.service_id FROM hostgroup_relation hgr, host h, service s, host_service_relation hsr " .
            "WHERE hgr.host_host_id = h.host_id " .
            "AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id " .
            "AND h.host_name LIKE :hostName " .
            "AND service_id = hsr.service_service_id " .
            "AND service_description LIKE :serviceDescription "
        );
        $statement->bindValue(':hostName', $hostName, \PDO::PARAM_STR);
        $statement->bindValue(':serviceDescription', $serviceDescription, \PDO::PARAM_STR);
        $statement->execute();
        if ($row = $statement->fetch()) {
            return (int) $row["service_id"];
        }
        $statement->closeCursor();

        return null;
    }

    /**
     * Get service notes url
     *
     * @param int $serviceId
     * @return string|null
     */
    private function getServiceNotesUrl(int $serviceId): ?string
    {
        $notesUrl = null;

        $statement = $this->DB->prepare(
            "SELECT esi_notes_url " .
            "FROM extended_service_information " .
            "WHERE service_service_id = :serviceId"
        );

        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            $notesUrl = $row['esi_notes_url'];
        }

        return $notesUrl;
    }

    /**
     * Get host url
     *
     * @param string $hostName
     * @return string|null
     */
    public function getHostUrl($hostName): ?string
    {
        $hostId = $this->getHostId($hostName);

        if ($hostId === null) {
            return null;
        }

        $hostProperties = $this->hostObj->getInheritedValues(
            $hostId,
            [],
            1,
            ['host_name', 'ehi_notes_url']
        );

        if (isset($hostProperties['ehi_notes_url'])) {
            return $this->wikiUrl . "/index.php?title=Host_:_" . $hostProperties['host_name'];
        }

        $templates = $this->hostObj->getTemplateChain($hostId);
        foreach ($templates as $template) {
            $inheritedHostProperties = $this->hostObj->getInheritedValues(
                $template['id'],
                [],
                1,
                ['host_name', 'ehi_notes_url']
            );

            if (isset($inheritedHostProperties['ehi_notes_url'])) {
                return $this->wikiUrl . "/index.php?title=Host-Template_:_" . $inheritedHostProperties['host_name'];
            }
        }

        return null;
    }

    /**
     * Get service url
     *
     * @param string $hostName
     * @param string $serviceDescription
     * @return string|null
     */
    public function getServiceUrl($hostName, $serviceDescription): ?string
    {
        $serviceDescription = str_replace(' ', '_', $serviceDescription);

        $serviceId = $this->getServiceId($hostName, $serviceDescription);

        if ($serviceId === null) {
            return null;
        }

        /*
         * Check Service
         */
        $notesUrl = $this->getServiceNotesUrl($serviceId);
        if ($notesUrl !== null) {
            return $this->wikiUrl . "/index.php?title=Service_:_" . $hostName . "_/_" . $serviceDescription;
        }

        /*
         * Check service Template
         */
        $serviceId = $this->getServiceId($hostName, $serviceDescription);
        $templates = $this->serviceObj->getTemplatesChain($serviceId);
        foreach (array_reverse($templates) as $templateId) {
            $templateDescription = $this->serviceObj->getServiceDesc($templateId);
            $notesUrl = $this->getServiceNotesUrl((int) $templateId);
            if ($notesUrl !== null) {
                return $this->wikiUrl . "/index.php?title=Service-Template_:_" . $templateDescription;
            }
        }

        return $this->getHostUrl($hostName);
    }
}
