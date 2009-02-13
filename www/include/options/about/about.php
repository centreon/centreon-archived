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
	if (!isset($oreon))
		exit();

	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
	$release =& $DBRESULT->fetchRow();
	
?><center>
<div style="width:100%;align:center;">
	<div style="width:700px;padding:20px;background-color:#FFFFFF;border:1px #CDCDCD solid;-moz-border-radius:4px;">
		<div style='float:left;width:270px;text-align:left;'>
		<p align="center"><h3><u>Centreon <?php print $release["value"]; ?>&nbsp;</u></h3><br />
			&nbsp;&nbsp;&nbsp;&nbsp;Developped by <a href="http://www.merethis.com">Merethis</a>
		</p>
		<br /><br />
		<h3><b><?php echo _("Project Leaders"); ?> :</b></h3>
		<br />
		<table>
			<tr>
				<td width="25">&nbsp;</td>	
				<td>-&nbsp;<a href="mailto:jmathis@centreon.com">Julien Mathis</a></td>
			</tr>
			<tr>
				<td width="25">&nbsp;</td>	
				<td>-&nbsp;<a href="mailto:rlemerlus@centreon.com">Romain Le Merlus</a></td>
			</tr>
		</table>
		<br><br><h3><b><?php echo _("Developers"); ?> :</b></h3><br />
		<table>
			<tr>
				<td width="25">&nbsp;</td>	
				<td>Maximilien Bersoult</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Romain Bertholon</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Nicolas Cordier</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Damien Duponchelle</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Cedrick Facon</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Sylvestre Ho Tam Chay</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Mathavarajan Sugumaran</td>
			</tr>
			<tr>	
				<td>&nbsp;</td>	
				<td>Guillaume Watteeux</td>
			</tr>
		</table>
		</div>
		<div style="padding-left: 30px;">
			<img src="./img/Paris-Business.jpg" alt="Logo Join Community">
		</div>
		<br>
		<div style="text-align:left;">
			<br><h3><b><?php echo _("Contributors"); ?> :</b></h3><br>
			<table width="80%">
				<tr>
					<td>Marisa Belijar</td>
					<td>Tobias Boehnert</td>
				</tr>
				<tr>
					<td>Duy-Huan BUI</td>
					<td>Gaetan Lucas de Couville</td>
				</tr>
				<tr>
					<td>Jean Marc Grisar</td>
					<td>Florin Grosu</td>
				</tr>
				<tr>
					<td>Luiz Gustavo</td>
					<td>guigui2607</td>
				</tr>
				<tr>
					<td>Ira Janssen</td>
					<td>Thomas Johansen</td>
				</tr>
				<tr>
					<td>Jay Lopez</td>
					<td>Jan Kuipers</td>
				</tr>
				<tr>
					<td>Danil Makeyev</td>
					<td>Camille N&eacute;ron</td>
				</tr>
				<tr>
					<td>Joerg Steinlechner</td>
					<td>Silvio Rodrigo Damasceno de Souza</td>
				</tr>
				<tr>
					<td>Massimiliano Ziccardi</td>
					<td>Christoph Ziemann</td>
				</tr>
				<tr>
					<td colspan="2"><?php print _("And others..."); ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
</center>