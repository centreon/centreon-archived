<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 */

namespace CentreonBroker\Hooks;

use Centreon\Internal\Di;

class DisplayBrokerPaths
{
    /**
     * Execute action 
     *
     * @param array $params
     */
    public static function execute($params)
    {
        $paths = static::getPathList();
        if (isset($params['pollerId']) && $params['pollerId']) {
            $paths = static::getPathValues($params['pollerId'], $paths);
        }
        return array(
            'template' => 'displayBrokerPaths.tpl',
            'variables' => array(
                'paths' => $paths
            )
        );
    }

    /**
     * Get path list
     *
     * @return array
     */
    protected static function getPathList()
    {
        $paths = array();

        $paths['broker_etc_directory'] = array(
            'label' => _('Configuration directory'),
            'help' => _('Directory to store configuration files for Broker'),
            'value' =>''
        );

        $paths['broker_module_directory'] = array(
            'label' => _('Module directory'),
            'help' => _('Broker module directory.'),
            'value' => ''
        );

        $paths['broker_logs_directory'] = array(
            'label' => _('Log directory'),
            'help' => _('Directory to store log file for Broker'),
            'value' => ''
        );

        $paths['broker_data_directory'] = array(
            'label' => _('Data directory'),
            'help' => _('Directory to store data for Broker'),
            'value' => ''
        );

        return $paths;
    }

    /**
     * Get path values
     *
     * @param int $pollerId
     * @param array $paths
     * @return array
     */
    protected static function getPathValues($pollerId, $paths)
    {
        if (!count($paths)) {
            return $paths;
        }
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT directory_config, directory_modules, directory_data, directory_logs
            FROM cfg_centreonbroker_paths
            WHERE poller_id = :poller_id";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array(':poller_id' => $pollerId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (is_null($row)) {
            return $paths;
        }
        $paths['broker_etc_directory']['value'] = $row['directory_config'];
        $paths['broker_module_directory']['value'] = $row['directory_modules'];
        $paths['broker_logs_directory']['value'] = $row['directory_logs'];
        $paths['broker_data_directory']['value'] = $row['directory_data'];
        return $paths;
    }
}
