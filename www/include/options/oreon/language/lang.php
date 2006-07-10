<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	$tpl->assign("title", $lang['lang_title']);

	$elemArr = array();
	$i = 0;
	# Information
    $langdispo="";
	$handle = opendir("./lang/");
	while (false !== ($filename = readdir($handle)))	{
		if ($filename != "." && $filename != "..")	{
			$filename = explode(".", $filename);
			$langdispo .= "-".$filename[0]." ";
		}
	}
	closedir($handle);
    $elemArr[$i] = array("ModuleTitle"=>$lang['lang_gen'],
						"LangDispo"=>$langdispo,
						"LangDispoName"=>$lang['lang_av'],
						"LangUtil"=>$oreon->user->get_lang(),
                        "LangUtilName"=>$lang['lang_use']);
    $i++;
	echo "<div>";
	# Configuration Module
    $langdispo="";
    $filename="";
	$handle = opendir("./include/configuration/lang/");
	$stock = array();
	while (false !== ($filename = readdir($handle)))	{
		if ($filename != "." && $filename != "..")	{
			$filename = explode(".", $filename);
			$stock[$filename[0]] = $filename[0];
			$langdispo .= "-".$filename[0]." ";
		}
	}
	closedir($handle);
	$elemArr[$i] = array("ModuleTitle"=>$lang['lang_mod']." ".$lang['m_configuration'],
					"LangDispo"=>$langdispo,
					"LangDispoName"=>$lang['lang_av'],
					"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : "en"),
                    "LangUtilName"=>$lang['lang_use']);
	$i++;
	# Options Module
    $langdispo="";
    $filename="";
	$handle = opendir("./include/options/lang/");
	$stock = array();
	while (false !== ($filename = readdir($handle)))	{
		if ($filename != "." && $filename != "..")	{
			$filename = explode(".", $filename);
			$stock[$filename[0]] = $filename[0];
			$langdispo .= "-".$filename[0]." ";
		}
	}
	closedir($handle);
	$elemArr[$i] = array("ModuleTitle"=>$lang['lang_mod']." ".$lang['m_options'],
					"LangDispo"=>$langdispo,
					"LangDispoName"=>$lang['lang_av'],
					"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : "en"),
                    "LangUtilName"=>$lang['lang_use']);
	$i++;		
	# Other Modules in modules/
	foreach ($oreon->modules as $mod)	{
		# Module in /modules
        $langdispo="";
        $filename="";
		$stock = array();
		if ($mod["lang"])	{
			$handle = opendir("./modules/".$mod["name"]."/lang/");
			while (false !== ($filename = readdir($handle)))	{
				if ($filename != "." && $filename != "..")	{
					$filename = explode(".", $filename);
					$stock[$filename[0]] = $filename[0];
					$langdispo .= "-".$filename[0]." ";
				}
			}
			closedir($handle);
		}
		else
			$langdispo = $lang['lang_none'];
			$elemArr[$i] = array("ModuleTitle"=>$lang['lang_mod']." ".$mod["name"],
							"LangDispo"=>$langdispo,
							"LangDispoName"=>$lang['lang_av'],
							"LangUtil"=>(array_key_exists($oreon->user->get_lang(), $stock) ? $oreon->user->get_lang() : (!count($stock) ? $lang['lang_none'] : "en")),
	                        "LangUtilName"=>$lang['lang_use']);
			$i++;
	}	
	#
	##Apply a template definition
	#
	$tpl->assign("elemArr", $elemArr);
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$tpl->display("lang.ihtml");
?>