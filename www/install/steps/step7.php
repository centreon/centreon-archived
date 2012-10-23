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
DEFINE('STEP_NUMBER', 7);
$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');

$title = _('Installation');

$contents = _('Currently installing database... please do not interrupt this process.<br/><br/>');

$contents .= "<table cellpadding='0' cellspacing='0' border='0' width='80%' class='StyleDottedHr' align='center'>
                <thead>
                    <tr>
                        <th>"._('Step')."</th>
                        <th>"._('Status')."</th>
                    </tr>
                </thead>
                <tbody id='step_contents'>
                </tbody>
              </table>";

$map = "{            
            'dbconf'     : './steps/process/installConfigurationDb.php',
            'dbstorage'  : './steps/process/installStorageDb.php',
            'dbutils'    : './steps/process/installUtilsDb.php',
            'createuser' : './steps/process/createDbUser.php',
            'baseconf'   : './steps/process/insertBaseConf.php',
            'configfile' : './steps/process/configFileSetup.php'
        }";

$labels = "{
            'dbconf'    : '"._('Configuration database')."',
            'dbstorage' : '"._('Storage database')."',
            'dbutils'   : '"._('Utils database')."',
            'createuser': '"._('Creating database user')."',
            'baseconf'  : '"._('Setting up basic configuration')."',
            'configfile': '"._('Setting up configuration file')."'
           }";

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var processStatus = false;
    var map = <?php echo $map;?>;
    var labels = <?php echo $labels;?>;
    
    jQuery(function() {
        jQuery("input[type=button]").hide();
        nextStep('dbconf');
    });
    
    /**
     * Next step
     * 
     * @param string key
     * @return void
     */
    function nextStep(key) {
       jQuery('#step_contents').append('<tr>');
       jQuery('#step_contents').append('<td>'+labels[key]+'</td>');
       jQuery('#step_contents').append('<td style="font-weight: bold;" id="'+key+'"><img src="../img/misc/ajax-loader.gif"></td>');
       jQuery('#step_contents').append('</tr>');
       doProcess(true, map[key], new Array, function(response) {
            var data = jQuery.parseJSON(response);
            if (data['result'] == 0) {
                jQuery('#'+data['id']).html('<span style="color:#10CA31;">OK</span>');
                if (key == 'dbconf') {
                    nextStep('dbstorage');   
                } else if (key == 'dbstorage') {
                    nextStep('dbutils')
                } else if (key == 'dbutils') {
                    nextStep('createuser');
                } else if (key == 'createuser') {
                    nextStep('baseconf');
                } else if (key == 'baseconf') {
                    nextStep('configfile');
                } else if ('configfile') {
                    processStatus = true;
                    jQuery("#next").show();
                }
            } else {
                jQuery("#previous").show();
                jQuery("#refresh").show();
                jQuery('#'+data['id']).html(data['msg']);
            }
       }); 
    }
    
    /**
     * Validates info
     * 
     * @return bool
     */
    function validation() {
       return processStatus;
    }
</script>