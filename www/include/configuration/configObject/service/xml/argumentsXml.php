<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

    include_once "@CENTREON_ETC@/centreon.conf.php";

    require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

	/*
	 * Declare Function
	 */
	function myDecodeValue($arg) {
		$arg = str_replace('#S#', "/", $arg);
		$arg = str_replace('#BS#', "\\", $arg);
		return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
	}

    /*
	 * start init db
	 */
	$db = new CentreonDB();
	$xml = new CentreonXML();

	$xml->startElement('root');
	$xml->startElement('main');
	$xml->writeElement('argLabel', _('Argument'));
	$xml->writeElement('argValue', _('Value'));
	$xml->writeElement('argExample', _('Example'));
	$xml->writeElement('noArgLabel', _('No argument found for this command'));
	$xml->endElement();

    if (isset($_GET['cmdId']) && isset($_GET['svcId']) && isset($_GET['svcTplId']) && isset($_GET['o'])) {

        $cmdId = CentreonDB::escape($_GET['cmdId']);
        $svcId = CentreonDB::escape($_GET['svcId']);
        $svcTplId = CentreonDB::escape($_GET['svcTplId']);
        $o = CentreonDB::escape($_GET['o']);

        $tab = array();
        if (!$cmdId && $svcTplId) {
            while (1) {
			    $query4 = "SELECT service_template_model_stm_id, command_command_id, command_command_id_arg FROM `service` WHERE service_id = '" . $svcTplId . "'";
			    $res4 = $db->query($query4);
			 	$row4 = $res4->fetchRow();
			 	if (isset($row4['command_command_id']) && $row4['command_command_id']) {
		 			$cmdId = $row4['command_command_id'];
		 			break;
			 	}
			 	if (!isset($row4['service_template_model_stm_id']) || !$row4['service_template_model_stm_id']) {
		 			break;
			 	}
			 	if (isset($tab[$row4['service_template_model_stm_id']])) {
                    break;
			 	}
			 	$svcTplId = $row4['service_template_model_stm_id'];
                $tab[$svcTplId] = 1;
            }
        }

        $argTab = array();

        $query2 = "SELECT command_line, command_example FROM command WHERE command_id = '".$cmdId."' LIMIT 1";
        $res2 = $db->query($query2);
        $row2 = $res2->fetchRow();
        $cmdLine = $row2['command_line'];
        preg_match_all("/\\\$(ARG[0-9]+)\\\$/", $cmdLine, $matches);
        foreach ($matches[1] as $key => $value) {
		    $argTab[$value] = $value;
		}
        $exampleTab = preg_split('!', $row2['command_example']);
        if (is_array($exampleTab)) {
            foreach ($exampleTab as $key => $value) {
                $nbTmp = $key;
                $exampleTab['ARG'.$nbTmp] = $value;
                unset($exampleTab[$key]);
            }
        } else {
            $exampleTab = array();
        }

        $query3 = "SELECT command_command_id_arg " .
                  "FROM service " .
                  "WHERE service_id = '".$svcId."' LIMIT 1";
        $res3 = $db->query($query3);
        if ($res3->numRows()) {
            $row3 = $res3->fetchRow();
            $valueTab = preg_split('!', $row3['command_command_id_arg']);
            if (is_array($valueTab)) {
                foreach($valueTab as $key => $value) {
                    $nbTmp = $key;
                    $valueTab['ARG'.$nbTmp] = $value;
                    unset($valueTab[$key]);
                }
            } else {
                $exampleTab = array();
            }
        }

		$query = "SELECT macro_name, macro_description " .
                 "FROM command_arg_description ".
                 "WHERE cmd_id = '".$cmdId."' ORDER BY macro_name" ;
        $res = $db->query($query);
        while ($row = $res->fetchRow()) {
            $argTab[$row['macro_name']] = $row['macro_description'];
        }
        $res->free();

        /*
         * Write XML
         */
        $style = 'list_two';
        $disabled = 0;
        $nbArg = 0;
        foreach ($argTab as $name => $description) {
            $style == 'list_one' ? $style = 'list_two' : $style = 'list_one';
            if ($o == "w") {
                $disabled = 1;
            }
            $xml->startElement('arg');
            $xml->writeElement('name', $name, false);
            $xml->writeElement('description', $description, false);
            $xml->writeElement('value', isset($valueTab[$name]) ? $valueTab[$name] : "", false);
            $xml->writeElement('example', isset($exampleTab[$name]) ? myDecodeValue($exampleTab[$name]) : "", false);
            $xml->writeElement('style', $style);
            $xml->writeElement('disabled', $disabled);
            $xml->endElement();
            $nbArg++;
        }
    }
    $xml->writeElement('nbArg', $nbArg);
	$xml->endElement();
	header('Content-Type: text/xml');
	$xml->output();
?>