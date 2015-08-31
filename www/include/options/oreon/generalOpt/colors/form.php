<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($centreon)) {
    exit();
}

/* Get Options */
$DBRESULT = $pearDB->query("SELECT * FROM `options`");
while ($opt = $DBRESULT->fetchRow()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}

$attrsText 		= array("size"=>"40");
$attrsText2		= array("size"=>"5");
$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Modify General Options"));

$form->addElement('header', 'host', _("Hosts status colors"));
$form->addElement('header', 'service', _("Services status colors"));
$form->addElement('header', 'misc', _("Miscelleneous"));
$form->addElement('header', 'hostspec', _("Specifics for hosts"));

$TabColorNameAndLang = array("color_up"=> _("Host UP Color"),
                             "color_down"=> _("Host DOWN Color"),
                             "color_unreachable"=> _("Host UNREACHABLE Color"),
                             "color_ok"=>_("Service OK Color"),
                             "color_warning"=>_("Service WARNING Color"),
                             "color_critical"=>_("Service CRITICAL Color"),
                             "color_line_critical"=>_("Row Color for Service CRITICAL"),
                             "color_pending"=>_("Service PENDING Color"),
                             "color_unknown"=>_("Service UNKNOWN Color"),
                             "color_ack"=> _("Acknowledge host or service Color"),
                             "color_downtime"=> _("Downtime host or service Color"),
                             "color_host_down"=>_("Color for host Down (Service view)"),
                             "color_host_unreachable"=>_("Color for host Unreachable (Service view)"),
                             );

while (list($nameColor, $val) = each($TabColorNameAndLang))	{
    $nameLang = $val;
    $codeColor = $gopt[$nameColor];
    $attrsText3 	= array("value"=>$nameColor,"size"=>"8","maxlength"=>"7");
    $form->addElement('text', $nameColor, $nameLang,  $attrsText3);

    if ($form->validate())
        $codeColor = $form->exportValue($nameColor);

    $attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." 0px; border-color:".$codeColor.";");
    $attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang');");

    $form->addElement('button', $nameColor.'_color', "", $attrsText4);
    if (!$form->validate())
        $form->addElement('button', $nameColor.'_modify', _("Modify"), $attrsText5);
}

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
function slash($elem = NULL)	{
    if ($elem) {
        return rtrim($elem, "/")."/";
    }
}
$form->applyFilter('__ALL__', 'myTrim');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path.'/colors', $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

/*
 * Picker Color JS
 */
$tpl->assign('colorJS',"
<script type='text/javascript'>
	function popup_color_picker(t,name) {
		var width = 318;
		var height = 314;
		var hcolor = '000000';
		var i_elem = document.getElementsByName(t+'_color').item(0);
		if ( i_elem != null ) {
			var bckcolor = i_elem.style.backgroundColor;
			var exp = new RegExp('rgb','g');
			if (exp.test(bckcolor)) {
				exp = new RegExp('[0-9]+','g');
				var tab_rgb = bckcolor.match(exp);
				hcolor = dechex(parseInt(tab_rgb[0]))+dechex(parseInt(tab_rgb[1]))+dechex(parseInt(tab_rgb[2]));
			} else {
				hcolor = bckcolor.substr(1,6);
			}
		}
		Modalbox.show('./include/common/javascript/color_picker_mb.php?name='+name, { title: \"" . _('Pick a color') . "\", width: width, height: height , afterLoad: function(){cp_init(t, hcolor);} });
	}
</script>");
/*
 * End of Picker Color
 */
$valid = false;
if ($form->validate())	{
    /*
     * Update in DB
     */
    updateColorsConfigData($form->getSubmitValue("gopt_id"));

    /*
     * Update in Oreon Object
     */
    $oreon->initOptGen($pearDB);
    $o = NULL;
    $valid = true;
    $form->freeze();
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
}
$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=colors'"));

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign("genOpt_colors_properties", _("Status Color Properties"));

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$tpl->assign('valid', $valid);
$tpl->display("form.ihtml");
