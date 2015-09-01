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

namespace CentreonEngine\Repository;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class CommandRepository
{
    const NOTIF_TYPE = 1;
    const CHECK_TYPE = 2;

    /**
     *
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public static function generate(& $filesList, $poller_id, $path, $filename, $cmdType)
    {
        if ($cmdType == self::CHECK_TYPE) {
            self::generateCheckCommand($filesList, $poller_id, $path, $filename);
        } elseif ($cmdType == self::NOTIF_TYPE) {
            self::generateMiscCommand($filesList, $poller_id, $path, $filename);
        } else {
            throw new Exception(sprintf('Unknown command type %s', $cmdType));
        }
    }

    /**
     * Methode tests
     *
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    private function generateCheckCommand(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Filter column that we want to include into the files */
        $enableField = array("command_name" => 1, "command_line" => 1, "command_example" => 1);
        $commentField = array("command_example" => 1);

        /* Get information into the database. */
        $query = "SELECT * FROM cfg_commands WHERE command_type = 2 ORDER BY command_name";
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
                $query = "SELECT `name` FROM `cfg_connectors` WHERE `id` = '".$row["connector_id"]."'";
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
                . "FROM cfg_commands_args_description "
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
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    private function generateMiscCommand(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Filter column that we want to include into the files */
        $enableField = array("command_name" => 1, "command_line" => 1, "command_example" => 1, );
        $commentField = array("command_example" => 1);

        /* Get information into the database. */
        $query = "SELECT * FROM cfg_commands WHERE command_type = 1 ORDER BY command_name";
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
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
