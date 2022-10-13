<?php
/*
 * Copyright 2005-2018 Centreon
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
 * this program; if not, see <htcommand://www.gnu.org/licenses>.
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
 * For more information : command@centreon.com
 *
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/centreonUUID.class.php';
require_once __DIR__ . '/centreonGMT.class.php';
require_once __DIR__ . '/centreonVersion.class.php';
require_once __DIR__ . '/centreonDB.class.php';
require_once __DIR__ . '/centreonStatsModules.class.php';

use Psr\Log\LoggerInterface;

class CentreonStatistics
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * CentreonStatistics constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->dbConfig = new centreonDB();
        $this->logger = $logger;
    }

    /**
     * get Centreon UUID
     *
     * @return array
     */
    public function getCentreonUUID()
    {
        $centreonUUID = new CentreonUUID($this->dbConfig);
        return array(
            'CentreonUUID' => $centreonUUID->getUUID()
        );
    }

    /**
     * get Centreon information
     *
     * @return array
     */
    public function getPlatformInfo()
    {

        $query = "SELECT COUNT(h.host_id) as nb_hosts, " .
            "(SELECT COUNT(hg.hg_id) FROM hostgroup hg " .
            "WHERE hg.hg_activate = '1') as nb_hg, " .
            "(SELECT COUNT(s.service_id) FROM service s " .
            "WHERE s.service_activate = '1' AND s.service_register = '1') as nb_services, " .
            "(SELECT COUNT(sg.sg_id) FROM servicegroup sg " .
            "WHERE sg.sg_activate = '1') as nb_sg, " .
            "@nb_remotes:=(SELECT COUNT(ns.id) FROM nagios_server ns, remote_servers rs WHERE ns.ns_activate = '1' " .
            "AND rs.server_id = ns.id) as nb_remotes , " .
            "((SELECT COUNT(ns2.id) FROM nagios_server ns2 WHERE ns2.ns_activate = '1')-@nb_remotes-1) as nb_pollers," .
            " '1' as nb_central " .
            "FROM host h WHERE h.host_activate = '1' AND h.host_register = '1'";
        $dbResult = $this->dbConfig->query($query);
        $data = $dbResult->fetch();

        return $data;
    }

    /**
     * get version of Centreon Web
     *
     * @return array
     * @throws Exception
     */
    public function getVersion()
    {
        $dbStorage = new CentreonDB("centstorage");
        $centreonVersion = new CentreonVersion($this->dbConfig, $dbStorage);
        return array(
            'core' => $centreonVersion->getCore(),
            'modules' => $centreonVersion->getModules(),
            'widgets' => $centreonVersion->getWidgets(),
            'system' => $centreonVersion->getSystem(),
        );
    }

    /**
     * get Centreon timezone
     *
     * @return array
     */
    public function getPlatformTimezone()
    {
        $oTimezone = new CentreonGMT($this->dbConfig);
        $defaultTimezone = $oTimezone->getCentreonTimezone();
        $timezoneById = $oTimezone->getList();

        if (!empty($defaultTimezone) && !empty($timezoneById[$defaultTimezone])) {
            $timezone = $timezoneById[$defaultTimezone];
        } else {
            $timezone = date_default_timezone_get();
        }

        return array(
            'timezone' => $timezone
        );
    }

    /**
     * get LDAP configured authentications options
     *
     * @return array
     */
    public function getLDAPAuthenticationOptions()
    {
        $data = [];

        # Get the number of LDAP directories configured by LDAP configuration
        $query = "SELECT ar.ar_id, COUNT(arh.auth_ressource_id) AS configured_ad
        FROM auth_ressource_host AS arh
        INNER JOIN auth_ressource AS ar ON (arh.auth_ressource_id = ar.ar_id)
        WHERE ar.ar_enable = '1'
        GROUP BY ar_id";
        $result = $this->dbConfig->query($query);
        while ($row = $result->fetch()) {
            $data[$row['ar_id']] = [
                "nb_ar_servers" => $row['configured_ad']
            ];
        }

        # Get configured options by LDAP configuration
        $query = "SELECT ar.ar_id, ari.ari_name, ari.ari_value
        FROM auth_ressource_host AS arh
        INNER JOIN auth_ressource AS ar ON (arh.auth_ressource_id = ar.ar_id)
        INNER JOIN auth_ressource_info AS ari ON (ari.ar_id = ar.ar_id)
        WHERE ari.ari_name IN ('ldap_template', 'ldap_auto_sync', 'ldap_sync_interval', 'ldap_auto_import',
            'ldap_search_limit', 'ldap_search_timeout', 'ldap_srv_dns', 'ldap_store_password', 'protocol_version')";
        $result = $this->dbConfig->query($query);
        while ($row = $result->fetch()) {
            $data[$row['ar_id']][$row['ari_name']] = $row['ari_value'];
        }

        return $data;
    }

    /**
     * get Local / SSO configured authentications options
     *
     * @return array
     */
    public function getNewAuthenticationOptions()
    {
        $data = [];

        $query = "SELECT * FROM provider_configuration WHERE is_active = '1'";
        $result = $this->dbConfig->query($query);
        while ($row = $result->fetch()) {
            $custom_configuration = json_decode($row['custom_configuration'], true);
            if ($row['type'] === 'local') {
                $data['local'] = $custom_configuration['password_security_policy'];
            } elseif ($row['type'] === 'web-sso') {
                $data['web-sso'] = [
                    'is_forced' => $row['is_forced'] ? true : false,
                    'trusted_client_addresses' => count($custom_configuration['trusted_client_addresses']) ?? 0,
                    'blacklist_client_addresses' => count($custom_configuration['blacklist_client_addresses']) ?? 0,
                    'pattern_matching_login' => ($custom_configuration['pattern_matching_login'] ? true : false),
                    'pattern_replace_login' => ($custom_configuration['pattern_replace_login'] ? true : false),
                ];
            } elseif ($row['type'] === 'openid') {
                $data['openid'] = [
                    'is_forced' => $row['is_forced'] ? true : false,
                    'trusted_client_addresses' => count($custom_configuration['trusted_client_addresses']) ?? 0,
                    'blacklist_client_addresses' => count($custom_configuration['blacklist_client_addresses']) ?? 0,
                    'introspection_token_endpoint' => ($custom_configuration['introspection_token_endpoint'] ? true : false),
                    'userinfo_endpoint' => ($custom_configuration['userinfo_endpoint'] ? true : false),
                    'endsession_endpoint' => ($custom_configuration['endsession_endpoint'] ? true : false),
                    'connection_scopes' => count($custom_configuration['connection_scopes']) ?? 0,
                    'authentication_type' => $custom_configuration['authentication_type'],
                    'verify_peer' => ($custom_configuration['verify_peer'] ? true : false),
                    'auto_import' => ($custom_configuration['auto_import'] ? true : false)
                ];
            }
        }

        return $data;
    }

    /**
     * get configured authentications options
     *
     * @return array
     */
    public function getAuthenticationOptions()
    {
        $data = array_merge(
            ['LDAP' => $this->getLDAPAuthenticationOptions()],
            [$this->getNewAuthenticationOptions()][0]
        );

        return $data;
    }

    /**
     * Get Additional data
     *
     * @return array
     */
    public function getAdditionalData()
    {
        $centreonVersion = new CentreonVersion($this->dbConfig);

        $data = array(
            'extension' => array(
                'widgets' => $centreonVersion->getWidgetsUsage()
            ),
        );

        $oModulesStats = new CentreonStatsModules($this->logger);
        $modulesData = $oModulesStats->getModulesStatistics();
        foreach ($modulesData as $moduleData) {
            $data['extension'] = array_merge($data['extension'], $moduleData);
        }

        return $data;
    }
}
