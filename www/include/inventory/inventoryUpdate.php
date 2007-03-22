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

	if (!$oreon)
		exit();

	$t = microtime();

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',$lang['s_header_inventory'] );

	#
	## Inventory information
	#

	$form->addElement('text', 'inventory', $lang['s_output_inventory'], "");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
//	$tpl->assign("headerMenu_name", $lang['name']);
//	$tpl->assign("headerMenu_desc", $lang['description']);
//	$tpl->assign("headerMenu_address", $lang['h_address']);
//	$tpl->assign("headerMenu_status", $lang['status']);
//	$tpl->assign("headerMenu_manu", $lang['s_manufacturer']);
//	$tpl->assign("headerMenu_type", $lang['s_type']);
	# end header menu


		$tpl->assign("initJS", "<script type='text/javascript'>
		display('". $lang['s_waiting'] ."<br><br><img src=\'./img/icones/16x16/spinner_blue.gif\'>','inventory');
		//display('Please wait during inventory...','inventory');
		loadXMLDoc('include/inventory/inventory_cron_update.php','inventory');
		</script>");

		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("inventoryUpdate.ihtml");


?>