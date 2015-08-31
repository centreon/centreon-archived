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

    if (!isset($oreon)) {
        exit();
    }

    /*
     * Connect to MySQL
     */
    $pearDBO = new CentreonDB("centstorage");

    require_once $centreon_path . "/www/class/centreonXML.class.php";

    $dir_conf = $centreonBrokerPath . '/' . $tab['id'];

    if (!is_dir($dir_conf) && is_writable($centreonBrokerPath)) {
        mkdir($dir_conf);
    }

    $ns_id = $tab['id'];

    $files = array();
        $eventQueueMaxSize = array();
        
    /*
     * Get the module path for the nagios_server
     */
    $query = "SELECT centreonbroker_module_path FROM nagios_server WHERE id = " . $ns_id;
    $res = $pearDB->query($query);
    $centreonBrokerModulePath = null;
    if (false === PEAR::isError($res) && $res->numRows() == 1) {
        $row = $res->fetchRow();
        if (trim($row['centreonbroker_module_path']) != '') {
            $centreonBrokerModulePath = trim($row['centreonbroker_module_path']);
        }
    }

    /*
     * Init Broker configuration object
     */
    $cbObj = new CentreonConfigCentreonBroker($pearDB);

    $query = "SELECT cs.config_filename, cs.config_write_thread_id, cs.config_write_timestamp, cs.event_queue_max_size, cs.command_file, csi.config_key, csi.config_value, csi.config_id, csi.config_group, csi.config_group_id, csi.grp_level, csi.subgrp_id , ns.name
        FROM cfg_centreonbroker_info csi, cfg_centreonbroker cs, nagios_server ns
        WHERE csi.config_id = cs.config_id AND cs.config_activate = '1' AND cs.ns_nagios_server = ns.id AND csi.grp_level = 0 AND cs.ns_nagios_server = " . $ns_id;

    $res = $pearDB->query($query);

    $blocks = array();
        if (false === PEAR::isError($res) && $res->numRows()) {
        $ns_name = null;
        while ($row = $res->fetchRow()) {
            $filename = $row['config_filename'];
            if (!isset($files[$filename])) {
                foreach ($cbObj->getTags() as $tagId => $tagName) {
                    $files[$filename][$tagName] = array();
                }
            }
        if (is_null($ns_name)) {
            $ns_name = $row['name'];
        }
        if ($row['config_key'] == 'blockId') {
            if (false === isset($blocks[$row['config_value']])) {
                $blocks[$row['config_value']] = array();
            }
            $blocks[$row['config_value']][] = array(
                'filename' => $filename,
                'config_group' => $row['config_group'],
                'config_group_id' => $row['config_group_id']
            );
        }
        $infos = array(
                'key' => $row['config_key'],
        'children' => getChildren($row),
        'values' => $row['config_value']
        );
        $files[$filename][$row['config_group']][$row['config_group_id']][] = $infos;
            $eventQueueMaxSize[$filename] = $row['event_queue_max_size'];
            $logTimestamp[$filename] = $row['config_write_timestamp'];
            $logThreadId[$filename] = $row['config_write_thread_id'];
            $commandFile[$filename] = $row['command_file'];
        }

    /*
     * Replace globals values
     */
    foreach ($blocks as $blockId => $block) {
        list($tagId, $typeId) = explode('_', $blockId);
        $fields = $cbObj->getBlockInfos($typeId);
        foreach ($fields as $field) {
            if (!is_null($field['value'])) {
                $default = $cbObj->getInfoDb($field['value']);
                if (trim($default) != '') {
                    foreach ($block as $infos) {
                        if (isset($files[$infos['filename']][$infos['config_group']][$infos['config_group_id']])) {
                            $nbEl = count($files[$infos['filename']][$infos['config_group']][$infos['config_group_id']]);
                            for ($i = 0; $i < $nbEl; $i++) {
                                if ($files[$infos['filename']][$infos['config_group']][$infos['config_group_id']][$i]['key'] == $field['fieldname']) {
                                    $files[$infos['filename']][$infos['config_group']][$infos['config_group_id']][$i]['values'] = $default;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

        /*
         * Delete all old files.
         */
        foreach (glob("$dir_conf/*") as $filename) {
            unlink($filename);
        }

        
        foreach ($files as $filename => $groups) {
            $fileXml = new CentreonXML(true);
            $fileXml->startElement('centreonBroker');

            $fileXml->writeElement('instance', $ns_id);
            $fileXml->writeElement('instance_name', $ns_name);

            if (!is_null($centreonBrokerModulePath)) {
                $fileXml->writeElement('module_directory', $centreonBrokerModulePath);
            }

            if (isset($eventQueueMaxSize[$filename])) {
                $fileXml->writeElement('event_queue_max_size', $eventQueueMaxSize[$filename]);
            }
            
            if (isset($logTimestamp[$filename])) {
                $fileXml->writeElement('log_timestamp', $logTimestamp[$filename]);
            }
            
            if (isset($logThreadId[$filename])) {
                $fileXml->writeElement('log_thread_id', $logThreadId[$filename]);
            }
            
            if (isset($commandFile[$filename]) && !is_null($commandFile[$filename]) && $commandFile[$filename] != '') {
                $fileXml->writeElement('command_file', $commandFile[$filename]);
            }
            
            foreach ($groups as $group => $listInfos) {
                if (count($listInfos) > 0) {
                    foreach ($listInfos as $key2 => $infos) {
                        $fileXml->startElement($group);
                        foreach ($infos as $value) {
                    writeElement($fileXml, $value);
                        }
                        $fileXml->endElement();
                    }
                }
            }
            $fileXml->endElement();

            // Write Config Files
            $oldumask = umask(0113);
            ob_start();
            $fileXml->output();
            file_put_contents($dir_conf . '/' . $filename, ob_get_contents());
            ob_end_clean();
            umask($oldumask);
        }
    }
?>
