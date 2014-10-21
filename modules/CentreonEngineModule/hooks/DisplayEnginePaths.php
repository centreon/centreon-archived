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
        $template = Di::getDefault()->get('template');
        $template->addCustomJs($js);
        return array(
            'template' => 'displayEnginePaths.tpl',
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

        $paths['conf_dir'] = array(
            'label' => _('Configuration directory'),
            'help' => _('Configuration files will be placed there.'),
            'value' => '/etc/centreon-engine/'
        );

        $paths['log_dir'] = array(
            'label' => _('Log directory'),
            'help' => _('Log files will be placed there.'),
            'value' => '/var/log/centreon-engine/'
        );

        $paths['var_lib_dir'] = array(
            'label' => _('Var Lib directory'),
            'help' => _('Var lib files will be placed there.'),
            'value' => '/var/lib/centreon-engine/'
        );

	$paths['module_dir'] = array(
            'label' => _('Module directory'),
            'help' => _('Event broker modules will be placed there.'),
            'value' => '/usr/lib/centreon-engine/'
        );

	$paths['init_script'] = array(
            'label' => _('Init script'),
            'help' => _('Path of init script'),
            'value' => '/etc/init.d/centengine'
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
        $sql = "SELECT {$columns} FROM cfg_engine WHERE poller_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        foreach ($rows as $k => $v) {
            $paths[$k]['value'] = $v;
        }
        return $paths;
    }
}
