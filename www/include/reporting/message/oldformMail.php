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



	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Diffusion List comes from DB -> Store in $difLists Array
	$diffLists = array();
	$res =& $pearDB->query("SELECT rtdl_id, name FROM reporting_diff_list ORDER BY name");
	if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	while($res->fetchInto($list))
		$diffLists[$list["rtdl_id"]] = $list["name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
		$form->addElement('header', 'title', 'Send Mail Test');

	#
	## Mail content
	#
	
	$form->addElement('text', 'subject', 'subject', $attrsText);

    $ams3 =& $form->addElement('advmultiselect', 'contact_lists', $lang["mailDB_diffList"], $diffLists, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);



	$mailActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, 'text', '1');
	$mailActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, 'html', '0');
	$form->addGroup($mailActivation, 'msg_type', 'msg type', '&nbsp;');
	$form->setDefaults(array('activate' => '1', "action"=>'1'));

	
	$form->addElement('textarea', 'body', 'body', $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'rtde_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	//$form->addRule('email', $lang['ErrEmail'], 'required');
	//$form->registerRule('exist', 'callback', 'testExistence');
	//$form->addRule('email', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
		$subC =& $form->addElement('submit', 'submitC', $lang["send"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
//	    $form->setDefaults($mail);

	
	$valid = false;
		if ($form->validate())	{
/*
require_once 'Mail.php';
$from = "diablo@oreon.com";
$subject = "oreon mail de test";
$to = "cedrick.facon@gmail.com";


 $headers = array(
 'From' => $from,
 'Subject' => $subject,
 'To' => $to);

$mail = Mail::factory('smtp', array(
        'host' => 'smtp.free.fr',
        'auth' => false,
        'username' => 'oreon',
        'password' => 'fouchoisy'));

$body = "boodddy";                        
 $send = $mail->send($to, $headers, $body);
 if (PEAR::isError($send)) { print($send->getMessage());}
*/
		$valid = true;
	}	

	$action = $form->getSubmitValue("action");

		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	

		$tpl->display("formMail.ihtml");
?>