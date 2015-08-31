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

    function getLocalRequester() {
        global $pearDB;
        $query = 'SELECT id, name
        	FROM nagios_server
        	WHERE localhost = "1"
        		AND ns_activate = "1"';
        $res = $pearDB->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $row = $res->fetchRow();
        return $row;
    }

    function getListRequester($withLocal = false) {
        global $pearDB;
        $query = 'SELECT id, name
        	FROM nagios_server
        	WHERE ns_activate = "1"';
        if ($withLocal === false) {
            $query .= ' AND localhost != "1"';
        }
        $res = $pearDB->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $list = array();
        while ($row = $res->fetchRow()) {
            $list[] = $row;
        }
        return $list;
    }

    if ($wizard->getValue(1, 'configtype') == 'central_without_poller') {
        $requester = getLocalRequester();
        if (count($requester) != 0) {
            $lang['central_configuration_without_poller'] = _('Central without poller configuration');
            $lang['requester'] = _('Requester');
            $lang['informations'] = _('Information');
            $lang['configuration_name'] = _('Configuration name');
            $lang['additional_daemon'] = _('Additional daemon');
            $lang['protocol'] = _('Serialization protocol');
            $lang['none'] = _('None');
            $tpl->assign('requester', $requester['name']);
            $tpl->assign('requester_id', $requester['id']);
            $page = 'step2_central_without_poller.ihtml';
        } else {
            $tpl->assign('strerr', _('Error while getting the local requester.'));
            $page = 'error.ihtml';
        }
    } elseif ($wizard->getValue(1, 'configtype') == 'central_with_poller') {
        $requester = getLocalRequester();
        if (count($requester) != 0) {
            $lang['central_configuration_with_poller'] = _('Central with poller configuration');
            $lang['requester'] = _('Requester');
            $lang['informations'] = _('Information');
            $lang['prefix_configuration_name'] = _('Prefix configuration name');
            $lang['additional_daemon'] = _('Additional daemon');
            $lang['protocol'] = _('Serialization protocol');
            $tpl->assign('requester', $requester['name']);
            $tpl->assign('requester_id', $requester['id']);
            $page = 'step2_central_with_poller.ihtml';
        } else {
            $tpl->assign('strerr', _('Error while getting the local requester.'));
            $page = 'error.ihtml';
        }
    } elseif ($wizard->getValue(1, 'configtype') == 'poller') {
        $requester_list = getListRequester();
        if (count($requester_list) == 0) {
            $tpl->assign('strerr', _('No active poller is defined in Centreon.'));
            $page = 'error.ihtml';
        } else {
            $lang['requester'] = _('Requester');
            $lang['informations'] = _('Information');
            $lang['configuration_name'] = _('Configuration name');
            $lang['central_address'] = _('Central address');
            $lang['additional_daemon'] = _('Additional daemon');
            $lang['communication_port'] = _('Communication port');
            $lang['protocol'] = _('Serialization protocol');
            $lang['none'] = _('None');
            $tpl->assign('requesters', $requester_list);
            $page = 'step2_poller.ihtml';
        }
    } else {
        $tpl->assign('strerr', _('Bad configuration type'));
        $page = 'error.ihtml';
    }
