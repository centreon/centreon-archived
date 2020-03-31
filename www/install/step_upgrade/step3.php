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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

session_start();
DEFINE('STEP_NUMBER', 3);
$_SESSION['step'] = STEP_NUMBER;

require_once '../steps/functions.php';
$template = getTemplate('templates');

$title = _('Release notes');

if (is_file('../RELEASENOTES.html')) {
    $contents = "<div id='releasenotes'></div>";
} else {
    $releasenotesContent = '';
    if (file_exists('../RELEASENOTES')) {
        $releasenotesContent = file_get_contents('../RELEASENOTES');
    }
    $contents = "<textarea cols='100' rows='30' readonly>" . $releasenotesContent . "</textarea>";
}

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->assign('blockPreview', 1);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var step3_s = 10;
    var step3_t;

    jQuery(function () {
        jQuery('#releasenotes').load('RELEASENOTES.html', function() {
            /* add accordion class to all div matching centreon-web-* id */
            jQuery('div[id^="centreon-web-"]').addClass('accordion');

            var acc = document.getElementsByClassName("accordion");
            var i;

            for (i = 0; i < acc.length; i++) {
              acc[i].addEventListener("click", function() {
                /* Toggle between adding and removing the "active" class,
                to highlight the button that controls the panel */
                this.classList.toggle("active");

                /* Toggle between hiding and showing the active panel */
                var panels = this.getElementsByClassName("section");
                var j;
                for (j = 0; j < panels.length; j++) {
                    if (panels[j].style.display === "block") {
                        panels[j].style.display = "none";
                    } else {
                        panels[j].style.display = "block";
                    }
                }
              });
            }
        });
        jQuery('#next').attr('disabled', 'disabled');
        timeout_button();
    });

    function timeout_button() {
        if (step3_t) {
            clearTimeout(step3_t);
        }
        jQuery("#next").val("Next (" + step3_s + ")");
        step3_s--;
        if (step3_s == 0) {
            jQuery("#next").val("Next");
            jQuery("#next").removeAttr('disabled');
        } else {
            step3_t = setTimeout('timeout_button()', 1000);
        }
    }

    function validation() {
        return true;
    }
</script>
