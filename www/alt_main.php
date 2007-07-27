<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	function return_color_health($h)	{
		if ($h < 25)
			return "#F5574C";
		else if (($h >= 25) && ($h < 50))
			return "#F5C425";
		else if (($h >= 50) && ($h <= 75))
			return "#FABF37";
		else if ($h > 75)
			return "#3BF541";
	}
	?>
	<table border=0 width="100%" class="tabTableTitleHome">
		<tr>
			<td style="text-align:left;font-family:Arial, Helvetica, Sans-Serif;font-size:13px;padding-left:20px;font-weight: bold;"><? echo $lang['network_health']; ?></td>
		</tr>
	</table><!--
	<table border=0 width="100%" class="tabTableHome" style="padding-top:8px;">
		<tr>
			<td width="100%" align="center" colspan="2">
				<table border=0 width="100%" cellpadding="0" cellspacing="0" align="center">
					<tr>
						<td width="150" class="text10b" align="center" style="white-space: nowrap; padding-right: 3px;"><? echo $lang['host_health']; ?></td>
						<td width="<? print $Logs->Host_health * 1.7 ; ?>" bgcolor="<? print return_color_health($Logs->Host_health); ?>" height="15"><? if ($Logs->Host_health != 0) print "<img src='./img/blank.gif' width='1' height='1'>"; ?></td>
						<td width="<? print (100 - $Logs->Host_health) * 1.7  ; ?>" bgcolor="#999999"><? if ($Logs->Host_health != 100) print "<img src='./img/blank.gif' width='1' height='1'>"; ?></td>
						<td width="15">&nbsp;</td>
						<td width="150" class="text10b" align="center" style="white-space:nowrap; padding-right: 3px;"><? echo $lang['service_health']; ?></td>
						<td width="<? print $Logs->Service_health * 1.7 ; ?>" bgcolor="<? print return_color_health($Logs->Service_health); ?>" height="15"><? if ($Logs->Service_health != 0) print "<img src='./img/blank.gif' width='1' height='1'>"; ?></td>
						<td width="<? print (100 - $Logs->Service_health) * 1.7 ; ?>" bgcolor="#999999"><? if ($Logs->Service_health != 100) print "<img src='./img/blank.gif' width='1' height='1'>"; ?></td>
						<td width="15">&nbsp;</td>
					</tr>
				</table><br>
			</td>
		</tr>
		<tr>
			<td width="100%" align="center" colspan="2">
			<div style="width:720px;">	
				<?
					$total = $Logs->host["UP"] + $Logs->host["DOWN"] + $Logs->host["UNREACHABLE"] + $Logs->host["PENDING"];
					$total_s = $Logs->sv["OK"] + $Logs->sv["WARNING"] + $Logs->sv["CRITICAL"] + $Logs->sv["PENDING"];
					if ($total != 0){	
						$data = array($Logs->host["UP"] * 100 / $total, $Logs->host["DOWN"] * 100 / $total, $Logs->host["UNREACHABLE"] * 100 / $total, $Logs->host["PENDING"] * 100 / $total);
						$label = array("UP - ".$Logs->host["UP"], "DOWN - ".$Logs->host["DOWN"], "UNREA - ".$Logs->host["UNREACHABLE"], "PENDING - ".$Logs->host["PENDING"]);
						$color = array($oreon->optGen->get_color_up(),$oreon->optGen->get_color_down(),$oreon->optGen->get_color_unreachable());
						$x = 320;
						$y = 150;
						$sn = "Hosts health";
						$fontcolor='000000';
						$theme = "pastel";
						$str = "<a href='./oreon.php?p=303&o=h'><img src='./include/reports/draw_graph_host.php?sn=$sn&coord_x=$x&coord_y=$y&fontcolor=";
						$str .= "$fontcolor&theme=$theme&dataA=$data[0]&dataB=$data[1]&dataC=$data[2]&&colorA=$color[0]&colorB=$color[1]&colorC=$color[2]&labelA=$label[0]&labelB=$label[1]&labelC=$label[2]' border=0></a>";
						print $str."&nbsp;&nbsp;&nbsp;";
					} 
					if ($total_s != 0){	
						$data = array($Logs->sv["OK"] * 100 / $total_s, $Logs->sv["WARNING"] * 100 / $total_s, $Logs->sv["CRITICAL"] * 100 / $total_s, $Logs->sv["PENDING"] * 100 / $total_s, $Logs->sv["UNKNOWN"] * 100 / $total_s);
						$label = array("OK - ".$Logs->sv["OK"], "WARNING - ".$Logs->sv["WARNING"], "CRITICAL - ".$Logs->sv["CRITICAL"], "PENDING - ".$Logs->sv["PENDING"], "UNKNOWN - ".$Logs->sv["UNKNOWN"]);
						$color = array($oreon->optGen->get_color_ok(),$oreon->optGen->get_color_warning(),$oreon->optGen->get_color_critical(), $oreon->optGen->get_color_pending(), $oreon->optGen->get_color_unknown());
						$x = 320;
						$y = 150;
						$sn = "Services health";
						$fontcolor='000000';
						$theme = "pastel";
						$str = "<a href='./oreon.php?p=303&o=s'><img src='./include/reports/draw_graph_service.php?sn=$sn&coord_x=$x&coord_y=$y&fontcolor=";
						$str .= "$fontcolor&theme=$theme&dataA=$data[0]&dataB=$data[1]&dataC=$data[2]&dataD=$data[3]&dataE=$data[4]&colorA=$color[0]&colorB=$color[1]&colorC=$color[2]&colorD=$color[3]&colorE=$color[4]&labelA=$label[0]&labelB=$label[1]&labelC=$label[2]&labelD=$label[3]&labelE=$label[4]' border=0></a>";
					} else {
						$str = "Stat not available for the moment.";
					}
					print $str;	
			?>
			</div>
			</td>
		</tr> 
		<tr>
			<td valign="top" style="padding-bottom: 15px;">
					<? include ("./include/Stat/alt_main_hg.php"); ?>			
					<? include ("./include/Stat/alt_main_sg.php"); ?>
			</td>
		</tr>
	</table>
	-->