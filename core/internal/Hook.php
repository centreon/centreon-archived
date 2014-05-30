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
namespace Centreon\Internal;

class Hook
{
    const DISPLAY_PREFIX = 'display';
    const TYPE_DISPLAY = 0;
    const TYPE_ACTION = 1;
    private static $hookCache;
    private static $moduleHookCache;

    /**
     * Get hook cache
     *
     * @return array
     */
    private static function getHookCache()
    {
        $db = Di::getDefault()->get('db_centreon');
        if (!isset(self::$hookCache)) {
            self::$hookCache = array('id' => array(), 'name' => array());
            $sql = "SELECT hook_id, hook_name, hook_description FROM hooks";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                self::$hookCache['id'][$row['hook_id']] = $row;
                self::$hookCache['name'][$row['hook_name']] = $row;
            }
        }
        return self::$hookCache;
    }

    /**
     * Get module hook cache
     *
     * @return array
     */
    private static function getModuleHookCache()
    {
        $db = Di::getDefault()->get('db_centreon');
        if (!isset(self::$moduleHookCache)) {
            self::$moduleHookCache = array();
            $sql = "SELECT module_id, hook_id, module_hook_name, module_hook_description
                FROM module_hooks";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $unique = implode(
                    "_",
                    array(
                        $row['module_id'],
                        $row['hook_id'],
                        $row['module_hook_name']
                    )
                );
                self::$moduleHookCache[$unique] = $row;
            }
        }
        return self::$moduleHookCache;
    }

    /**
     * Get hook id from hook name
     * 
     * @param string $hookName
     * @return int 
     * @throws \Centreon\Exception
     */
    public static function getHookId($hookName)
    {
        $hooks = self::getHookCache();
        if (isset($hooks['name'][$hookName]) && isset($hooks['name'][$hookName]['hook_id'])) {
            return $hooks['name'][$hookName]['hook_id'];
        } else {
            throw new Exception(sprintf(_('Could not find hook named %s'), $hookName));
        }
    }

    /**
     * Get hook name from hook id
     *
     * @param string $hookId
     * @return string
     */
    public static function getHookName($hookId)
    {
        $hooks = self::getHookCache();
        if (isset($hooks['id'][$hookId]) && isset($hooks['id'][$hookId]['hook_name'])) {
            return $hooks['id'][$hookId]['hook_name'];
        } else {
            throw new Exception(sprintf(_('Could not find hook id %s'), $hookId));
        }
    }

    /**
     * Register a hook
     *
     * @param int $moduleId
     * @param string $hookName
     * @param string $moduleHookName
     * @param string $moduleHookDescription
     * @throws \Centreon\Exception
     */
    public static function register($moduleId, $hookName, $moduleHookName, $moduleHookDescription)
    {
        $unique = implode("_", array($moduleId, self::getHookId($hookName), $moduleHookName));
        $moduleHookCache = self::getModuleHookCache();
        if (isset($moduleHookCache[$unique])) {
            throw new Exception(_('Hook already registered'));
        }
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO module_hooks 
            (module_id, hook_id, module_hook_name, module_hook_description) VALUES
            (?, ?, ?, ?)";
        $arr = array(
            'module_id' => $moduleId,
            'hook_id' => self::getHookId($hookName),
            'module_hook_name' => $moduleHookName,
            'module_hook_description' => $moduleHookDescription
        );
        $stmt = $db->prepare($sql);
        $sqlarr = array();
        foreach ($arr as $elem) {
            $sqlarr[] = $elem;
        }
        $stmt->execute($sqlarr);
        self::$moduleHookCache[$unique] = $arr;
    }

    /**
     * Unregister a hook
     *
     * @param int $moduleId
     * @param string $hookName
     * @param string $moduleHookName
     * @throws \Centreon\Exception
     */
    public static function unregister($moduleId, $hookName, $moduleHookName)
    {
        $hookId = self::getHookId($hookName);
        $unique = implode("_", array($moduleId, $hookId, $moduleHookName));
        $moduleHookCache = self::getModuleHookCache();
        if (!isset($moduleHookCache[$unique])) {
            throw new Exception(sprintf(_('Could not find module hook named %s'), $moduleHookName));
        }
        $db = Di::getDefault()->get('db_centreon');
        $db->prepare(
            "DELETE FROM module_hooks 
            WHERE module_id = ? 
            AND hook_id = ? 
            AND module_hook_name = ?"
        );
        $db->execute(array($moduleId, $hookId, $moduleHookName));
        unset(self::$moduleHookCache[$unique]);
    }

    /**
     * Get modules from hook
     *
     * @param string $hookName
     * @return array
     */
    public static function getModulesFromHook($hookType = null, $hookName = null)
    {
        $filters = array();
        $sql = "SELECT m.name AS module, h.hook_name, module_hook_name
            FROM module m, hooks h, module_hooks mh 
            WHERE m.id = mh.module_id
            AND mh.hook_id = h.hook_id";
        if (!is_null($hookName)) {
            $sql .= " AND h.hook_name = ? ";
            $filters[] = $hookName;
        }
        if (!is_null($hookType)) {
            $sql .= " AND h.hook_type = ? ";
            $filters[] = $hookType;
        }
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute($filters);
        return $stmt->fetchAll();
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
        $hooks = self::getModulesFromHook(self::TYPE_DISPLAY, $hookName);
        $hookData = array();
        $i = 0;
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        foreach ($hooks as $hook) {
            $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $hook['module'])));
            $filename = "$path/modules/{$commonName}Module/hooks/".ucfirst($hook['module_hook_name']).".php"; 
            if (file_exists($filename)) {
                include_once $filename;
                $data = call_user_func(array("\\".$commonName."\\".ucfirst($hook['module_hook_name']), "execute"), $params);
                $templateFile = "$path/modules/{$commonName}Module/views/{$data[0]}";
                if (is_array($data) && count($data) && is_file($templateFile)) {
                    $hookData[$i]['template'] = $data[0];
                    $hookData[$i]['variables'] = array();
                    if (isset($data[1]) && is_array($data[1])) {
                        $hookData[$i]['variables'] = $data[1];
                    }
                    $i++;
                }
            }
        }
        return $hookData;
    }

    /**
     * Init action listeners of modules
     *
     */
    public static function initActionListeners()
    {
        $hooks = self::getModulesFromHook(self::TYPE_ACTION);
        $emitter = Di::getDefault()->get('action_hooks');
        foreach ($hooks as $hook) {
            $emitter->on(
                $hook['hook_name'],
                function ($params) use ($hook) {
                    call_user_func(array("\\Modules\\".$hook['module'], $hook['hook_name']), $params);
                }
            );
        }
    }
}
