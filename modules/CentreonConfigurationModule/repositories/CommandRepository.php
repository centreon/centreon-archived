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
     * @param int $id
     * @return mixed
     */
    public static function getCommandName($id)
    {
        $res = \CentreonConfiguration\Models\Command::get($id, "command_name");
        
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
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        $result = "";

        /* Get Object Stats */
        for ($i = 1; $i != -1; $i--) {
            $stmt = $dbconn->prepare(
                "SELECT count(*) AS number "
                . "FROM $object "
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

    /**
     * Methode tests
     *
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public function generateCheckCommand(& $filesList, $poller_id, $path, $filename)
    {
        $di = \Centreon\Internal\Di::getDefault();
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Filter column that we want to include into the files */
        $enableField = array("command_name" => 1, "command_line" => 1, "command_example" => 1);
        $commentField = array("command_example" => 1);

        /* Get information into the database. */
        $query = "SELECT * FROM command WHERE command_type = 2 ORDER BY command_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "command");
            $tmpData = array();
            foreach ($row as $key => $value) {
                if (isset($enableField[$key])) {
                    if (isset($commentField[$key])) {
                        $key = "#".$key;
                    }
                    if ($key == "command_line" && $row['enable_shell'] == 1) {
                        $value = "/bin/sh -c ".escapeshellarg($value);
                    }
                    $tmpData[$key] = $value;
                }
            }
            
            /*
             * Manage connector
             */
            if (isset($row["connector_id"])) {
                $query = "SELECT `name` FROM `connector` WHERE `connector`.`id` = '".$row["connector_id"]."'";
                $stmt2 = $dbconn->prepare($query);
                $stmt2->execute();
                while ($connector = $stmt2->fetch(\PDO::FETCH_ASSOC)) {
                    $tmpData["connector"] = $connector["name"];
                }
            }

            /*
             * Display arguments used in the command line.
             */
            $query = "SELECT macro_name, macro_description "
                . "FROM command_arg_description "
                . "WHERE cmd_id = '".$row["command_id"]."' "
                . "ORDER BY macro_name";
            $stmt2 = $dbconn->prepare($query);
            $stmt2->execute();
            while ($args = $stmt2->fetch(\PDO::FETCH_ASSOC)) {
                $tmpData[";\$".$args["macro_name"]."\$"] = $args["macro_description"];
            }

            $tmp["content"] = $tmpData;
            $content[] = $tmp;

            unset($tmp);
            unset($tmpData);
            unset($row);
        }

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public function generateMiscCommand(& $filesList, $poller_id, $path, $filename)
    {
        $di = \Centreon\Internal\Di::getDefault();
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Filter column that we want to include into the files */
        $enableField = array("command_name" => 1, "command_line" => 1, "command_example" => 1, );
        $commentField = array("command_example" => 1);

        /* Get information into the database. */
        $query = "SELECT * FROM command WHERE command_type = 1 ORDER BY command_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "command");
            $tmpData = array();
            foreach ($row as $key => $value) {
                if (isset($enableField[$key])) {
                    if (isset($commentField[$key])) {
                        $key = "#".$key;
                    }
                    if ($key == "command_line" && $row['enable_shell'] == 1) {
                        $value = "/bin/sh -c ".escapeshellarg($value);
                    }
                    if ($key == "command_line") {
                        /* TODO : get the real mailer */
                        $value = str_replace("@MAILER@", "/bin/mail", $value);
                    }
                    $tmpData[$key] = $value;
                }
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;

            unset($tmp);
            unset($tmpData);
            unset($row);
        }
        
        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }
}
