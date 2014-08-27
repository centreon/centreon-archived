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
class TimePeriodRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'timeperiod';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Timeperiod';
    
    /**
     *
     * @var array Main database field to get
     */
    public static $datatableColumn = array(
        '<input id="allTimeperiod" class="allTimeperiod" type="checkbox">' => 'tp_id',
        'Name' => 'tp_name',
        'Alias' => 'tp_alias',
        'Sunday' => 'tp_sunday',
        'Monday' => 'tp_monday',
        'Tuesday' => 'tp_tuesday',
        'Wednesday' => 'tp_wednesday',
        'Thursday' => 'tp_thursday',
        'Friday' => 'tp_friday',
        'Saturday' => 'tp_saturday'
    );
    
    /**
     *
     * @var array Column name for the search index
     */
    public static $researchIndex = array(
        'tp_id',
        'tp_name',
        'tp_alias',
        'tp_sunday',
        'tp_monday',
        'tp_tuesday',
        'tp_wednesday',
        'tp_thursday',
        'tp_friday',
        'tp_saturday'
    );
    
    /**
     * @inherit doc
     * @var array 
     */
    public static $columnCast = array(
        'tp_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::tp_name::'
            )
        ),
        'tp_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/timeperiod/[i:id]',
                'routeParams' => array(
                    'id' => '::tp_id::'
                ),
                'linkName' => '::tp_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        'none',
        'none',
        'none',
        'none',
        'none'
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        'none',
        'none',
        'none',
        'none',
        'none'
    );

    /**
     * 
     * @param int $tp_id
     * @return string
     */
    public static function getPeriodName($tp_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $contactList = "";

        $query = "SELECT tp_name FROM timeperiod WHERE tp_id = '$tp_id'";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row["tp_name"];
        }
        return "";
    }

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public function generate(& $filesList, $poller_id, $path, $filename)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $enableField = array("tp_id" => 1);
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT * FROM timeperiod ORDER BY tp_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "timeperiod");
            $tmpData = array();
            foreach ($row as $key => $value) {
                if ($key == 'tp_name') {
                    $key = "timeperiod_name";
                }
                if (!isset($enableField[$key]) && $value != "") {
                    $key = str_replace("tp_", "", $key);
                    $tmpData[$key] = $value;
                }
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }
}
