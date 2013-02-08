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

session_start();
DEFINE('STEP_NUMBER', 4);
$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');

$title = _('Broker module information');

$brokers = array();
if ($handle = opendir('../var/brokers')) {
    while (false !== ($broker = readdir($handle))) {
        if ($broker != "." && $broker != "..") {
            $brokers[] = $broker;
        }
    }
    closedir($handle);
}

$selectedBroker = "";;
if (isset($_SESSION['BROKER_MODULE'])) {
    $selectedBroker = $_SESSION['BROKER_MODULE'];
}

$brokerOption = "<option value='0'></option>";
foreach ($brokers as $broker) {
    $selected = "";
    if ($broker == $selectedBroker) {
        $selected = "selected";
    }
    $brokerOption .= "<option value='$broker' $selected>$broker</option>";
}
$contents = " 
<form id='form_step".STEP_NUMBER."'>
        <table cellpadding='0' cellspacing='0' border='0' width='80%' class='StyleDottedHr' align='center'>
        <thead>
            <tr>
                <th colspan='2'>"._('Broker Module information')."</th>
            </tr>
            <tr>
                <td class='formlabel'>"._('Broker Module')."</td>
                <td class='formvalue'>
                    <select name='BROKER_MODULE' onChange='loadParameters(this.value);'>$brokerOption</select>
                    <label class='field_msg'></label>
                </td>
            </tr>
        </thead>
        <tbody id='brokerParams'></tbody>
        <table>
    </form>
";

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var step = <?php echo STEP_NUMBER;?>;
    var broker = '<?php echo $selectedBroker;?>';
    
    jQuery(function() {
       loadParameters(broker);
    });
    
    
    /**
     * Validates info
     * 
     * @return bool
     */
    function validation() {
       var result = false;
       jQuery('label[class=field_msg]').html('');
        doProcess(false, './steps/process/process_step'+step+'.php', jQuery('#form_step'+step).serialize(), function(data) {
            if (data == 0) {
                result = true;
            } else {
                eval(data);
            }
        });
        return result;
    }
    
    function loadParameters(broker) {
        jQuery("select[name=BROKER_MODULE]").next().html("");
        doProcess(true, './steps/process/loadBrokerParameters.php', { 'broker' : broker }, function(data) {
                            jQuery('#brokerParams').html(data);
                        });
    }
</script>
