<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	<?
	$DBRESULT =& $pearDB->query("SELECT oi.value FROM oreon_informations oi WHERE oi.key = 'version' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$release = $DBRESULT->fetchRow();
	print $release["value"];
	?></h3></div>
	
	<br><br>
	<div class="list_one"><h3>
	<b><?php echo $lang["about_project_leaders"]; ?> :</b>
	</h3></div><br>
	<a href="mailto:rlemerlus@oreon-project.org" class="list_two">Romain Le Merlus (rom)</a><br>
	<a href="mailto:jmathis@oreon-project.org" class="list_two">Julien Mathis (Julio)</a><br>
	
	<br>
	<div class="list_one"><h3>
	<b><?php echo $lang["about_project_contributors"]; ?> :</b>
	</h3></div><br>
	<a href="mailto:ccoraboeuf@oreon-project.org" class="list_two">Christophe Coraboeuf (Wistof)</a><br>
	<a href="mailto:cfacon@oreon-project.org" class="list_two">Cedrick Facon (Apo)</a><br>
	Mathavarajan Sugumaran (MrBrown)<br>
	Florian Foiry (Inconnuflo)<br>
	Guillaume Watteux (Watt)<br>
	Maximilien Bersoult (leoncx)<br>
	Gaetan Lucas de Couville (gae)<br>
	Nathanael Guyot (Tor)<br>
	Jean Marc Grisard (Jmou)<br>
	</ul>
</div>