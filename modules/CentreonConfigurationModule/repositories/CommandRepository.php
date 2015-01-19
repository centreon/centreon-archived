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

use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use CentreonConfiguration\Repository\Repository;
use CentreonConfiguration\Models\Command;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class CommandRepository extends Repository
{
    const NOTIF_TYPE = 1;
    const CHECK_TYPE = 2;

    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_commands';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Command';
    
    /**
     * 
     * @param int $id
     * @return mixed
     */
    public static function getCommandName($id)
    {
        $res = Command::get($id, "command_name");
        
        if (is_array($res)) {
            $returnedValue = $res['command_name'];
        } else {
            $returnedValue = -1;
        }
        
        return $returnedValue;
    }

    /**
     * 
     * @param int $id
     * @param string $object
     * @return string
     */
    public static function getUseNumber($id, $object)
    {
        $di = Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $result = "";
        
        if ($object == 'host') {
            $objectTable = 'cfg_hosts';
        } elseif ($object == 'service') {
            $objectTable = 'cfg_services';
        }

        /* Get Object Stats */
        for ($i = 1; $i != -1; $i--) {
            $stmt = $dbconn->prepare(
                "SELECT count(*) AS number "
                . "FROM $objectTable "
                . "WHERE (command_command_id = '$id' "
                . "OR command_command_id2 = '$id') "
                . "AND ".$object."_register = '$i'"
            );
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
