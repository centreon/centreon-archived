<?php

namespace Centreon\Infrastructure\Service;

/**
 * Class CentcoreConfigService
 *
 * @package Centreon\Infrastructure\Service
 */
class CentcoreConfigService
{

    public const CONF_WEB = 'instCentWeb.conf';

    public const MACROS_DELIMITER_TEMPLATE = '@%s@';

    /**
     * @var array
     */
    private $macros;

    /**
     * Macros getter
     *
     * @return array
     */
    public function getMacros(): array
    {
        if ($this->macros === null) {
            $this->initMacros();
        }

        return $this->macros;
    }

    /**
     * Replace macros with their values
     *
     * @param string $string
     */
    public function replaceMacros(&$string): void
    {
        $macros = $this->getMacros();

        foreach ($macros as $key => $val) {
            $key = str_replace("'", "\\'", $key);
            $macro = sprintf(static::MACROS_DELIMITER_TEMPLATE, $key);

            $string = str_replace($macro, $val, $string);
        }
    }

    private function initMacros(): void
    {
        $data = $this->parseIniFile(_CENTREON_ETC_ . '/' . static::CONF_WEB);

        $this->macros = [
            'centreon_dir' => "{$data['INSTALL_DIR_CENTREON']}/",
            'centreon_etc' => "{$data['CENTREON_ETC']}/",
            'centreon_dir_www' => "{$data['INSTALL_DIR_CENTREON']}/www/",
            'centreon_dir_rrd' => "{$data['INSTALL_DIR_CENTREON']}/rrd/",
            'centreon_log' => "{$data['CENTREON_LOG']}",
            'centreon_cachedir' => "{$data['CENTREON_CACHEDIR']}/",
            'centreon_varlib' => "{$data['CENTREON_VARLIB']}",
            'centreon_group' => "{$data['CENTREON_GROUP']}",
            'centreon_user' => "{$data['CENTREON_USER']}",
            'rrdtool_dir' => "{$data['BIN_RRDTOOL']}",
            'apache_user' => "{$data['WEB_USER']}",
            'apache_group' => "{$data['WEB_GROUP']}",
            'mail' => "{$data['BIN_MAIL']}",
            'broker_user' => "{$data['BROKER_USER']}",
            'broker_group' => 'centreon-broker',
            'broker_etc' => "{$data['BROKER_ETC']}",
            'monitoring_user' => "{$data['MONITORINGENGINE_USER']}",
            'monitoring_group' => "{$data['MONITORINGENGINE_GROUP']}",
            'monitoring_etc' => "{$data['MONITORINGENGINE_ETC']}",
            'monitoring_binary' => "{$data['MONITORINGENGINE_BINARY']}",
            'monitoring_varlog' => "{$data['MONITORINGENGINE_LOG']}",
            'plugin_dir' => "{$data['PLUGIN_DIR']}",
            'address' => hostCentreon,
            'address_storage' => hostCentstorage,
            'port' => port,
            'db' => db,
            'db_user' => user,
            'db_password' => password,
            'db_storage' => dbcstg,
            'monitoring_var_lib' => '/var/lib/centreon-engine',
            'centreon_engine_lib' => '/usr/lib64/centreon-engine',
            'centreon_engine_stats_binary' => '/usr/sbin/centenginestats',
            'centreon_engine_connectors' => '/usr/lib64/centreon-connector',
            'centreonbroker_lib' => '/usr/share/centreon/lib/centreon-broker',
            'centreonbroker_varlib' => '/var/lib/centreon-broker',
            'centreonbroker_log' => '/var/log/centreon-broker',
            'centreonbroker_cbmod' => '/usr/lib64/nagios/cbmod.so',
            'centreonbroker_etc' => '/etc/centreon-broker',
            'centreonplugins' => '/usr/lib/centreon/plugins',
            'centreon_plugins' => '/usr/lib/centreon/plugins',
        ];

        // @todo try to extract missing data from DB

        /*
         * monitoring_var_lib -> cfg_centreonbroker.cache_directory (config_id: 3, config_name: central-module-master)
         * centreon_engine_lib -> broker_module.cfg_nagios_broker_module (cfg_nagios_id)
         * centreon_engine_stats_binary -> nagios_server.nagiostats_bin
         * centreon_engine_connectors -> nagios_server.centreonconnector_path
         * centreonbroker_lib -> nagios_server.centreonbroker_module_path
         * centreonbroker_varlib -> cfg_centreonbroker.cache_directory
         * centreonbroker_log
         * centreonbroker_cbmod
         * centreonbroker_etc
         * centreonplugins -> cfg_resource.resource_line (resource_name: $CENTREONPLUGINS$)
         * centreon_plugins
         */
    }

    private function parseIniFile($filename): array
    {
        $reslt = [];

        try {
            $result = parse_ini_file($filename);
        } catch (\Exception $ex) {
        }

        return $result;
    }
}
