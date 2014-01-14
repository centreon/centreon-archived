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
namespace Centreon\Core;

class Hook
{
    const DISPLAY_PREFIX = 'display';

    /**
     * Register a hook
     *
     * @param int $moduleId
     * @param string $hookName
     * @param string $blockName
     * @param string $blockDescription
     */
    public static function register($moduleId, $hookName, $blockName, $blockDescription)
    {
        $db = Di::getDefault()->get('db_centreon');
    }

    /**
     * Unregister a hook
     *
     */
    public static function unregister($moduleId, $blockName)
    {
        $db = Di::getDefault()->get('db_centreon');
    }

    /**
     * Execute a hook
     *
     * @param string $hookName
     * @param array $params
     * @todo retrieve registered hooks from modules
     * @return array
     */
    public static function execute($hookName, $params)
    {
        if (!preg_match('/^'.self::DISPLAY_PREFIX.'/', $hookName)) {
            throw new Exception(sprintf('Invalid hook name %s', $hookName));
        }
        $db = Di::getDefault()->get('db_centreon');
        $hooks = array(
            array('module' => 'Dummy')
        );
        $hookData = array();
        $i = 0;
        foreach ($hooks as $hook) {
            $data = call_user_func(array("\\Modules\\".$hook['module'], $hookName), $params);
            if (is_array($data) && count($data) && is_file($data[0])) {
                $hookData[$i]['template'] = $data[0];
                if (isset($data[1]) && is_array($data[1])) {
                    $hookData[$i]['variables'] = $data[1];
                }
                $i++;
            }
        }
        return $hookData;
    }

    /**
     * Init action listeners of modules
     *
     * @todo retrieve list of registered action hooks
     */
    public static function initActionListeners()
    {
        $hooks = array(
            array(
                'module' => 'Dummy',
                'hook_name' => 'actionHostAfterCreate'
            )
        );
        $emitter = Di::getDefault()->get('action_hooks');
        foreach ($hooks as $hook) {
            $emitter->on($hook['hook_name'], function($params) use ($hook) {
                call_user_func(array("\\Modules\\".$hook['module'], $hook['hook_name']), $params);
            });
        }
    }
}
