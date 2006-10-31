<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	// Print File header
	include("./include/generatefile/functions.php");
	// Create Oreon.conf
	include("./include/generatefile/oreon_pm.php");

	function tab2space ($text, $spaces = 4)	{
		// Explode the text into an array of single lines
		$lines = explode("\n", $text);
		// Loop through each line
		foreach ($lines as $line) {
			// Break out of the loop when there are no more tabs to replace
			while (false !== $tab_pos = strpos($line, "\t")) {
				// Break the string apart, insert spaces then concatenate
				$start = substr($line, 0, $tab_pos);
				$tab   = str_repeat(' ', $spaces - $tab_pos % $spaces);
				$end   = substr($line, $tab_pos + 1);
				$line  = $start . $tab . $end;
			}
			$result[] = $line;
		}
		return implode("\n", $result);
	}

	if (isset($_GET["a"]) && isset($_GET["n"]) && (!strcmp($_GET["a"], "w") && strcmp($_GET["n"], "")))	{
		$str = "";
		$ret = "1";
		$exec = preg_split ("/[;\<\>|]{1}/", $_GET["n"]);
		$stdout = shell_exec($oreon->optGen->get_plugins_path() . $exec[0] . " --help");
		$tab = preg_split ("/[\n]+/", htmlentities($stdout));
	}
	else if (isset($_POST["a"]) && !strcmp($_POST["a"], "add"))	{
		if (!strcmp("", $_FILES["file2"]["tmp_name"]))
			;
		else		{
			if (move_uploaded_file($_FILES["file2"]["tmp_name"], $oreon->optGen->get_plugins_path() . $_FILES["file2"]["name"]) != 0){
				$msg = $lang["plugins3"];
				chmod($oreon->optGen->get_plugins_path() . $_FILES["file2"]["name"], 0755);
				//chown($oreon->optGen->get_plugins_path() . $_FILES["file2"]["name"], $oreon->Nagioscfg->nag_user);
				//chgrp($oreon->optGen->get_plugins_path() . $_FILES["file2"]["name"], $oreon->Nagioscfg->nag_grp);
				//passthru("chown  " . $oreon->Nagioscfg->nag_user . ":" . $oreon->Nagioscfg->nag_grp . " " . $oreon->optGen->nagios_pwd . "libexec/*");
				// log change
				system("echo \"[" . time() . "] AddPlugin;" . $_FILES["file2"]["name"] . ";" . $oreon->user->get_alias() ."\" >> ./include/log/" . date("Ymd") . ".txt");
				}
			else
				$msg = $lang["plugins4"];
		}
	}
	else if (isset($_GET["a"]) && !strcmp($_GET["a"], "delete") && isset($_GET["file"]) && strcmp($_GET["file"], ""))	{
		if (is_file($oreon->optGen->get_plugins_path() . addslashes($_GET["file"]))){
			unlink($oreon->optGen->get_plugins_path() . addslashes($_GET["file"]));
			$msg = $lang["plugins1"];
			// log change
			system("echo \"[" . time() . "] DeletePlugin;" . $_GET["file"] . ";" . $oreon->user->get_alias() . "\" >> ./include/log/" . date("Ymd") . ".txt");
		}
	} else if (isset($_POST["s"]) && !strcmp($_POST["s"], "save"))	{
		$msg_pm =$lang["plugins6"];
		// Path where configuration file will be write
		$path = $oreon->optGen->get_plugins_path();
		Create_oreon_pm_conf($oreon, $path);
		$handle = fopen($path . "oreon.conf", "r");
		$stdout = fread($handle, filesize($path . "oreon.conf"));
		fclose($handle);
		$tab = preg_split ("/[\n]+/", htmlentities($stdout));
	}
?>

<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top" align="left">
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left">
					<form action="oreon.php?p=207" method="POST" enctype="multipart/form-data">
						<? if (isset($msg))
								echo "<div class='msg' style='padding-bottom: 10px;' align='center'>".$msg."</div>"; ?>
						<table cellpadding="0" cellspacing="0" width="300">
							<tr>
								<td class="tabTableTitle"><? echo $lang["plugins"]; ?></td>
							</tr>
							<tr>
								<td class='tabTable' style="padding-top:5px"><? echo $lang["plugins_add"]; ?><br></td>
							</tr>
							<tr>
								<td class='tabTable' style="padding-top:10px" align="center"><input name="file2" type="file"></td>
							</tr>
							<tr>
								<td align="center" style="padding-top: 10px;" class='tabTable'>
									<input name="a" type="hidden" value="add">
									<input name="enregistrer" type="submit" value="<? echo $lang['save']; ?>">
									<br><br>
								</td>
							</tr>
							<tr>
								<td bgcolor='#CCCCCC'></td>
							</tr>
						</table>
					</form>
					<br>
					<form action="oreon.php?p=207" method="POST" enctype="multipart/form-data">
						<? if (isset($msg_pm))
								echo "<div class='msg' style='padding-bottom: 10px;' align='center'>".$msg_pm."</div>"; ?>
						<table cellpadding="0" cellspacing="0" width="300">
							<tr>
								<td class="tabTableTitle"><? echo $lang["plugins_pm_conf"]; ?></td>
							</tr>
							<tr>
								<td class="tabTable" style="padding-top:5px"><? echo $lang["plugins_pm_conf_desc"]; ?><br></td>
							</tr>
							<tr>
								<td align="center" style="padding-top: 10px;" class='tabTable'>
									<input name="s" type="hidden" value="save">
									<input name="enregister" type="submit" value="<? echo $lang['db_generate']; ?>">
									<br><br>
								</td>
							</tr>
							<?  if (isset($_POST["s"]) && strcmp("", $_POST["s"]))	{	?>
							<tr>
							    <td style="padding-top: 10px;" height="2" class='tabTable'>
							    <? foreach ($tab as $str)
									//print "<nobr>" . tab2space($str) . "</nobr><br>";
									print  tab2space($str) ."<br>" ;
							    ?>
							    </td>
							</tr>
							<? }	?>
							<tr>
								<td bgcolor='#CCCCCC'></td>
							</tr>
						</table>
					</form>
				<?  if (isset($_GET["n"]) && strcmp("", $_GET["n"]))	{	?>
					<br>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td class="tabTableTitle" height="2" align="center"><? print $lang['details']. " : ".$_GET["n"] ; ?></td>
						</tr>
						<tr>
							<td class="tabTable" style="padding:5px;">
							<? foreach ($tab as $str)
									print "<nobr>" . tab2space($str) . "</nobr><br>";
							?>
							</td>
						</tr>
						<tr>
							<td bgcolor='#CCCCCC' height="1"></td>
						</tr>
					</table>
				<? } ?>
				</td>
			</tr>
		</table>
	</td>
	<td style="padding-left: 20px;"></td>
	<td align="left" valign="top">
		<table border="0" align="left">
			<tr>
				<td>
					<?
					// etc
					$chemintotal = $oreon->optGen->get_plugins_path();
					$cpt = 1;
					$cpt1 = 1;
					if ($handle  = @opendir($chemintotal))	{
						while ($file = @readdir($handle))	{
							if(!is_dir("$chemintotal$file") && strcmp($file, "index.php") && strcmp($file, "exemple.php")) {
								if (!strstr($file, "#") && !strstr($file, "~")){
									$table_file[$cpt] = $file;
									$cpt++;
								}
							}
							if(is_dir("$chemintotal$file")){
								$table_rep[$cpt1] = $file;
								$cpt1++;
							}
						}
						@closedir($handle);
					} ?>
					<table border='0' align="left" cellpadding="0" cellspacing="0" width="300">
						<tr>
							<td class="tabTableTitle"><? print $lang["plugins_list"] ; ?></td>
						</tr>
						<tr>
							<td valign="top" align="center" class="tabTableForTab">
								<?
								$cpt = 0;
								echo "<TABLE BORDER='0' CELLPADDING=3 CELLSPACING='1' nowrap align=center>";
								echo "<TR>";
								echo "	<TD background='./img/menu.jpg' ALIGN='center'><b class='link'>".$lang['plugins']."</b></TD>";
								echo "	<TD background='./img/menu.jpg' ALIGN='center' COLSPAN='1'><B class='link'>".$lang['size']."</B></TD>";
								echo "	<TD background='./img/menu.jpg' ALIGN='center' COLSPAN='1'><B class='link'>".$lang['delete']."</B></TD>";
								echo "</TR>";
								if (isset($table_rep)){
									sort($table_rep);
									foreach ($table_rep as $rep)
									{
										echo "<TR>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='left'>&nbsp;&nbsp;&nbsp;<a href='oreon.php?p=207&a=w&n=$rep' class='text10'>$rep</a></TD>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='right' COLSPAN='1'><font class='link'>";
										printf("%u", filesize($oreon->optGen->get_plugins_path() . "$rep") / 1000);
										echo " ko</font></TD>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='center' COLSPAN='1'>&nbsp;</TD>";
										echo "</TR>";
										unset($rep);
									}
								}
								if (isset($table_file)){
									sort($table_file);
									foreach ($table_file as $file)
									{
										echo "<TR>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='left'>&nbsp;&nbsp;&nbsp;<a href='oreon.php?p=207&a=w&n=$file' class='text10'>$file</a></TD>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='right' COLSPAN='1'><font class='link'>";
										printf("%u", filesize($oreon->optGen->get_plugins_path() . "$file") / 1000);
										echo " ko</font></TD>";
										echo "	<TD BGCOLOR='#EBEEF3' bordercolor='#EBEEF3' ALIGN='center' COLSPAN='1'><a href='oreon.php?p=207&a=delete&file=".$file."'><img src='./img/listDel.gif' border=0  onclick=\"return confirm('".$lang['plugins2']."')\"></a></TD>";
										echo "</TR>";
										unset($file);
									}
								}
								echo "</TABLE>";
								?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
		</table>
	</td>
</tr>
</table>