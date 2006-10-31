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

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
?>
<table cellpadding="0" cellspacing="0">
<tr>
	<td valign="top">
		<table align="left" border="0">
			<tr>
				<td align="left"><? 
					require_once './include/calendar/calendrier_historique.php';
					echo calendar();	?>
				</td>
			</tr>
		</table>
	</td>
	<td style="padding-left: 20px;"></td>
	<td align="left" valign="top" width="100%">
		<? $time = NULL;
		if (!isset($_GET["o"]) || (isset($_GET["o"]) && !strcmp($_GET["o"], "")))				{
			$ti = NULL;
			$i = NULL;
			if (!file_exists("./include/log/" . date("Ymd") . ".txt"))
				system("touch  ./include/log/" . date("Ymd") . ".txt");
			if (file_exists("./include/log/" . date("Ymd") . ".txt"))
			{
				$log = fopen("./include/log/" . date("Ymd") . ".txt", "r");
				while ($str = fgets($log))
				{
					if (preg_match("/^\[([0-9]*)\][ ]*([.]*)/", $str, $matches))
						$time = $matches[1];
					if ($ti != date("G", $time))
						$i = 0;
					$res = preg_split("/\]/", $str);
					$logs[date("G", $time)][$i] = $time. ";" .$res[1]  ;
					$ltime[date("G", $time)][$i] = $time;
					$i++;
					$ti = date("G", $time);
				}
			}
		?>
		
		<? } else if (isset($_GET["o"]) && isset($_GET["file"]) && (!strcmp($_GET["o"], "d") && strcmp($_GET["file"], "") && is_file("./include/log/" . $_GET["file"])))	{
			$log = fopen("./include/log/" . $_GET["file"], "r");
			if (ereg ("([0-9]{4})([0-9]{2})([0-9]{2})\.txt", $_GET["file"], $regs)) {
				$date = mktime(0, 0, 0, $regs[2],$regs[3],$regs[1]);
			} else {
			 	$date = now();
			}
			$i = NULL;
			$ti = NULL;
			while ($str = fgets($log))				{
				if (preg_match("/^\[([0-9]*)\][ ]*([.]*)/", $str, $matches)){
					$time = $matches[1];}
				if ($ti != date("G", $time))
					$i = 0;
				$res = preg_split("/\]/", $str);
				$logs[date("G", $time)][$i] =  $time. ";" .$res[1];
				$i++;
				$ti = date("G", $time);
			}
			if (!$time)
				$time = time();
			}	?>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td class='tabTableTitle'><? 
						echo $lang['log_detail'];
						if (isset($date))
							echo date($lang["date_format"], $date);
						else 
							echo date($lang["date_format"]);
							 ?>
					</td>
				</tr>
				<tr>
					<td class="tabTableForTab">
						<table cellspacing=1 cellpadding=5 border=0 align="left">
						<?
						for ($t = 23; $t != -1; $t--){
							if (isset($logs[$t])){
								print "<tr bgColor=#eaecef><td colspan='3' class='text11b'>" . $t . ":00</td></tr>" ;
								foreach ($logs[$t] as $l => $value){///$logs[1][0];
									$r = preg_split("/;/", $value);
									print "<tr bgColor='#eaecef'><td class='text11'>" . date($lang["time_format"], $r[0]) . "</td><td> $r[3]</td><td align=left> $r[1]";
									if (strcmp($r[2], ""))
										print " - $r[2]";
									print "</td></tr>";
								}
							}
						} ?>
						</table>
					</td>
				</tr>
			</table>
	</td>
	</tr>
</table>
