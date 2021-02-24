<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
require_once dirname(__FILE__) . "/formConnectorFunction.php";

try {
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl);
    
    $cnt = array();
    if (($o == "c" || $o == "w") && isset($connector_id)) {
        $cnt = $connectorObj->read((int)$connector_id);
        $cnt['connector_name'] = $cnt['name'];
        $cnt['connector_description'] = $cnt['description'];
        $cnt['command_line'] = $cnt['command_line'];

        if ($cnt['enabled']) {
            $cnt['connector_status'] = '1';
        } else {
            $cnt['connector_status'] = '0';
        }
        $cnt['connector_id'] = $cnt['id'];

        unset($cnt['name']);
        unset($cnt['description']);
        unset($cnt['status']);
        unset($cnt['id']);
    }

    /*
     * Resource Macro
     */
    $resource = array();
    $DBRESULT = $pearDB->query("SELECT DISTINCT `resource_name`, `resource_comment` 
                                FROM `cfg_resource` 
                                ORDER BY `resource_line`");
    while ($row = $DBRESULT->fetchRow()) {
        $resource[$row["resource_name"]] = $row["resource_name"];
        if (isset($row["resource_comment"]) && $row["resource_comment"] != "") {
            $resource[$row["resource_name"]] .= " (".$row["resource_comment"].")";
        }
    }
    unset($row);
    $DBRESULT->closeCursor();

    /*
     * Nagios Macro
     */
    $macros = array();
    $DBRESULT = $pearDB->query("SELECT `macro_name` FROM `nagios_macro` ORDER BY `macro_name`");
    while ($row = $DBRESULT->fetchRow()) {
        $macros[$row["macro_name"]] = $row["macro_name"];
    }
    unset($row);
    $DBRESULT->closeCursor();

    $availableConnectors_list = return_plugin((isset($oreon->optGen["cengine_path_connectors"]) ? $oreon->optGen["cengine_path_connectors"] : null));

    $form = new HTML_QuickFormCustom('Form', 'post', "?p=".$p);

    $form->addElement('header', 'information', _('General information'));
    if ($o == "a") {
        $form->addElement('header', 'title', _("Add a Connector"));
    } elseif ($o == "c") {
        $form->addElement('header', 'title', _("Modify a Connector"));
    } elseif ($o == "w") {
        $form->addElement('header', 'title', _("View a Connector"));
    }

    $attrsText        = array("size"=>"35");
    $attrsTextarea    = array("rows"=>"9", "cols"=>"65", "id"=>"command_line");
    $attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
    $attrCommands = array(
        'datasourceOrigin' => 'ajax',
        'multiple' => true,
        'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?'
        . 'object=centreon_configuration_command&action=defaultValues&target=connector&field=command_id&id='
        . (isset($connector_id) ? $connector_id : ''),
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?'
        . 'object=centreon_configuration_command&action=list',
        'linkedObject' => 'centreonCommand'
    );

    $form->addElement('text', 'connector_name', _("Connector Name"), $attrsText);
    $form->addElement('text', 'connector_description', _("Connector Description"), $attrsText);
    $form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);

    $form->addElement('select', 'resource', null, $resource);
    $form->addElement('select', 'macros', null, $macros);
    ksort($availableConnectors_list);
    $form->addElement('select', 'plugins', null, $availableConnectors_list);

    $form->addElement('select2', 'command_id', _("Used by command"), array(), $attrCommands);

    $cntStatus = array();
    $cntStatus[] = $form->createElement('radio', 'connector_status', null, _("Enabled"), '1');
    $cntStatus[] = $form->createElement('radio', 'connector_status', null, _("Disabled"), '0');
    $form->addGroup($cntStatus, 'connector_status', _("Connector Status"), '&nbsp;&nbsp;');

    if (isset($cnt['connector_status']) && is_numeric($cnt['connector_status'])) {
        $form->setDefaults(array('connector_status' => $cnt['connector_status']));
    } else {
        $form->setDefaults(array('connector_status' => '0'));
    }

    if ($o == "w") {
        if ($centreon->user->access->page($p) != 2) {
            $form->addElement(
                "button",
                "change",
                _("Modify"),
                array(
                    "onClick"=>"javascript:window.location.href='?p="
                        .$p."&o=c&connector_id=".$connector_id."&status=".$status."'"
                )
            );
        }
        $form->setDefaults($cnt);
        $form->freeze();
    } elseif ($o == "c") {
        $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
        $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
        $form->setDefaults($cnt);
    } elseif ($o == "a") {
        $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
        $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    }
    
    $form->addRule('connector_name', _("Name"), 'required');
    $form->addRule('command_line', _("Command Line"), 'required');
    $form->registerRule('exist', 'callback', 'testConnectorExistence');
    $form->addRule('connector_name', _("Name is already in use"), 'exist');
    $form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));
    $form->addElement('hidden', 'connector_id');
    $redirect = $form->addElement('hidden', 'o');
    $redirect->setValue($o);

    $valid = false;
    if ($form->validate()) {
        $cntObj = new CentreonConnector($pearDB);
        $tab = $form->getSubmitValues();
        $connectorValues = array();
        $connectorValues['name'] = filter_var($tab['connector_name'], FILTER_SANITIZE_STRING);
        $connectorValues['description'] = filter_var($tab['connector_description'], FILTER_SANITIZE_STRING);
        $connectorValues['enabled'] = $tab['connector_status']['connector_status'] === '0' ? 0 : 1;
        $connectorValues['command_id'] = isset($tab['command_id']) ? $tab['command_id'] : null;
        $connectorValues['command_line'] = $tab['command_line'];
        $connectorId = (int)$tab['connector_id'];

        if (!empty($connectorValues['name'])) {
            if ($form->getSubmitValue("submitA")) {
                $connectorId = $cntObj->create($connectorValues, true);
            } elseif ($form->getSubmitValue("submitC")) {
                $cntObj->update((int)$connectorId, $connectorValues);
            }
            $valid = true;
        }
    }

    if ($valid) {
        require_once($path."listConnector.php");
    } else {
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
        $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        $form->accept($renderer);
        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('o', $o);
        $tpl->assign(
            "helpattr",
            'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange",'
            . 'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH,'
            . '-300, SHADOW, true, TEXTALIGN, "justify"'
        );
        $helptext = "";
        include_once("help.php");
        foreach ($help as $key => $text) {
            $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
        }
        $tpl->assign("helptext", $helptext);
        
        $tpl->display("formConnector.ihtml");
    }
} catch (Exception $e) {
    echo "Erreur n°".$e->getCode()." : ".$e->getMessage();
}

?>
<script type='text/javascript'>
    <!--
    function insertValueQuery(elem)
    {
        var myQuery = document.Form.command_line;
        if(elem == 1)
            var myListBox = document.Form.resource;
        else if (elem == 2)
            var myListBox = document.Form.plugins;
        else if (elem == 3)
            var myListBox = document.Form.macros;

        if (myListBox.options.length > 0)
        {
            var chaineAj = '';
            var NbSelect = 0;
            for (var i=0; i<myListBox.options.length; i++)
            {
                if (myListBox.options[i].selected)
                {
                    NbSelect++;
                    if (NbSelect > 1)
                        chaineAj += ', ';
                    chaineAj += myListBox.options[i].value;
                }
            }

            if (document.selection)
            {
                // IE support
                myQuery.focus();
                sel = document.selection.createRange();
                sel.text = chaineAj;
                document.Form.insert.focus();
            }
            else if (document.Form.command_line.selectionStart || document.Form.command_line.selectionStart == '0')
            {
                // MOZILLA/NETSCAPE support
                var startPos = document.Form.command_line.selectionStart;
                var endPos = document.Form.command_line.selectionEnd;
                var chaineSql = document.Form.command_line.value;
                myQuery.value = chaineSql.substring(0, startPos)
                    + chaineAj
                    + chaineSql.substring(endPos, chaineSql.length);
            }
            else
                myQuery.value += chaineAj;
        }
    }
    //-->
</script>
