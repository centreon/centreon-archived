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
		exit;
?>
		<div id="footer">
		<table cellpadding="0" cellspacing="0" style="height:1px; width:100%;">
			<tr><td id="footerline1"></td></tr>
			<tr><td id="footerline2"></td></tr>
		</table>
		<table cellpadding='0' cellspacing='0' width='100%' border='0'>
			<tr>
				<td align='center' class='copyRight'>
					<?php print _("Generated in "); $time_end = microtime_float(); $now = $time_end - $time_start; print round($now,3) . _(" seconds "); ?><br />
					Copyright &copy; 1999-2008 Nagios - <a href="http://www.nagios.org/contact/">Ethan Galstad</a> | Copyright &copy; 2004-2008 <a href="mailto:infos@centreon.com">Centreon</a><br />
					All Rights Reserved<br />
				</td>
			</tr>
		</table>
		</div>
<?php
if	(isset($_GET["mini"]) && $_GET["mini"] == 1)	{
?>
	<script>
		new Effect.toggle('header');
		new Effect.toggle('menu_3');
		new Effect.toggle('menu_2');
		new Effect.toggle('menu_2');
		Effect.toggle('menu1_bgcolor');
		Effect.toggle('QuickSearch');
	</script>
<?php } ?>
</body>
</html>
<?php
	
	if (isset($pearDB) && is_object($pearDB))
		$pearDB->disconnect();
	if (isset($pearDBO) && is_object($pearDBO))
		$pearDBO->disconnect();
	if (isset($pearDBndo) && is_object($pearDBndo) && strcmp($pearDBndo, "DB Error: connect failed"))
		$pearDBndo->disconnect();
?>