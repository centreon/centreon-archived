<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	include_once ("../class/centreonSession.class.php");
	include_once ("DB-Func.php");
	CentreonSession::start();
	ini_set("track_errors",true);
	if (file_exists("install.conf.php")) {
	   include_once ("install.conf.php");
	}
	// Pear Modules Management
	if (file_exists("pear_module.conf.php")) {
	   include_once ("pear_module.conf.php");
	}

	$DEBUG = 0;
	$msg = NULL;
	$return_false = NULL;

    if (isset($_POST["step"]) && $_POST["step"] == 2 && isset($_POST["Restart"]))
         $_POST["step"] = NULL;
	if (isset($_POST["step"]) && $_POST["step"] == 4 && isset($_POST["Recheck"]))
		 $_POST["step"] = 3;
	if (isset($_POST["step"]) && $_POST["step"] == 5 && isset($_POST["Recheck"]))
		 $_POST["step"] = 4;
	if (isset($_POST["step"]) && $_POST["step"] == 7 && isset($_POST["Recheck"]))
		 $_POST["step"] = 6;
	if (isset($_POST["step"]) && $_POST["step"] == 10 && isset($_POST["Recheck"]))
		 $_POST["step"] = 9;

	if (isset($_POST["goto"]) && !strcmp($_POST["goto"], "Back"))
		 $_POST["step"] -= 2;
	if (isset($_POST["step"]) && $_POST["step"] == 6 && isset($_POST["pwdOreonDB"]) && strcmp($_POST["pwdOreonDB"], $_POST["pwdOreonDB2"])){
		$_POST["step"] = 5;
		$passwd_error = "Password not confirmed correctly.";
	}
	if (isset($_POST["step"]) && $_POST["step"] == 6 && isset($_POST["pwdOreonDB"]) && !strcmp($_POST["pwdOreonDB"], "")){
		$_POST["step"] = 5;
		$passwd_error = "Sorry: Centreon user database password acces cannot be empty.";
	}
	if (isset($_POST["step"]) && $_POST["step"] == 7 && isset($_POST["oreonpasswd"])  && strcmp($_POST["oreonpasswd"], $_POST["oreonpasswd2"])){
		$_POST["step"] = 6;
		$passwd_error = "Password not confirmed correctly.";
	}

	if (!isset($_POST["step"]))
		include("./steps/step1.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 1)
		include("./steps/step2.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 2)
		include("./steps/step3.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 3)
		include("./steps/step4.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 4)
		include("./steps/step5.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 5)
		include("./steps/step6.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 6)
		include("./steps/step7.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 7)
		include("./steps/step8.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 8)
		include("./steps/step9.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 9)
		include("./steps/step10.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 10)
		include("./steps/step11.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 11)
		include("./steps/step12.php");
	else if (isset($_POST["step"]) && $_POST["step"] == 12)
		include("./steps/step13.php");
		ini_set("track_errors",false);
?>
<script type='text/javascript' src='../include/common/javascript/scriptaculous/prototype.js'></script>
<script type='text/javascript'>
$$('input[class="button"]').each(function(el) {
	el.setAttribute('onclick', "this.setAttribute('disabled', 'disabled'); $$('form').each(function(frm) { frm.submit()});");
});
</script>
<?php
	exit();
?>
