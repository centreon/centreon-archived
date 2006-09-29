<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/
	if (!isset($oreon))
		exit();

	$hosts = & $oreon->hosts;
	$services = & $oreon->services;
	// Launch log analyse
	$Logs = new Logs($oreon);
?>
	<table border="0" cellpadding="0" cellspacing="0" align="left">
		<tr>
			<td valign="top">
				<table border="0" cellpadding="0" cellspacing="0" align="left" width="160">
					<tr>
						<td class="tabTableTitle"><? echo "<div style='white-space: nowrap;'>". $lang["lr_available"]."</div>" ; ?></td>
					</tr>
					<tr>
						<td valign="top" class="tabTableMenu" style="padding-top:5px;padding-bottom:5px;">
							<?	 if (isset($hosts) && count($hosts) != 0)
									foreach ($hosts as $h){
										if ($oreon->is_accessible($h->get_id())){
											if ($h->get_register())	{?>
											<div style="padding: 2px; white-space: nowrap" align="left">
												<li>
													<a href="oreon.php?p=501&h=<? echo $h->get_id(); ?>" class="text10" style="white-space: nowrap;">
													<? echo $h->get_name(); ?>
													</a>
												</li>
											</div>
										<? unset($h);
											}
										}
									}	?>
						</td>
					</tr>
				</table>
			</td>
			<td style="width:20px;">&nbsp;</td>
			<td valign="top" style="padding-left: 20px;">
			 <? if (isset($_GET["h"])) {
			 	
				if (isset($_GET["h"]) && $oreon->is_accessible($_GET["h"])){
					?>
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
							<!-- host -->
								<table cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td class='tabTableTitle'><? echo $lang["bbreporting"]; ?></td>
									</tr>
									<tr>
										<td valign="top" class='tabTableForTab' style="border-bottom:0px;padding-left:20px;">
											<div style="padding-left: 20px;padding:10px;float:left;width:300px;">
												<span><li><? echo $lang["lr_host"]; ?></b><? print $oreon->hosts[$_GET["h"]]->get_name(); ?></li></span>
												<span><li><? echo $lang["lr_alias"]; ?></b><? print $oreon->hosts[$_GET["h"]]->get_alias(); ?></li></span> 
												<span><li><? echo $lang["lr_ip"]; ?></b><? print $oreon->hosts[$_GET["h"]]->get_address(); ?></li></span> 
												<br><br>
												<li class="text12b"><? echo $lang['options']; ?></li>
												<ul>
													<li type="square"><a href='./oreon.php?p=102&h=<? print $_GET["h"]; ?>&o=w' class='text10'><? echo $lang["lr_configure_host"]; ?></a></li>
													<li type="square"><a href='./oreon.php?p=303&o=s&host_id=<? print $_GET["h"]; ?>' class='text10'><? echo $lang["lr_view_services"]; ?></a></li>
													<li type="square"><a href='./oreon.php?p=314&h=<? print $_GET["h"]; ?>' class='text10'><? echo $lang["lr_details_host"]; ?></a></li>
												</ul>
											</div>
											<div>
										<?
										if (isset($_GET["h"]	) && isset($Logs->log_h[$_GET["h"]])){
											$x = 340;
											$y = 150;
											$fontcolor='000000';
											$theme = "pastel";
											$color = array($oreon->optGen->get_color_up(),$oreon->optGen->get_color_down(),$oreon->optGen->get_color_unreachable());
											$h = & $Logs->log_h[$_GET["h"]];
											$sn = $h->get_name() . " - " . $oreon->hosts[$h->get_id()]->get_address();
											$total = $h->get_time_up() + $h->get_time_down() + $h->get_time_unrea();
											if ($total == 0)
												$total = 1;

											$data = array($h->get_time_up() * 100 / $total, $h->get_time_down() * 100 / $total, $h->get_time_unrea() * 100 / $total);
											if ($data[0] == 0 && $data[1] == 0 && $data[2] == 0)
												$data[0] = 1;
											$label = array("UP - ".round($data[0]), "DOWN - ".round($data[1]), "UNREACHABLE - ".round($data[2]));

											$str = "<a href='./oreon.php?p=303&o=s&host_id=".$oreon->hosts[$_GET["h"]]->get_id()."'><img src='./include/reports/draw_graph_host.php?sn=$sn&coord_x=$x&coord_y=$y&fontcolor=";
											$str .= "$fontcolor&theme=$theme&dataA=$data[0]&dataB=$data[1]&dataC=$data[2]&colorA=$color[0]&colorB=$color[1]";
											$str .= "&colorC=$color[2]&labelA=$label[0]&labelB=$label[1]&labelC=$label[2]' border=0></a>";
											print $str;
										} ?>
										</div>
									</tr>
								</table>
							<!-- services -->
								<table cellpadding="0" cellspacing="0" class='tabTableForTab' width="100%">
									<tr>
										<td>
										<? if (isset($_GET["h"]	) && isset($Logs->log_h[$_GET["h"]]->log_s)){
												$x = 340;
												$y = 150;
												$fontcolor='000000';
												$theme = "pastel";
												$color = array($oreon->optGen->get_color_ok(),$oreon->optGen->get_color_warning(),$oreon->optGen->get_color_critical(), $oreon->optGen->get_color_unknown());
												$i = 0;
												if (isset($Logs->log_h[$_GET["h"]]->log_s))
													foreach ($Logs->log_h[$_GET["h"]]->log_s as $s){
														if (isset($s)) {
															$sn = $s->get_description() . " - " . $s->get_host_name();
															$total = $s->get_time_ok() + $s->get_time_warning() + $s->get_time_critical() + $s->get_time_unknown() ;
															if ($total == 0) $total = 1;

															//print $total."|". $s->get_time_ok()."|".$s->get_time_critical()."|".$s->get_time_warning()."|".$s->get_time_unknown();
															$data = array($s->get_time_ok() * 100 / $total, $s->get_time_warning() * 100 / $total, $s->get_time_critical() * 100 / $total, $s->get_time_unknown() * 100 / $total);
															if ($data[0] == 0 && $data[3] == 0 && $data[1] == 0 && $data[2] == 0)
																$data[0] = 1;
															$label = array("OK - ".round($data[0]), "WARNING - ".round($data[1]), "CRITICAL - ".round($data[2]), "UNKNOWN	 - ".round($data[3]));

															$str = "<img src='./include/reports/draw_graph_service.php?sn=$sn&coord_x=$x&coord_y=$y&fontcolor=";
															$str .= "$fontcolor&theme=$theme&dataA=$data[0]&dataB=$data[1]&dataC=$data[2]&dataD=$data[3]&colorA=$color[0]&colorB=$color[1]";
															$str .= "&colorC=$color[2]&colorD=$color[3]&labelA=$label[0]&labelB=$label[1]&labelC=$label[2]&labelD=$label[3]' style='padding:10px;'>";
															if ($i % 2 == 1)
																print $str . "<br>";
															else
																print $str . "&nbsp;";
															$i++;
														}
													}
											}  ?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
			 <?	}
			 } ?>
			</td>
		</tr>
	</table>

