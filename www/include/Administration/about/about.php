<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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
	if (!isset($oreon))
		exit();

	$DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
	$release = $DBRESULT->fetchRow();

?><center>
<div style="width:100%;align:center;">
	<div style="width:700px;padding:20px;background-color:#FFFFFF;border:1px #CDCDCD solid;-moz-border-radius:4px;">
		<div style='float:left;width:270px;text-align:left;'>
		<p align="center"><h3><u>Centreon <?php print $release["value"]; ?>&nbsp;</u></h3><br />
			&nbsp;&nbsp;&nbsp;&nbsp;Developed by <a href="http://www.centreon.com">Centreon</a>
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
				<td>Lionel Assepo</td>
			</tr>
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
				<td>Christophe Coraboeuf</td>
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
				<td>Nikolaus Filus</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Quentin Garnier</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Dorian Guillois</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Sylvestre Ho</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Matthieu Kermagoret</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Antoine Nguyen</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Laurent Pinsivy</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>David Porte</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Mathavarajan Sugumaran</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Cedric Temple</td>
			</tr>
            <tr>
				<td>&nbsp;</td>
				<td>Alexandru Vilau</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Guillaume Watteeux</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Remi Werquin</td>
			</tr>
		</table>
		</div>
		<div style="padding-left: 30px;">
			<img src="./img/Paris-Business.jpg" alt="Logo Join Community">
		</div>
		<br/><br/><br/><br/><br/><br/><br/>
		<div style="margin-top:90px;text-align:left;width:100%;">
			<table width="80%">
				<thead><h3 style='text-align:left;'><b><?php echo _("Contributors"); ?> :</b></h3><br /></thead>
				<tr>
					<td style='padding-left:25px;'>Marisa Belijar</td>
					<td>Tobias Boehnert</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Duy-Huan BUI</td>
					<td>Gaetan Lucas de Couville</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Vincent Carpentier</td>
					<td>Christoph Ziemann</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Jean Marc Grisar</td>
					<td>Florin Grosu</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Luiz Gustavo Costa</td>
					<td>guigui2607</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Thomas Fisher</td>
					<td>Jean Gabes</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Jay Lopez</td>
					<td>Jan Kuipers</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Ira Janssen</td>
					<td>Thomas Johansen</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Peeters Jan</td>
					<td>Jan Kuipers</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Danil Makeyev</td>
					<td>Camille N&eacute;ron</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Maxime Peccoux</td>
					<td>Patrick Proy</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Joerg Steinlechner</td>
					<td>Silvio Rodrigo Damasceno de Souza</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Thierry Van Acker</td>
					<td>Felix Zingel</td>
				</tr>
				<tr>
					<td style='padding-left:25px;'>Massimiliano Ziccardi</td>
					<td></td>
				</tr>
				<tr>
					<td colspan="2" style='padding-left:25px;'>
                        <br /><?php print _("And many others..."); ?><br />
                        <?php print _("You can see the full list by visiting the Centreon's Forge"); ?>
                    </td>
				</tr>
			</table>
		</div>
	</div>
</div>
</center>