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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Centreon - IT & Network Monitoring</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, nofollow" />
<link href="<?php echo $skin; ?>login.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="./img/favicon.ico">
</head>
<body OnLoad="document.login.useralias.focus();">
<?php
    /*
     * Init Date
     */
    $date = date("d/m/Y");

    if (isset($msg_error))
        echo "<div class='error_msg'><span class='msg'>$msg_error</span></div>";
    else if (isset($_POST["submit"]))
        echo "<div  class='error_msg'><span class='msg'>Invalid user</span></div>";
    ?>
<form action="./index.php" method="post" name="login" style="height: 100%;">
<?php
    if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2)
        print "<div class='error_msg'><span class='msg'>Session Expired.</span></div>";
    if ($file_install_acces)
        print "<div class='error_msg'><span class='msg'>$error_msg</span></div>";
    if (isset($msg) && $msg)
        print "<div class='error_msg'><span class='msg'>$msg</span></div>";

?>
<p align="center">
    <div class="login_wrapper">
        <div class="LoginInvitLogo">
           <img src="img/centreon.png" alt="Centreon Logo" title="Centreon Logo" />
        </div>

        <div class="logintab">
            <table id="logintab1" style="text-align:center;" align="center">

               <tr>
                    <td align='right'>
                        <label for="useralias">Login</label>
                    </td>
                    <td>
                        <input type="text" name="useralias" value="" class="inputclassic" <?php if (isset($freeze) && $freeze) print "disabled='disabled'"; ?>>
                    </td>
                </tr>
                <tr>
                    <td align='right'><label for="password">Password</label></td>
                    <td><input type="password" name="password" value="" class="inputclassicPass" <?php if (isset($freeze) && $freeze) print "disabled='disabled'"; ?>></td>
                </tr>
                <tr>
                    <td  colspan="2" align='center'>
                        <input type="Submit" name="submit" class="btc bt_info" value="Connect" <?php if ($file_install_acces) print "disabled"; ?> >
                    </td>
                </tr>
            </table>
        </div>
        <div class="LoginInvitVersion">
            <span id="LoginInvitcpy">
                &copy; <a href="mailto:infos@centreon.com">Centreon</a> 2005-2015
            </span>
            <br>
            <span>
                <?php
                    /*
                     * Print Centreon Version
                     */
                    $DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
                    $release = $DBRESULT->fetchRow();
                    print("v. ".$release["value"]);
                ?>
            </span>
            <!--<span class="LoginInvitDate"><br /><?php echo $date; ?></span>-->
        </div>
    </div>
</p>
</form>
</body>
</html>
