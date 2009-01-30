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
		exit();

	#
	## Database retrieve information
	#
	$DBRESULT =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#

	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	$TabColorNameAndLang = array("color_up"=>_("Host UP Color"),
                                    	"color_down"=>_("Host DOWN Color"),
                                    	"color_unreachable"=>_("Host UNREACHABLE Color"),
                                    	"color_ok"=>_("Service OK Color"),
                                    	"color_warning"=>_("Service WARNING Color"),
                                    	"color_critical"=>_("Service CRITICAL Color"),
                                    	"color_pending"=>_("Service PENDING Color"),
                                    	"color_unknown"=>_("Service UNKNOWN Color"),
					);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $val;
		$codeColor = $gopt[$nameColor];
		$title = _("Pick a color");
		$attrsText3 	= array("value"=>$nameColor,"size"=>"8","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		if ($form->validate())	{
			$codeColor = $form->exportValue($nameColor);
		}
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		if (!$form->validate())	{
			$form->addElement('button', $nameColor.'_modify', _("Modify"), $attrsText5);
		}
	}

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	
	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'/colors', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

	#
	##Picker Color JS
	#
	$tpl->assign('colorJS',"
	<script type='text/javascript'>
		function popup_color_picker(t,name,title)
		{
			var width = 400;
			var height = 300;
			window.open('./include/common/javascript/color_picker.php?n='+t+'&name='+name+'&title='+title, 'cp', 'resizable=no, location=no, width='
						+width+', height='+height+', menubar=no, status=yes, scrollbars=no, menubar=no');
		}
	</script>
    "
    );
	#
	##End of Picker Color
	#

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateColorsConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=colors'"));

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_colors_properties", _("Status Color Properties"));
	
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>