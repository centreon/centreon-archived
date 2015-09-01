<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace Centreon\Internal;

class Hook
{
    const DISPLAY_PREFIX = 'display';
    const TYPE_DISPLAY = 0;
    const TYPE_ACTION = 1;
    private static $hookCache;
    private static $moduleHookCache;


    /**
     * Reset static properties
     */
    public static function reset()
    {
        static::$hookCache = null;
        static::$moduleHookCache = null;
    }

    /**
     * Get hook id from hook name
     * 
     * @param string $hookName
     * @return int 
     * @throws \Centreon\Internal\Exception
     */
    public static function getHookId($hookName)
    {
        $hooks = self::getHookCache(true);
        if (isset($hooks['name'][$hookName]) && isset($hooks['name'][$hookName]['hook_id'])) {
            return $hooks['name'][$hookName]['hook_id'];
        } else {
            throw new Exception(sprintf('Could not find hook named %s', $hookName));
        }
    }

    /**
     * Get hook name from hook id
     *
     * @param string $hookId
     * @return string
     * @throws \Centreon\Internal\Exception
     */
    public static function getHookName($hookId)
    {
        $hooks = self::getHookCache();
        if (isset($hooks['id'][$hookId]) && isset($hooks['id'][$hookId]['hook_name'])) {
            return $hooks['id'][$hookId]['hook_name'];
        } else {
            throw new Exception(sprintf('Could not find hook id %s', $hookId));
        }
    }
    
    /**
     * 
     * @param type $hookName
     * @param type $hookDescription
     */
    public static function insertHook($hookName, $hookDescription)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO cfg_hooks 
            (hook_name, hook_description) VALUES
            (?, ?)";
        
        $arr = array(
            'hook_name' => $hookName,
            'hook_description' => $hookDescription
        );
        $stmt = $db->prepare($sql);
        $sqlarr = array();
        foreach ($arr as $elem) {
            $sqlarr[] = $elem;
        }
        $stmt->execute($sqlarr);
    }

    /**
     * Register a hook
     *
     * @param int $moduleId
     * @param string $hookName
     * @param string $moduleHookName
     * @param string $moduleHookDescription
     * @throws \Centreon\Internal\Exception
     */
    public static function register($moduleId, $hookName, $moduleHookName, $moduleHookDescription)
    {
        $unique = implode("_", array($moduleId, self::getHookId($hookName), $moduleHookName));
        $moduleHookCache = self::getModuleHookCache();
        if (isset($moduleHookCache[$unique])) {
            throw new Exception('Hook already registered');
        }
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO cfg_modules_hooks 
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
     * @throws \Centreon\Internal\Exception
     */
    public static function unregister($moduleId, $hookName, $moduleHookName)
    {
        $hookId = self::getHookId($hookName);
        $unique = implode("_", array($moduleId, $hookId, $moduleHookName));
        $moduleHookCache = self::getModuleHookCache();
        if (isset($moduleHookCache[$unique])) {
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare(
                "DELETE FROM cfg_modules_hooks 
                WHERE module_id = ? 
                AND hook_id = ? 
                AND module_hook_name = ?"
            );
            $stmt->execute(array($moduleId, $hookId, $moduleHookName));
            unset(self::$moduleHookCache[$unique]);
        }
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
            FROM cfg_modules m, cfg_hooks h, cfg_modules_hooks mh 
            WHERE m.id = mh.module_id
            AND mh.hook_id = h.hook_id";
        if (!is_null($hookName)) {
            $sql .= " AND h.hook_name = ? ";
            $filters[] = $hookName;
        }
        if (!is_null($hookType)) {
            if (!in_array($hookType, array(static::TYPE_DISPLAY, static::TYPE_ACTION))) {
                throw new Exception(sprintf('Unknown hook type %s', $hookType));
            }
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
     * @todo retrieve registered hooks FROM cfg_moduless
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
                $data = call_user_func(
                    array("\\".$commonName."\\Hooks\\".ucfirst($hook['module_hook_name']), "execute"),
                    $params
                );
                /* has template */

                if (is_array($data) && count($data) && isset($data[0]) && 
                    is_file("$path/modules/{$commonName}Module/views/{$data[0]}")) {
                    $hookData[$i]['template'] = $data[0];
                    $hookData[$i]['variables'] = array();
                    if (isset($data[1]) && is_array($data[1])) {
                        $hookData[$i]['variables'] = $data[1];
                    }
                } else { /* has no template */
                    $hookData[$i] = $data;
                }
                $i++;
            }
        }
        return $hookData;
    }

    /**
     * Add to template the list of static file (js / css)
     *
     * @param string $hookName The hook name
     */
    public static function addStaticFile($hookName)
    {
        if (!preg_match('/^'.self::DISPLAY_PREFIX.'/', $hookName)) {
            throw new Exception(sprintf('Invalid hook name %s', $hookName));
        }
        $hooks = self::getModulesFromHook(self::TYPE_DISPLAY, $hookName);
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $tpl = Di::getDefault()->get('template');
        foreach ($hooks as $hook) {
            $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $hook['module'])));
            $filename = "$path/modules/{$commonName}Module/hooks/".ucfirst($hook['module_hook_name']).".php";
            if (file_exists($filename)) {
                include_once $filename;
                $classname = "\\" . $commonName . "\\Hooks\\" . ucfirst($hook['module_hook_name']);
                if (method_exists($classname, 'addJs')) {
                    call_user_func(array($classname, 'addJs'), $tpl);
                }
                if (method_exists($classname, 'addCss')) {
                    call_user_func(array($classname, 'addCss'), $tpl);
                }
            }
        }
    }

    /**
     * Get hook cache
     *
     * @return array
     */
    private static function getHookCache($force = false)
    {
        $db = Di::getDefault()->get('db_centreon');
        if (!isset(self::$hookCache) || $force) {
            self::$hookCache = array('id' => array(), 'name' => array());
            $sql = "SELECT hook_id, hook_name, hook_description FROM cfg_hooks";
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
                FROM cfg_modules_hooks";
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
}
