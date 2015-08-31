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


if (!isset($oreon))
    exit();

if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
        mkdir($nagiosCFGPath.$tab['id']."/");
}

$fileHandler = create_file($nagiosCFGPath.$tab['id']."/connectors.cfg", $oreon->user->get_name());

$str = "";
if ($tab['monitoring_engine'] == 'CENGINE') {
    /* Getting base path for connectors */
    $queryGetPath = 'SELECT centreonconnector_path FROM nagios_server WHERE id = ' . $tab['id'];
    $res = $pearDB->query($queryGetPath);
    $row = $res->fetchRow();
    if ($row['centreonconnector_path'] != '') {
        $connector_basepath = preg_replace('!/$!', '', $row['centreonconnector_path']);
        
        require_once $centreon_path . 'www/class/centreonConnector.class.php';
        $connectorObj = new CentreonConnector($pearDB);
        $connectorList = $connectorObj->getList(true, false, 0, true);
        
        /**
         * Define preg arguments
         */
        $slashesOri = array('/#BR#/',
                            '/#T#/',
                            '/#R#/',
                            '/#S#/',
                            '/#BS#/',
                            '/#P#/');
        $slashesRep = array("\\n",
                            "\\t",
                            "\\r",
                            "/",
                            "\\",
                            "|");
        
        foreach($connectorList as $connector) {
            if (strncmp($connector['command_line'], '/', 1)) {
                $connector['command_line'] = $connector_basepath.'/'.$connector['command_line'];
            }
            $connector['command_line'] = trim(preg_replace($slashesOri, $slashesRep, $connector['command_line']));
            $str .= "define connector{\n";
            $str .= print_line('connector_name', $connector['name']);
            $str .= print_line('connector_line', $connector['command_line']);
            $str .= "}\n\n";
        }
    }
}

write_in_file($fileHandler, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/connectors.cfg");
fclose($fileHandler);
setFileMod($nagiosCFGPath.$tab['id']."/connectors.cfg");

?>
