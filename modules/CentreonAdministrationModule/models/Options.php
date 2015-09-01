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

namespace CentreonAdministration\Models;

use Centreon\Internal\Di;
use Centreon\Models\CentreonBaseModel;

/**
 * Description of Options
 *
 * @author lionel
 */
class Options extends CentreonBaseModel
{
    public static $table = 'cfg_options';

    /**
     * 
     * @return type
     */
    public static function getOptionsKeysList()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->query("SELECT `key` FROM `cfg_options`");
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $finalList= array();
        foreach ($list as $currentOpt) {
            $finalList[] = $currentOpt['key'];
        }
        
        return $finalList;
    }
    
    /**
     * 
     * @param type $group
     * @param array $options
     * @return type
     */
    public static function getList($group = null, array $options = array())
    {
        $db = Di::getDefault()->get('db_centreon');
        
        $conditions = "";
        if (!is_null($group)) {
            $conditions .= "WHERE `group` = " . $db->quote($group);
        }
        
        if (count($options) > 0) {
            foreach ($options as &$optionKey) {
                $optionKey = $db->quote($optionKey);
            }
            $listOfOptionKeys = implode(',', $options); 
            
            if (empty($conditions)) {
                $conditions .= "WHERE ";
            } else {
                $conditions .= "AND ";
            }
            $conditions .= '`key` IN (' . $listOfOptionKeys . ')';
        }

        $stmt = $db->query("SELECT `key`, `value` FROM `cfg_options` $conditions");
        
        $savedOptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $optionsList = array();
        foreach ($savedOptions as $savedOption) {
            $optionsList[$savedOption['key']] = $savedOption['value'];
        }
        return $optionsList;
    }
    
    /**
     * 
     * @param type $values
     */
    public static function update($values)
    {
        $db = Di::getDefault()->get('db_centreon');
        
        foreach ($values as $key => $value) {
            $sql = "UPDATE `cfg_options` SET `value`='$value' WHERE `key`='$key'";
            $db->exec($sql);
        }
    }
    
    /**
     * 
     * @param type $values
     * @param type $group
     */
    public static function insert($values, $group = "default")
    {
        $db = Di::getDefault()->get('db_centreon');
        
        foreach ($values as $key => $value) {
            $sql = "INSERT INTO `cfg_options`(`group`, `key`, `value`) VALUES('$group', '$key', '$value');";
            $db->exec($sql);
        }
    }
}
