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

namespace CentreonConfiguration\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class CommandRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'command';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Command';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allCommand" class="allCommand" type="checkbox">' => 'command_id',
        'Name' => 'command_name',
        'Command Line' => 'command_line',
        'Type' => 'command_type',
        'Host Use' => 'NULL AS host_use',
        'Service Use' => 'NULL AS svc_use',
    );
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $researchIndex = array(
        'command_id',
        'command_name',
        'command_line',
        'command_type',
        'none',
        'none'
    );
    
    public static $columnCast = array(
        'command_type' => array(
            'type' => 'select',
            'parameters' => array(
                '1' => 'Notifications',
                '2' => 'Check',
                '3' => 'Miscelleanous',
                '4' => 'Discovery',
            )
        ),
        'command_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::command_name::'
            )
        ),
        'command_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/command/[i:id]',
                'routeParams' => array(
                    'id' => '::command_id::'
                ),
                'linkName' => '::command_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_line',
        array(
            'select' => array(
                'Check' => '2',
                'Notifications' => '1',
                'Miscelleanous' => '3',
                'Discovery' => '4'
                              )
              ),
        'none',
        'none'
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'search',
        'search',
        array('select' => array(
                                'Check' => '2',
                                'Notifications' => '1',
                                'Miscelleanous' => '3',
                                'Discovery' => '4'
                                )
              ),
        'none',
        'none'
        
    );

    /**
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myCmdSet) {
            $myCmdSet['host_use'] = static::getUseNumber($myCmdSet["command_id"], "host");
            $myCmdSet['svc_use'] = static::getUseNumber($myCmdSet["command_id"], "service");
        }
    }
    
    public static function getCommandName($id) 
    {
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        /* Get Command name */
        $stmt = $dbconn->prepare("SELECT command_name FROM command WHERE command_id = '$id'");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (isset($row["command_name"])) {
            return $row["command_name"];
        } else {
            return -1;
        }
    }

    public static function getUseNumber($id, $object) 
    {
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $result = "";

        /* Get Object Stats */
        for ($i = 1; $i != -1; $i--) {
            $stmt = $dbconn->prepare("SELECT count(*) AS number FROM $object WHERE (command_command_id = '$id' OR command_command_id2 = '$id') AND ".$object."_register = '$i'");
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (isset($row["number"])) {
                if ($i) {
                    $result .= $row["number"];
                } else {
                    $result .= " (".$row["number"].")";
                }
            } 
        }
        return $result;
    }
}
