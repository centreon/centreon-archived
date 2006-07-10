<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf - Cedrick Facon

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
		
	if (!isset($_GET["options"]) && !isset($_GET["update"]))	{
		$options[0] = 0;
		$options[1] = 0;
		$options[2] = 0;
		$options[3] = 0;
		$options[4] = 1;
		$options[5] = 0;
	} else
		$options = & $_GET["options"];


	$log = NULL;	
	if (!isset($oreon->Lca[$oreon->user->get_id()]) || (isset($oreon->Lca[$oreon->user->get_id()]) && !strcmp($oreon->Lca[$oreon->user->get_id()]->get_watch_log(), "1"))){
		$ti = 0;
		if (isset($_GET["o"]) && !strcmp($_GET["o"], "d") && isset($_GET["file"]) && strcmp($_GET["file"], "") && is_file($oreon->Nagioscfg->log_archive_path . $_GET["file"]))
			$log = fopen($oreon->Nagioscfg->log_archive_path . $_GET["file"], "r");
		else{
			if (file_exists($oreon->Nagioscfg->log_file) && !($log = fopen($oreon->Nagioscfg->log_file, "r")))
				echo $lang["pel_cant_open"];
		}
		if ($log)
			$event_log = new TabEventLog($options, $log);
		else
			$event_log = NULL;
	?>
	<table border=0>
		<tr>
			<td valign="top">
			<?
				if (isset($_GET['file']) && strcmp($_GET['file'], "")){
					preg_match("/^nagios\-([0-9]+)\-([0-9]+)\-([0-9]+)\-00\.log/", $_GET["file"], $matches);
					$today_now = 1;
				} else
					$today_now = 0;
			?>
			<font class="text14b"><? echo $lang['pel_notify_title']; ?><? if ($today_now == 1 && isset($matches[2]) && $matches[1] && $matches[3]) print $matches[2] - 1 . "/" . $matches[1] . "/" . $matches[3]; else print date("d/m/Y") ;?></b></font>
			</td>
		</tr>
	<tr>
		<td align="center">
			<table border="0">
				<tr>
					<td class="tabTableTitle">
						<?  
						print $lang['hours'] . "&nbsp;:&nbsp;";
						for ($t = 0; $t != 24; $t++)
						{
							if (isset($event_log->tab_hour[$t]) && $event_log->tab_hour[$t])
								print "<a href='#$t' class='text11'>$t</a>";
							if (isset($event_log->tab_hour[$t + 1]) && $event_log->tab_hour[$t + 1])
								if (strcmp($event_log->tab_hour[$t + 1], ""))
									print " - ";
						}
						?>
					</td>
				</tr>
			</table><br><br>
			<table>
			<tr>
				<td valign="top">
					<? //include ("tab3Top.php"); 
						$color[0] = "EAEAEA";
						$color[1] = "DDDDDD"; 
					?>
					<table cellSpacing=1 cellPadding=1 border=0 style="border-width: thin; border-style: dashed; border-color=#9C9C9C;">
						<tr>
							<td bgcolor="#CCCCCC" width="50" class="text12b"><? echo $lang['date']; ?></td>
							<td bgcolor="#CCCCCC" width="150" class="text12b"><? echo $lang['event']; ?></td>
							<td bgcolor="#CCCCCC" width="75" class="text12b"><? echo $lang['h']; ?></td>
							<td bgcolor="#CCCCCC" width="75" class="text12b"><? echo $lang['s']; ?></td>
						</tr>
					<?
					$time_now = date("G");
					$time_before = date("G");	
					$c = -1;
					$clr = 0;
					for ($i = count($event_log->tab) - 1, $x = $i; $event_log && $i != 0; $i--){
						$color_set = $color[$clr % 2];
						$haut_i = $clr + 1;
						$color_haut = $color[$haut_i % 2];
						$time_now = date("G", $event_log->tab[$i]->time_event);
						if ($event_log->tab[$i]->type){
							$str = "";
							if ($time_now != $time_before && $c != -1 && $c != $time_now){
								$str =  "<td colspan=4 style='border-width: thin; border-bottom: 1px;border-top:0px;border-right:0px;border-left:0px; border-style: dashed; border-color=#9C9C9C;white-space:nowrap' bgcolor='#".$color_haut."' align='right'><a name='$time_now'></a><a href='#top' class='text9b'>".$lang['top']."</a>&nbsp;&nbsp;</td>\n" ;
								$c = -1;
							}
							if (!strcmp($event_log->tab[$i]->type, "HOST NOTIFICATION")){
								$c = $time_now;
								$str = "<td style='white-space:nowrap' class='text9br' bgcolor='#".$color_set."'>" . $event_log->tab[$i]->type .  "</td><td colspan=2 class='text9' bgcolor='#".$color_set."'>&nbsp;" . $event_log->tab[$i]->host . "</td></tr><tr><td>&nbsp;</td><td style='white-space:nowrap' class='text9' colspan='3' bgcolor='#".$color_set."'>&nbsp;" . $event_log->tab[$i]->output ."</td>" ;
							} else if (!strcmp($event_log->tab[$i]->type, "SERVICE NOTIFICATION")){
								$c = $time_now;
								$str =  "<td style='white-space:nowrap' class='text9br' bgcolor='#".$color_set."'>" . $event_log->tab[$i]->type .  "</td><td class='text9' bgcolor='#".$color_set."'>&nbsp;" . $event_log->tab[$i]->host . "</td><td class='text9' bgcolor='#".$color_set."'>&nbsp;" . $event_log->tab[$i]->service . "</td></tr><tr><td>&nbsp;</td><td style='white-space:nowrap' class='text9' colspan='3' bgcolor='#".$color_set."'>&nbsp;" . $event_log->tab[$i]->output ."</td>" ;
							}
							if ($str){
								if ($c == -1)
									print "<tr><td style='white-space:nowrap' class='text9b'></td>" . $str . "</tr>";
								else
									print "<tr><td style='white-space:nowrap' class='text9b' bgcolor='#".$color_set."'>" . date("G:i:s", $event_log->tab[$i]->time_event) . "</td>" . $str . "</tr>";
								$clr++;
							}
							$time_before = $time_now;
							$x--;
						}
					} 
					print "<tr><td style='white-space:nowrap' class='text9b'></td><td colspan=4 style='border-width: thin; border-bottom: 1px;border-top:0px;border-right:0px;border-left:0px;white-space:nowrap' bgcolor='#".$color_set."' align='right'><a href='#top' class='text9b'>".$lang['top']."</a>&nbsp;&nbsp;</td></tr>";
					?>
					</table>
				</td>
				<td valign="top" align="center" style="padding-left: 20px;"> 
					<table border="0" width="95%">
						<tr>
							<td valign="top" class="tabTableForTab">
								<?	require_once './include/calendar/calendrier.php';
									echo calendar($oreon);
								?>
							</td>
						</tr>
					</table>						
				</td>
			</tr>
		</table>
<? 	}
	else
		include("./include/security/error.php"); ?>