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

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);


	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Diffusion List comes from DB -> Store in $difLists Array
	$diffLists = array();
	$res =& $pearDB->query("SELECT rtdl_id, name FROM reporting_diff_list ORDER BY name");
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




	
	$form->addElement('textarea', 'body', 'body', $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'rtde_id');
	
	$o = 's';
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
	
		$subC =& $form->addElement('submit', 'submit', $lang["m_send"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
//	    $form->setDefaults($mail);

	
	$valid = false;
		if ($form->validate())	{
			/*
	echo "send mail here !!";

	print_r($form->getSubmitValue('contact_lists'));

print "msg type:";
 print_r($form->getSubmitValue('msg_type'));

print "subject: ".$form->getSubmitValue('subject');
print "body: ".$form->getSubmitValue('body');

	require_once 'Mail.php';
	require_once 'Mail/mime.php';

	$recipients = 'cedrick.facon@gmail.com';
	$from = 'cfacon@oreon-project.org';

	$headers['From']    = $from;
	$headers['To']      = $recipients;
	$headers['Subject'] = 'Test message sub';
	$headers['Content-Type'] = 'text/html; charset=windows-1250';
	
	$body = 'iciciciii';
	
	$params['host'] = 'smtp.wanadoo.fr';
	
	
	$mail_object =& Mail::factory('smtp', $params);
	
//	$tmp = $mail_object->send($recipients, $headers, $body);
	
	
	if (PEAR::isError($tmp)) {
	  echo "message error:";
	  print($tmp->getMessage());
	  echo "<br>code error:";
	  print($tmp->getCode());
	 }
*/

/*
 * 
 * next old
 * */


/*
	$text = 'Text version of email';
	$html = "<html><body>Test html version oreon<br><img src=\"logo_oreon.gif\"></body></html>";
	$crlf = "\n";
	$hdrs = array(
              'From'    => 'cfacon@oreon-project.org',
              'Subject' => 'Test mime message plus piece jointe en theorie'
              );
	$mime = new Mail_mime($crlf);
	$mime->setTXTBody($text);
	$mime->setHTMLBody($html);
	#
	## image html
	#
	$image = "/usr/local/oreon/www/img/logo_oreon.gif";
	$mime->addHTMLImage($image, 'image/gif'); 		
	#
	## piece jointe
	#
//	$file = '/usr/local/oreon/www/include/reporting/message/test.txt';
//	$mime->addAttachment($file, 'text/plain');

	$file = '/usr/local/oreon/www/img/logo_oreon.gif';
	$mime->addAttachment($file, 'image/gif');
		
	$body = $mime->get();
	$hdrs = $mime->headers($hdrs);
	
	
	$params['host'] = 'smtp.wanadoo.fr';
	$mail =& Mail::factory('smtp', $params);
	
	$mail->send('cedrick.facon@gmail.com', $hdrs, $body);
	
//	$tab = $form->getSubmitValue("contact_lists");
//	print "->".$tab[0];
*/

/*
$recipients = 'cedrick.facon@gmail.com';
$from = 'cfacon@oreon-project.org';

$headers['From']    = $from;
$headers['To']      = $recipients;
$headers['Subject'] = 'choisy mail test';
//$headers['Content-Type'] = 'text/html; charset=windows-1250';

$body = 'toto';

//$params['host'] = 'smtp.gmail.com';

$params['host'] = 'smtp.free.fr';

//$params['host'] = 'smtp.free.fr';
//$params['host'] = 'smtp.cnsi.fr';

//$params['port'] = '25';
//$params['auth'] = 'false';
//$params['username'] = 'cfacon@oreon-project.org';
//$params['password'] = 'oreon';

$mail_object =& Mail::factory('smtp', $params);
$tmp = $mail_object->send($recipients, $headers, $body);


 if (PEAR::isError($tmp)) {
 	echo "message error:";
 	print($tmp->getMessage());
 	echo "<br>code error:";
 	print($tmp->getCode());
 	}
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