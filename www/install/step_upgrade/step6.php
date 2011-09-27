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

	include_once $centreon_path . "/www/class/centreonDB.class.php";

	$pearDB = new CentreonDB();

	$DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version = $DBRESULT->fetchRow();

	if (count(glob("./sql/centreon/Update-DB-".$version["value"]."_to_*.sql")))
		include("./step_upgrade/step4.php");
	else {

aff_header("Centreon Setup Wizard", "Post-Installation", 6);	?>

<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
	<td colspan="2"><b>End of Update</b></td>
  </tr>
  <tr>
	<td colspan="2">&nbsp;</td>
  </tr>
  <tr>
  	<td colspan="2" style="color:red;">Make sure to start CentStorage : /etc/init.d/centstorage start</td>
  </tr>
  <tr>
	<td colspan="2"><br />

	Centreon Update is finished.
	<br /><br />
	<b>Self service and commercial Support.</b><br /><br />
	There are various ways to get information about Centreon ; the documentation, wiki, forum etc...
	<ul>
		<li> Centreon WebSite : <a target="_blank" href="http://www.centreon.com">www.centreon.com</a></li>
		<li> Centreon Forum : <a target="_blank" href="http://forum.centreon.com">forum.centreon.com</a></li></li>
		<li> Centreon Wiki : <a target="_blank" href="http://doc.centreon.com">doc.centreon.com</a></li>
	</ul>
	<br /><p align="justify">
	If your company needs professional consulting and services for Centreon, or if you need to purchase a support contract for it, don't hesitate to contact official </b><a  target="_blank" href="http://support.centreon.com">Centreon support center</a></b>.
	</p>
	</td>
  </tr>
   <tr>
	<td colspan="2">&nbsp;</td>
  </tr>
	<?php
	// end last code
	aff_middle();
	$str = "<input class='button' type='submit' name='goto' value='Click here to complete your install' id='button_next' ";
	$str .= " />";
	print $str;
	aff_footer();

	}
?>