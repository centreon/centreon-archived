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
	
	include_once $centreon_path."www/class/centreonGMT.class.php";

	/*
	 * Init GMT class
	 */
	
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession(session_id());
	
	/*
	 * ACL Actions
	 */
	$actions = false;
	$GroupListofUser = array();
	$actions = verifyActionsACLofUser("host_comment");
	$GroupListofUser =  getGroupListofUser($pearDB);
	
	if ($actions == true || count($GroupListofUser) == 0) {
		
		/*
		 * ACL
		 */
		$LCA_error = 0;
		if (!$is_admin)
			$lcaHostByName = getLcaHostByName($pearDB);
		
		if (isset($_GET["host_name"])){
			$host_id = getMyHostID($_GET["host_name"]);
			if (!isset($lcaHostByName["LcaHost"] [$_GET["host_name"]]) && !$is_admin)
				$LCA_error = 1;
			$host_name = $_GET["host_name"];
		} else
			$host_name = NULL;
		
		if ($LCA_error)
			require_once("./alt_error.php");
		else {
		
			$data = array("host_id" => getMyHostID($host_name));
				
			/*
			 * Database retrieve information for differents elements list we need on the page
			 */
			$hosts = array(""=>"");
			$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM `host` WHERE host_register = '1' ORDER BY host_name");
			if (PEAR::isError($DBRESULT)) 
				print "AddHostComment - Rq 1 Mysql Error : ".$DBRESULT->getMessage();
			while ($host =& $DBRESULT->fetchRow()){
				if (!$host["host_name"])
					$host["host_name"] = getMyHostName($host["host_template_model_htm_id"]);
				if (isset($lcaHostByName["LcaHost"][$host["host_name"]])  || $is_admin)
					$hosts[$host["host_id"]]= $host["host_name"];
			}
			$DBRESULT->free();
		
			$debug = 0;
			$attrsTextI		= array("size"=>"3");
			$attrsText 		= array("size"=>"30");
			$attrsTextarea 	= array("rows"=>"7", "cols"=>"100");
		
			/*
			 * Form begin
			 */
			$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
			if ($o == "ah")
				$form->addElement('header', 'title', _("Add a comment for Host"));
	
			/*
			 * Indicator basic information
			 */
			$redirect =& $form->addElement('hidden', 'o');
			$redirect->setValue($o);
			
		    $selHost =& $form->addElement('select', 'host_id', _("Host Name"), $hosts);
			$form->addElement('checkbox', 'persistant', _("Persistent"));
			$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
			
			$form->addRule('host_id', _("Required Field"), 'required');
			$form->addRule('comment', _("Required Field"), 'required');	
			
			$subA =& $form->addElement('submit', 'submitA', _("Save"));
			$res =& $form->addElement('reset', 'reset', _("Reset"));
			
			$form->setDefaults($data);
		  
		  	$valid = false;
			if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate())	{
				if (!isset($_POST["persistant"]))
					$_POST["persistant"] = 0;
				if (!isset($_POST["comment"]))
					$_POST["comment"] = 0;
				AddHostComment($_POST["host_id"], $_POST["comment"], $_POST["persistant"]);
				$valid = true;
		    	require_once($path."viewComment.php");
		    } else {	
				/*
				 * Smarty template Init
				 */
				$tpl = new Smarty();
				$tpl = initSmartyTpl($path, $tpl, "template/");
					
				/*
				 * Apply a template definition
				 */	
				$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
				$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
				$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
				$form->accept($renderer);			
				$tpl->assign('form', $renderer->toArray());	
				$tpl->assign('o', $o);		
				$tpl->display("AddHostComment.ihtml");
		    }
		}
	} else {
		require_once("./alt_error.php");
	}
?>