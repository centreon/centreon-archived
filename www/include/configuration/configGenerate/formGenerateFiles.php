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

if (!isset($centreon)) {
    exit();
}

if (!$centreon->user->admin && $centreon->user->access->checkAction('generate_cfg') === 0) {
    require_once _CENTREON_PATH_ . 'www/include/core/errors/alt_error.php';
    return null;
}

/*
 *  Get Poller List
 */
$acl = $centreon->user->access;
$tab_nagios_server = $acl->getPollerAclConf(array(
    'get_row' => 'name',
    'order' => array('name'),
    'keys' => array('id'),
    'conditions' => array('ns_activate' => 1)
));
/* Sort the list of poller server */
$pollersFromUrl = $_GET['poller'] ?? '';
$pollersId = explode(',', $pollersFromUrl);
$selectedPollers = array();

foreach ($tab_nagios_server as $key => $name) {
    if (in_array($key, $pollersId)) {
        $selectedPollers[] = array(
            'id' => $key,
            'text' => $name
        );
    }
}

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

$form->addElement('checkbox', 'debug', _("Run monitoring engine debug (-v)"), null, array('id' => 'ndebug'));
$form->addElement('checkbox', 'gen', _("Generate Configuration Files"), null, array('id' => 'ngen'));
$form->addElement('checkbox', 'move', _("Move Export Files"), null, array('id' => 'nmove'));
$form->addElement('checkbox', 'restart', _("Restart Monitoring Engine"), null, array('id' => 'nrestart'));
$form->addElement('checkbox', 'postcmd', _('Post generation command'), null, array('id' => 'npostcmd'));
$form->addElement(
    'select',
    'restart_mode',
    _("Method"),
    array(2 => _("Restart"), 1 => _("Reload")),
    array('id' => 'nrestart_mode', 'style' => 'width: 220px;')
);
$form->setDefaults(array('debug' => '1', 'gen' => '1', 'restart_mode' => '1'));

/* Add multiselect for pollers */
$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_poller&action=list';
$attrPoller = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => true,
    'availableDatasetRoute' => $route,
    'multiple' => true
);
$form->addElement('select2', 'nhost', _("Pollers"), array("class" => "required"), $attrPoller);
$form->addRule('nhost', _("You need to select a least one polling instance."), 'required', null, 'client');

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$sub = $form->addElement(
    'button',
    'submit',
    _("Export"),
    array('id' => 'exportBtn', 'onClick' => 'generationProcess();', 'class' => 'btc bt_success')
);
$msg = null;
$stdout = null;

$tpl->assign("noPollerSelectedLabel", _("Compulsory Poller"));
$tpl->assign("consoleLabel", _("Console"));
$tpl->assign("progressLabel", _("Progress"));
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, ' .
    '"orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], ' .
    'WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

include_once("help.php");

$helptext = "";
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);

$tpl->display("formGenerateFiles.ihtml");

?>
<script type='text/javascript'>
    var initPollers = '<?php echo json_encode($selectedPollers); ?>';
    var selectedPoller;
    var debugOption;
    var generateOption;
    var moveOption;
    var restartOption;
    var restartMode;
    var exportBtn;
    var steps = new Array();
    var stepProgress;
    var curProgress;
    var postcmdOption;

    var tooltip = new CentreonToolTip();
    var session_id = "<?php echo session_id(); ?>";
    tooltip.render();
    var msgTab = new Array();

    msgTab['start'] = "<?php echo addslashes(_("Preparing environment")); ?>";
    msgTab['gen'] = "<?php echo addslashes(_("Generating files")); ?>";
    msgTab['debug'] = "<?php echo addslashes(_("Running debug mode")); ?>";
    msgTab['move'] = "<?php echo addslashes(_("Moving files")); ?>";
    msgTab['restart'] = "<?php echo addslashes(_("Restarting engine")); ?>";
    msgTab['abort'] = "<?php echo addslashes(_("Aborted.")); ?>";
    msgTab['noPoller'] = "<?php echo addslashes(_("No poller selected")); ?>";
    msgTab['postcmd'] = "<?php echo addslashes(_("Executing command")); ?>";

    jQuery(function () {

        $('#progress_bar').progressbar({
            value: 0
        }).removeClass('ui-corner-all');

        var pollers = JSON.parse(initPollers);
        for (var i = 0; i < pollers.length; i++) {
            jQuery('#nhost').append(
                '<option value="' + pollers[i].id + '" selected>' + pollers[i].text + '</option>'
            );
        }
        jQuery('#nhost').trigger('change');
    });

    /**
     * Next step
     *
     * @returns void
     */
    function nextStep() {
        var func = window[steps.shift()];
        if (typeof(func) === 'function') {
            func();
        } else {
            // no more step
            exportBtn.disabled = false;
        }
    }

    /**
     * Display error if no poller is selected
     *
     * @returns boolean
     */
    function checkSelectedPoller() {
        var countSelectedPoller = jQuery('#nhost').next('span').find('.select2-selection__choice').length;
        if (countSelectedPoller > 0) {
            jQuery('#noSelectedPoller').hide();
            jQuery('#noSelectedPoller').next('br').remove();
            return true;
        } else {
            jQuery('#noSelectedPoller').show();
            if (!jQuery('#noSelectedPoller').next('br').length) {
                jQuery('#noSelectedPoller').after('<br>');
            }
            return false;
        }
    }

    /**
     * Generation process
     *
     * @return void
     */
    function generationProcess() {
        if (!checkSelectedPoller()) {
            return null;
        }
        curProgress = 0;
        stepProgress = 0;
        updateProgress();
        cleanErrorPhp();
        document.getElementById('console').style.visibility = 'visible';
        $('#consoleContent').html(msgTab['start'] + "... ");
        $('#consoleDetails').html("");
        initEnvironment();
        if (selectedPoller !== "-1") {
            nextStep();
        }
    }

    /**
     * Initializes generation options
     */
    function initEnvironment() {
        selectedPoller = jQuery('#nhost').val().join(',');
        debugOption = document.getElementById('ndebug').checked;
        generateOption = document.getElementById('ngen').checked;
        if (generateOption) {
            steps.push("generateFiles");
        }
        moveOption = document.getElementById('nmove').checked;
        if (moveOption) {
            steps.push("moveFiles");
        }
        restartOption = document.getElementById('nrestart').checked;
        if (restartOption) {
            steps.push("restartPollers");
        }
        restartMode = document.getElementById('nrestart_mode').value;
        postcmdOption = 0;
        if (document.getElementById('npostcmd')) {
            postcmdOption = document.getElementById('npostcmd').checked;
        }
        if (postcmdOption) {
            steps.push("executeCommand");
        }
        stepProgress = 100 / steps.length;
        curProgress = 0;
        exportBtn = document.getElementById('exportBtn');
        exportBtn.disabled = true;
        if (selectedPoller == "-1") {
            $('#consoleContent').append("<b><font color='red'>NOK</font></b> (" + msgTab['noPoller'] + ")<br/>");
            abortProgress();
            return null;
        }
        $('#consoleContent').append("<b><font color='green'>OK</font></b><br/>");
    }

    /**
     * Generate files
     */
    function generateFiles() {
        if (debugOption && !generateOption) {
            $('#consoleContent').append(msgTab['debug'] + '... ');
        } else {
            $('#consoleContent').append(msgTab['gen'] + "... ");
        }
        jQuery.ajax({
            url: './include/configuration/configGenerate/xml/generateFiles.php',
            type: 'POST',
            dataType: "xml",
            data: {
                poller: selectedPoller,
                debug: debugOption,
                generate: generateOption
            },
            success: function (data) {
                data = $(data);
                displayStatusMessage(data);
                displayDetails(data);
                displayPhpErrorMsg('generate', data);
                if (isError(data) == "1") {
                    abortProgress();
                    return null;
                }
                updateProgress();
                nextStep();
            }
        });
    }

    /**
     * Move files
     */
    function moveFiles() {
        $('#consoleContent').append(msgTab['move'] + "... ");
        jQuery.ajax({
            url: './include/configuration/configGenerate/xml/moveFiles.php',
            type: 'POST',
            dataType: "xml",
            data: {
                poller: selectedPoller
            },
            success: function (data) {
                data = $(data);
                displayStatusMessage(data);
                displayDetails(data);
                displayPhpErrorMsg('move', data);
                if (isError(data) == "1") {
                    abortProgress();
                    return null;
                }
                updateProgress();
                nextStep();
            }
        });
    }

    /**
     * Restart Pollers
     */
    function restartPollers() {
        $('#consoleContent').append(msgTab['restart'] + "... ");
        jQuery.ajax({
            url: './include/configuration/configGenerate/xml/restartPollers.php',
            type: 'POST',
            dataType: "xml",
            data: {
                poller: selectedPoller,
                mode: restartMode
            },
            success: function (data) {
                data = $(data);
                displayStatusMessage(data);
                displayDetails(data);
                displayPhpErrorMsg('restart', data);
                if (isError(data) == "1") {
                    abortProgress();
                    return null;
                }
                updateProgress();
                nextStep();
            }
        });
    }

    /**
     * Execute commands
     */
    function executeCommand() {
        $('#consoleContent').append(msgTab['postcmd'] + "... ");
        jQuery.ajax({
            url: './include/configuration/configGenerate/xml/postcommand.php',
            type: 'POST',
            dataType: "xml",
            data: {
                poller: selectedPoller
            },
            success: function (data) {
                data = $(data);
                displayPostExecutionCommand(data);
                if (isError(data) == "1") {
                    abortProgress();
                    return null;
                }
                updateProgress();
                nextStep();
            }
        });
    }

    /**
     * Display status message
     */
    function displayStatusMessage(responseXML) {
        var status = responseXML.find("status");
        var error = responseXML.find("error");
        var str;

        str = status.text();
        if (error.length) {
            str += " (" + error.text() + ")";
        }
        str += "<br/>";
        $('#consoleContent').append(str);
    }

    /**
     * Display details
     */
    function displayDetails(responseXML) {
        var debug = responseXML.find("debug");
        var str;

        str = "";
        if (debug.length) {
            str = debug.text();
        }
        str += "<br/>";
        $('#consoleDetails').append(str);
    }

    /**
     * Display post command result and output
     */
    function displayPostExecutionCommand(responseXML) {
        var xml = responseXML.find("result");
        var xmlStatus = responseXML.find("status");
        var str;
        str = "";
        if (xml.length) {
            str += xml.text();
        }
        str += "<br/>";
        $('#consoleDetails').append(str);
        $('#consoleContent').append(xmlStatus.text());
    }

    /**
     * Returns 1 if is error
     * Returns 0 otherwise
     */
    function isError(responseXML) {
        var statuscode = responseXML.find("statuscode");
        if (statuscode.length) {
            return statuscode.text();
        }
        return 0;
    }

    /**
     * Action (generate, move, restart)
     */
    var errorClass = 'list_two';

    function displayPhpErrorMsg(action, responseXML) {
        var errors = responseXML.find('errorPhp');
        var titleError;
        if (errorClass == 'list_one') {
            errorClass = 'list_two';
        } else {
            errorClass = 'list_one';
        }
        if (errors.length == 0) {
            return;
        }
        switch (action) {
            case 'generate':
                titleError = '<b>Errors/warnings in generate</b>';
                break;
            case 'move':
                titleError = '<b>Errors/warnings in move files</b>';
                break;
            case 'restart':
                titleError = '<b>Errors/warnings in restart</b>';
                break;
        }

        var bodyErrors = document.getElementById('error_log');
        var trEl = document.createElement('tr');
        trEl.setAttribute('class', errorClass);
        bodyErrors.appendChild(trEl);
        var tdEl1 = document.createElement('td');
        tdEl1.setAttribute('class', 'FormRowField');
        tdEl1.innerHTML = titleError;
        trEl.appendChild(tdEl1);
        var tdEl2 = document.createElement('td');
        tdEl2.setAttribute('class', 'FormRowValue');
        tdEl2.innerHTML = '<span style="position: relative; float: left; margin-right: 5px;">' +
        '<a href="javascript:toggleErrorPhp(\'' + action + '\');" id="expend_' + action + '">[ + ]</a></span>';
        trEl.appendChild(tdEl2);
        var divErrors = document.createElement('div');
        divErrors.setAttribute('id', 'errors_' + action);
        divErrors.setAttribute('style', 'position: relative; float: left;');
        divErrors.style.visibility = 'hidden';
        tdEl2.appendChild(divErrors);
        for (var i = 0; i < errors.length; i++) {
            divErrors.innerHTML += errors.item(i).firstChild.data;
        }
    }

    function toggleErrorPhp(action) {
        var linkEl = document.getElementById('expend_' + action);
        var divErrors = document.getElementById('errors_' + action);
        if (linkEl.innerHTML == '[ + ]') {
            linkEl.innerHTML = '[ - ]';
            divErrors.style.visibility = 'visible';
        } else {
            linkEl.innerHTML = '[ + ]';
            divErrors.style.visibility = 'hidden';
        }
    }

    function cleanErrorPhp() {
        var bodyErrors = document.getElementById('error_log');
        while (bodyErrors.hasChildNodes()) {
            bodyErrors.removeChild(bodyErrors.firstChild);
        }
    }

    /**
     * Updates progress
     */
    function updateProgress() {
        var pct = 0;
        if (typeof(curProgress) != 'undefined' && typeof(stepProgress) != 'undefined') {
            pct = curProgress + stepProgress;
            curProgress += stepProgress;
        }
        if (pct > 100) {
            pct = 100;
        }
        $('#progress_bar').progressbar('value', pct);
        $('#progressPct').html(Math.round(pct) + "%");
    }

    /**
     * Toggle debug
     */
    function toggleDebug(pollerId) {
        if (pollerId) {
            if ($('#debug_' + pollerId).is(':visible')) {
                $('#togglerp_' + pollerId).show();
                $('#togglerm_' + pollerId).hide();
                $('#debug_' + pollerId).hide();
            } else {
                $('#togglerp_' + pollerId).hide();
                $('#togglerm_' + pollerId).show();
                $('#debug_' + pollerId).show();
            }
        }
    }

    function abortProgress() {
        $('#consoleContent').append(msgTab['abort']);
        exportBtn.disabled = false;
        steps = new Array();
    }
</script>
