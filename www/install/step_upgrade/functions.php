<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

function aff_header($str, $str2, $nb)
{
    ?>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Content-Style-Type" content="text/css">
        <title><?php print $str; ?></title>
        <link rel="shortcut icon" href="../img/favicon.ico">
        <link rel="stylesheet" href="./install.css" type="text/css">
        <SCRIPT language='javascript'>
            function LicenceAccepted() {
                var theForm = document.forms[0];
                var nextButton = document.getElementById("button_next");

                if (theForm.setup_license_accept.checked) {
                    nextButton.disabled = '';
                    nextButton.focus();
                }
                else {
                    nextButton.disabled = "disabled";
                }
            }

            function LicenceAcceptedByLink() {
                var theForm = document.forms[0];
                var nextButton = document.getElementById("button_next");

                theForm.setup_license_accept.checked = true;
                nextButton.disabled = '';
                nextButton.focus();
            }
        </SCRIPT>
    </head>
    <body rightmargin="0" topmargin="0" leftmargin="0">
    <table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr height="83" style=" background-image: url('../img/bg_banner.gif');">
        <th width="400" height="83"><?php print $nb . ". " . $str2; ?></th>
        <th width="200" height="83" style="text-align: right; padding: 0px;">
            <a href="http://www.centreon.com" target="_blank"><img src="../img/centreon.png" alt="Oreon" border="0"
                                                                   style="padding-top:10px;padding-right:10px;"></a>
        </th>
    </tr>
    <tr>
    <td colspan="2" width="600"
    style="background-position : right; background-color: #DDDDDD; background-repeat : no-repeat;">
    <form action="upgrade.php" method="post" name="theForm" id="theForm">
    <input type="hidden" name="step" value="<?php print $nb; ?>">
    <?php
}

function aff_middle()
{
    ?>
    </td>
    </tr>
    <tr>
    <td align="right" colspan="2" height="20">
    <hr>
    <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
    <tr>
    <td>    <?php
}

function aff_footer()
{
    ?>                </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>
    </table>
    </body>
    </html>
    <?php
}

?>