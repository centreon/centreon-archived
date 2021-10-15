<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonUUID.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonStatistics.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class CentreonCeip extends CentreonWebService
{
    protected $uuid;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $centreon;
        $this->centreon = $centreon;

        parent::__construct();
    }

    /**
     * Get CEIP Account and User info
     *
     * @return array with Account/User info
     */
    public function getCeipInfo()
    {
        // Don't compute data is CEIP is disabled
        if (!$this->isCeipActive()) {
            return [
                'ceip' => false
            ];
        }

        // Get UUID
        $centreonUUID = new CentreonUUID($this->pearDB);
        $this->uuid = $centreonUUID->getUUID();

        return [
            'visitor' => $this->getVisitorInformation(),
            'account' => $this->getAccountInformation(),
            'excludeAllText' => true,
            'ceip' => true
        ];
    }

    /**
     * Get the type of the Centreon server
     *
     * @return string the type of the server
     */
    private function getServerType(): string
    {
        $stmt = $this->pearDB->prepare(
            "SELECT `value` FROM `informations` WHERE `key` = 'isRemote'"
        );
        $stmt->execute();
        $result = $stmt->fetchRow();

        $isRemote = $result['value'] === 'yes' ? 'remote' : 'central';

        return $isRemote;
    }

    /**
     * Get visitor information
     *
     * @return array with visitor information
     */
    private function getVisitorInformation(): array
    {
        $locale = $this->centreon->user->lang === 'browser'
            ? null
            : $this->centreon->user->lang;

        $role = $this->centreon->user->admin
            ? "admin"
            : "user";

        if (strcmp($role, 'admin') != 0) {
            $stmt = $this->pearDB->prepare('
                SELECT COUNT(*)
                FROM acl_actions_rules  AS aar
                INNER JOIN acl_actions AS aa ON (aa.acl_action_id = aar.acl_action_rule_id)
                INNER JOIN acl_group_actions_relations AS agar ON (agar.acl_action_id = aar.acl_action_rule_id)
                INNER JOIN acl_group_contacts_relations AS agcr ON (agcr.acl_group_id = agar.acl_group_id)
                INNER JOIN acl_group_contactgroups_relations AS agcgr ON (agcgr.acl_group_id = agar.acl_group_id)
                WHERE aar.acl_action_name LIKE "service\_%" OR aar.acl_action_name LIKE "host\_%"
                AND agcr.contact_contact_id = :contact_id
                OR agcgr.acl_group_id IN (
                    SELECT contactgroup_cg_id
                    FROM contactgroup_contact_relation
                    WHERE contact_contact_id = :contact_id
                )
            ');
            $stmt->bindValue(':contact_id', $this->centreon->user->user_id, PDO::PARAM_INT);
            $stmt->bindValue(':contact_id', $this->centreon->user->user_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchRow();

            if ($result > 0) {
                $role = 'operator';
            }
        }

        return [
            'id' => substr($this->uuid, 0, 6) . '-' . $this->centreon->user->user_id,
            'locale' => $locale,
            'role' => $role
        ];
    }

    /**
     * Get account information
     *
     * @return array with account information
     */
    private function getAccountInformation(): array
    {
        // Get Centreon statistics
        $output = new ConsoleOutput();
        $logger = new ConsoleLogger($output);
        $centreonStats = new CentreonStatistics($logger);
        $configUsage = $centreonStats->getPlatformInfo();

        // Get Licences information
        $licenseInfo = $this->getLicenseInformation();

        // Get Version of Centreon
        $centreonVersion = $this->getCentreonVersion();

        return [
            'id' => $this->uuid,
            'name' => $licenseInfo['companyName'],
            'serverType' => $this->getServerType(),
            'licenseType' => $licenseInfo['licenseType'],
            'versionMajor' => $centreonVersion['major'],
            'versionMinor' => $centreonVersion['minor'],
            'nb_hosts' => $configUsage['nb_hosts'],
            'nb_services' => $configUsage['nb_services'],
            'nb_servers' => $configUsage['nb_central'] + $configUsage['nb_remotes'] + $configUsage['nb_pollers']
        ];
    }

    /**
     * Get license information such as company name and license type
     *
     * @return array with license info
     */
    private function getLicenseInformation(): array
    {
        /**
         * Getting License informations.
         */
        $dependencyInjector = \Centreon\LegacyContainer::getInstance();
        $productLicense = 'Open Source';
        $licenseClientName = '';
        try {
            $centreonModules = ['epp', 'bam', 'map', 'mbi'];
            $licenseObject = $dependencyInjector['lm.license'];
            $licenseInformation = [];
            foreach ($centreonModules as $module) {
                $licenseObject->setProduct($module);
                $isLicenseValid = $licenseObject->validate(false);
                if ($isLicenseValid && !empty($licenseObject->getData())) {
                    $licenseInformation[$module] = $licenseObject->getData();
                    $licenseClientName = $licenseInformation[$module]['client']['name'];
                    if ($module === 'epp') {
                        $productLicense = 'IT Edition';
                    }
                    if (in_array($module, ['mbi', 'bam', 'map'])) {
                        $productLicense = 'Business Edition';
                        break;
                    }
                }
            }
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }

        return [
            'companyName' => $licenseClientName,
            'licenseType' => $productLicense
        ];
    }

    /**
     * Get the major and minor versions of Centreon web
     *
     * @return array with major and minor versions
     */
    private function getCentreonVersion(): array
    {
        $stmt = $this->pearDB->prepare(
            "SELECT informations.value FROM informations WHERE informations.key = 'version'"
        );
        $stmt->execute();
        $result = $stmt->fetchRow();

        $minor = $result['value'];
        $major = substr($minor, 0, strrpos($minor, '.', 0));

        return [
            'major' => $major,
            'minor' => $minor
        ];
    }

    /**
     * Get CEIP status
     *
     * @return bool the status of CEIP
     */
    private function isCeipActive(): bool
    {
        $stmt = $this->pearDB->prepare(
            "SELECT `value` FROM `options` WHERE `key` = 'send_statistics' LIMIT 1"
        );
        $stmt->execute();

        if (($sendStatisticsResult = $stmt->fetchRow()) && $sendStatisticsResult["value"] == "1") {
            return true;
        } else {
            return false;
        }
    }
}
