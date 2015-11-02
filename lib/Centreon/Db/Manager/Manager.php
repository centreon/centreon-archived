<?php

require_once 'Zend/Db.php';

/**
 * Centreon Database Manager
 *
 * @author sylvestre
 */
class Centreon_Db_Manager
{
    private static $instances = array();

    /**
     * Factory
     *
     * @param string $instanceName
     * @param string $adapter
     * @param array $config
     * @return Zend_Db_Adapter_Abstract
     */
    public static function factory($instanceName = "centreon", $adapter = "pdo_mysql", $config = array())
    {
        if (!isset($instanceName)) {
            throw new Exception('Missing instance name');
        }
        if (isset(self::$instances[$instanceName])) {
            return self::$instances[$instanceName];
        }
        if (!isset($adapter) || !isset($config) || !is_array($config)) {
            throw new Exception('Missing adapter name and/or configuration array');
        }
        $db = Zend_Db::factory($adapter, $config);
        self::$instances[$instanceName] = Zend_Db::factory($adapter, $config);
        return self::$instances[$instanceName];
    }
}
?>