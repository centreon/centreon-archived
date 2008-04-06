<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus- Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
?>
<div style="float: left; padding-left: 30px;">
	<img src="./img/paris.jpg" alt="Logo Join Community">
</div>
<div style="float: left; padding-left: 60px; padding-top: 30px;">
	<div class="list_one"><h3>Centreon 
	<?php
	$DBRESULT =& $pearDB->query("SELECT oi.value FROM oreon_informations oi WHERE oi.key = 'version' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$release = $DBRESULT->fetchRow();
	print $release["value"];
	?></h3></div>
	
	<br /><br />
	<div class="list_one"><h3>
	<b><?php echo _("Project Leaders"); ?> :</b>
	</h3></div><br />
	<a href="mailto:rlemerlus@oreon-project.org" class="list_two">Romain Le Merlus (rom)</a><br />
	<a href="mailto:jmathis@oreon-project.org" class="list_two">Julien Mathis (Julio)</a><br />
	
	<br />
	<div class="list_one"><h3>
	<b><?php echo _("Contributors"); ?> :</b>
	</h3></div><br />
	Guillaume Watteeux (Watt)<br />
	Maximilien Bersoult (leoncx)<br />
	Christophe Coraboeuf (Wistof)<br />
	Mathavarajan Sugumaran (MrBrown)<br />
	Sylvestre Ho Tam Chay<br />
	Nicolas Cordier<br />
	Cedrick Facon (Apo)<br />
	Gaetan Lucas de Couville (gae)<br />
	Nathanael Guyot (Tor)<br />
	Jean Marc Grisard (Jmou)<br />
	Ira Janssen (Iralein)<br />
	
	<br />
	<div class="list_one"><h3>
	<b><?php echo _("Translators"); ?> :</b>
	</h3></div><br />
	Christoph Ziemann<br>
	guigui2607<br>
	Silvio Rodrigo Damasceno de Souza<br>
	Tobias Boehnert<br>
	Luiz Gustavo<br>
	Danil Makeyev<br>
	Duy-Huan BUI<br>
	</ul>
</div>