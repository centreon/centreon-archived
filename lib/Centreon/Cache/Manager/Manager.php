<?php

require_once 'Zend/Cache.php';

/**
 * Centreon Cache Manager
 *
 * @author sylvestre
 */
class Centreon_Cache_Manager
{
    private static $instances = array();

    /**
     * Factory
     *
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public static function factory($instanceName = "cache", $lifetime = 60, $cacheDir = "/tmp/")
    {
        if (isset(self::$instances[$instanceName])) {
            self::$instances[$instanceName]->clean(Zend_Cache::CLEANING_MODE_OLD);
            return self::$instances[$instanceName];
        }
        $frontEndOptions = array('lifetime' => $lifetime,
                                 'automatic_serialization' => true);
        $backEndOptions = array('cache_dir' => $cacheDir);
        self::$instances[$instanceName] = Zend_Cache::factory('Core', 'File', $frontEndOptions, $backEndOptions);
        return self::$instances[$instanceName];
    }

    /**
     * Get cache file name from sql query
     *
     * @param string $sqlQuery
     * @param array $sqlParams
     * @return string
     */
    public function getCacheFileName($sqlQuery, $sqlParams = array())
    {
        $paramString = implode(",", $sqlParams);
        return md5($sqlQuery.$paramString);
    }
}