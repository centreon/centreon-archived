<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	function Connexion ($pNom, $pMotPasse, $pServeur)	{
		$connexion = @mysql_pconnect($pServeur, $pNom, $pMotPasse) or ($msg = mysql_error());
		if (!isset($msg))
			$msg = "" ;
		return array ($connexion, $msg);
	}
	
	function aff_header($str, $str2, $nb){
	?>
	<html>
	<head>
	   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	   <meta http-equiv="Content-Style-Type" content="text/css">
	   <title><?php print $str; ?></title>
	   <link rel="shortcut icon" href="../img/favicon.ico">
	   <link rel="stylesheet" href="./install.css" type="text/css">
	   <SCRIPT language='javascript' src='../include/javascript/functions.js'></SCRIPT>
	   <SCRIPT language='javascript'>
		function LicenceAccepted(){
			var theForm     = document.forms[0];
			var nextButton  = document.getElementById("button_next");
	
			if( theForm.setup_license_accept.checked ){
				nextButton.disabled = '';
				nextButton.focus();
			}
			else {
				nextButton.disabled = "disabled";
			}
		}
		
		function LicenceAcceptedByLink(){
			var theForm     = document.forms[0];
			var nextButton  = document.getElementById("button_next");
	
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
			<a href="http://www.centreon.com" target="_blank"><img src="../img/centreon.gif" alt="Oreon" border="0" style="padding-top:10px;padding-right:10px;"></a>
	  </th>
	</tr>
	<tr>
	  <td colspan="2" width="600" style="background-position : right; background-color: #DDDDDD; background-repeat : no-repeat;">
		<form action="upgrade.php" method="post" name="theForm" id="theForm">
		<input type="hidden" name="step" value="<?php print $nb; ?>">
	<?php
	}
	
	function aff_middle(){	?>
	  </td>
	</tr>
	<tr>
	  <td align="right" colspan="2" height="20">
		<hr>
		<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
		  <tr>
			<td>	<?php
	}
	
	function aff_footer(){
	?>				</td>
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