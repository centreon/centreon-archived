<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Centreon\Custom\Module;

/**
 * Description of ModuleInformations
 *
 * @author lionel
 */
class ModuleInformations
{
    /**
     * 
     * @param array $module
     * @return boolean
     */
    public static function checkDependency($module)
    {
        $dependencySatisfied = false;
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $sql = "SELECT name, version FROM module WHERE name = '$module[name]'";
        $res = $db->query($sql);
        $dependency = $res->fetchAll(\PDO::FETCH_ASSOC);
        
        if (is_array($dependency) && count($dependency) > 0) {
            if (version_compare($dependency[0]['version'], $module['version'], '>=')) {
                $dependencySatisfied = true;
            }
        }
        
        return $dependencySatisfied;
    }
    
    /**
     * 
     */
    public static function addInformationsInDb()
    {
        
    }
    
    /**
     * 
     */
    public static function updateInformationsInDb()
    {
        
    }
}
