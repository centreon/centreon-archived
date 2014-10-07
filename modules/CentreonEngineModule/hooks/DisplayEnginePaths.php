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

namespace CentreonEngine\Hooks;

use Centreon\Internal\Di;

class DisplayEnginePaths
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
        $js = '
            $(".param-help").each(function() {
                $(this).qtip({
                    content: {
                        text: $(this).data("help"),
                        title: $(this).data("helptitle"),
                        button: true
                    },
                    position: {
                        my: "top right",
                        at: "bottom left",
                        target: $(this)
                    },
                    show: {
                        event: "click",
                        solo: "true"
                    },
                    style: {
                        classes: "qtip-bootstrap"
                    },
                    hide: {
                        event: "unfocus"
                    }
                });
            });';
        return array(
            'template' => 'displayEnginePaths.tpl',
            'variables' => array(
                'paths' => $paths,
                'js' => $js
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

        $paths['resource_file'] = array(
            'label' => _('Resource file'),
            'help' => _('Resource file path.'),
            'value' => ''
        );
        
        $paths['state_retention_file'] = array(
            'label' => _('State retention file'),
            'help' => _('State retention file path.'),
            'value' => ''
        );

        $paths['status_file'] = array(
            'label' => _('Status file'),
            'help' => _('Status file path.'),
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
        $db = Di::getDefault()->get('db_centreon');
        $columns = implode(', ', array_keys($paths));
        $sql = "SELECT {$columns} FROM cfg_engine WHERE engine_server_id ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $rows = $stmt->fetchAll();
        foreach ($rows as $k => $v) {
            $paths[$k]['value'] = $v;
        }
        return $paths;
    }
}
