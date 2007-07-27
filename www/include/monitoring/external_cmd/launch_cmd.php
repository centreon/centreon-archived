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
?>
<td width='85%'>
<?
	function reschedule_chk_host($oreon, $id)
	{
		?>
		<br><br>
		<table border=0 width="35%" height="50%">
		<tr>
			<td>
			<? include("./tab3Top.php"); ?>
			<form action="" method="get">
				<table width="100%" height='100%' border=0>
				<tr>
					<td class="text10b">Host Name<font color="red">*</font></td>
					<td><input name="p" type="hidden" value="306"><input name="cmd" type="hidden" value="0">
						  <select name="cmt[host_name]">
							<? 
							if (isset($oreon->hosts))
								foreach ($oreon->hosts as $h)
									if ($h->register != 1)
										print "<option>".$h->get_name()."</option>";
							?>
						  </select>
					</td>
				</tr>
				<tr>
					<td class="text10b">Persistent</td>
					<td><input name="cmt[pers]" type="checkbox" checked></td>
				</tr>	
				<tr>
					<td class="text10b">Auteur<font color="red">*</font> </td>
					<td><input name="cmt[auther]" type="text" value="<? print $oreon->user->get_alias(); ?>"></td>
				</tr>
				<tr>
					<td class="text10b" valign="top">Comment<font color="red">*</font></td>
					<td><textarea name="cmt[comment]" cols="40" rows="7"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><br><bR><br> <input name="envoyer" type="submit"></td>
				</tr>
				</table>
			<? include("./tab3Bot.php"); ?></form>
			</td>
		</tr>
		</table>	
		<?
	}
	



if (isset($_GET["cmd"]))
	switch ($_GET["cmd"]) {
		case 1: reschedule_chk_host($oreon, $_GET['id']);break;//
	}
?>

</td>
