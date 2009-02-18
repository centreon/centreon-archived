<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

function Connexion ($pNom, $pMotPasse, $pServeur)	{
	$msg = '';
	$connexion = @mysql_pconnect($pServeur, $pNom, $pMotPasse) or ($msg = mysql_error());
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
		<a href="http://www.centreon.com" target="_blank"><IMG src="../img/centreon.gif" alt="Centreon" border="0"></a>
  </th>
</tr>
<tr>
  <td colspan="2" width="600" style="background-position : right; background-color: #DDDDDD; background-repeat : no-repeat;">
	<form action="setup.php" method="post" name="theForm" id="theForm">
	<input type="hidden" name="step" value="<?php print $nb; ?>">
<?php
}

function aff_middle(){
?>
  </td>
</tr>
<tr>
  <td align="right" colspan="2" height="20">
	<hr>
	<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
	  <tr>
		<td>
<?php
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