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

 	if (!isset($centreon))
		exit;

	?><div>
		<table cellpadding="0" cellspacing="0" style="height:1px; width:100%;">
			<tr><td id="footerline1"></td></tr>
			<tr><td id="footerline2"></td></tr>
		</table>
		<div id="footer">
			<table cellpadding='0' cellspacing='0' width='100%' border='0'>
				<tr>
					<td align='center' class='copyRight'><a href="http://support.centreon.com" title="Centreon Support Access" target='_blank'>Centreon Support</a> - <a href="http://www.centreon.com" title='Centreon Services Overview' target='_blank'>Centreon Services</a> | Copyright &copy; 2004-2012 <a href="http://www.merethis.com">Merethis</a><br /><?php print _("Generated in "); $time_end = microtime_float(); $now = $time_end - $time_start; print round($now,3) . " " . _("seconds"); ?></td>
				</tr>
			</table>
		</div>
		<img src="./img/icones/7x7/sort_asc.gif" onclick="new Effect.toggle('footer'); xhr = new XMLHttpRequest(); xhr.open('GET','./menu/userMenuPreferences.php?uid=<?php echo $centreon->user->user_id; ?>&div=footer', true);xhr.send(null);" style="position:absolute;left:5px;" title="<?php echo _("Hide Footer"); ?>" />
	</div>
<?php
if	(isset($_GET["mini"]) && $_GET["mini"] == 1)	{
?>
	<script type="text/javascript">
		new Effect.toggle('header');
		new Effect.toggle('menu_3');
		new Effect.toggle('menu_2');
		new Effect.toggle('footer');
		Effect.toggle('menu1_bgcolor');
		Effect.toggle('QuickSearch');
	</script>
<?php } else {
	if (!$centreon->user->showDiv("footer")) { ?> <script type="text/javascript">new Effect.toggle('footer', 'blind', { duration : 0 });</script> <?php }
}
?>
</body>
</html>
<?php

	if (isset($pearDB) && is_object($pearDB))
		$pearDB->disconnect();
	if (isset($pearDBO) && is_object($pearDBO))
		$pearDBO->disconnect();
?>
<script type='text/javascript'>
document.onLoad = initWholePage();

/**
 * Init whole page
 */
function initWholePage()
{
	setQuickSearchPosition();
}

/**
 * set quick search position
 */
function setQuickSearchPosition()
{
	if ($('QuickSearch')) {
    	if ($('header').visible()) {
    		$('QuickSearch').setStyle({ top: '86px' });
    	} else {
    		$('QuickSearch').setStyle({ top: '3px' });
    	}
	}
}
</script>