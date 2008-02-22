<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	require_once "./include/common/common-Func.php";

	#Path to the options dir
	$path = "./include/options/oreon/language";
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("title", _("Lang Files management"));

	$elemArr = array();
	$i = 0;
	# Information



    $langdispo = getLangsByDir("./lang/");	

    $elemArr[$i] = array("ModuleTitle"=>_("Main Available Lang Files :"),
						"LangDispo"=>$langdispo,
						"LangDispoName"=>_("Available languages"),
						"LangUtil"=>$oreon->user->get_lang(),
                        "LangUtilName"=>_("Used language"));
    $i++;
	echo "<div>";
	# Configuration Module
    $langdispo="";
    $langdispo = getLangsByDir("./include/configuration/lang/");	
    $stock = getLangs("./include/configuration/lang/");	
    
	$elemArr[$i] = array("ModuleTitle"=>_("Module")." "._("Configuration"),
					"LangDispo"=>$langdispo,
					"LangDispoName"=>_("Available languages"),
					"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : "en"),
                    "LangUtilName"=>_("Used language"));
	$i++;
	# Options Module
    $langdispo="";
    $langdispo = getLangsByDir("./include/options/lang/");	



	$elemArr[$i] = array("ModuleTitle"=>_("Module")." "._("Options"),
					"LangDispo"=>$langdispo,
					"LangDispoName"=>_("Available languages"),
					"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : "en"),
                    "LangUtilName"=>_("Used language"));
	$i++;		
	# Other Modules in modules/
	foreach ($oreon->modules as $mod)	{
		# Module in /modules
        $langdispo="";
        $filename="";
		$stock = array();
		if ($mod["lang"])	{
		    $langdispo="";
		    $langdispo = getLangsByDir("./modules/".$mod["name"]."/lang/");	
		    $stock = getLangs("./modules/".$mod["name"]."/lang/");	
		}
		else
			$langdispo = $lang['lang_none'];
			$elemArr[$i] = array("ModuleTitle"=>_("Module")." ".$mod["name"],
							"LangDispo"=>$langdispo,
							"LangDispoName"=>_("Available language"),
							"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : (!count($stock) ? _("None") : "en")),
	                        "LangUtilName"=>_("Used language"));
			$i++;
	}	
	#
	##Apply a template definition
	#
	$tpl->assign("elemArr", $elemArr);
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$tpl->display("lang.ihtml");
?>