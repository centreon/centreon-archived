<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	if (isset($_GET["command_name"]))
		$command_name = $_GET["command_name"];
	else if (isset($_POST["command_name"]))
		$command_name = $_POST["command_name"];
	else
		$command_name = NULL;

	$command_name = ltrim($command_name,"/");

	$stdout = shell_exec($oreon->optGen["nagios_path_plugins"]. $command_name . " --help");
	$msg = str_replace ("\n", "<br>", $stdout);

	$attrsText 		= array("size"=>"25");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',$lang['cmd_help']);

	#
	## Command information
	#
	$form->addElement('header', 'information', $lang['cmd_help_output']);

	$form->addElement('text', 'command_line', $lang['cmd_line'], $attrsText);
	$form->addElement('text', 'command_help', $lang['cmd_output'], $attrsText);

	#
	## Further informations
	#


	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	$tpl->assign('command_line', $oreon->optGen["nagios_path_plugins"]. $command_name . " --help");
	if (isset($msg) && $msg)
		$tpl->assign('msg', $msg);

	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("minHelpCommand.ihtml");
?>