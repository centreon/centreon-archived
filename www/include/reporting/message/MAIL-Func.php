<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick
Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the
quality,
safety, contents, performance, merchantability, non-infringement or
suitability for
any particular or intended purpose of the Software found on the OREON web
site.
In no event will OREON be liable for any direct, indirect, punitive,
special,
incidental or consequential damages however they may arise and even if OREON
has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
function array_to_string($array)
 {
 	$flag=0;
 	$val2 = NULL;
  foreach ($array as $index => $val)
   {
   	if($flag)
   		$val2 .=",".$val;
   	else
   	{
   		$val2 .=$val;
		$flag=1;
   	}
   }
  return $val2;
}

function send_simple_message($subject, $body, $contact_lists)
{
	global $pearDB;
	global $oreon;
		$crlf = "\r\n";


	require_once 'Mail.php';
	require_once 'Mail/mime.php';


	#
	## generate 'To' list for All Format
	#
	foreach($contact_lists as $clALLFORMAT)
	{
		$resALLFORMAT =& $pearDB->query("SELECT contact_id, contact_email FROM contact WHERE contact_id IN (SELECT rtde_id FROM reporting_email_list_relation where rtdl_id =".$clALLFORMAT.")");
		$contactALLFORMAT = array();
		while($resALLFORMAT->fetchInto($contactALLFORMAT))
		{
			$recipientsALLFORMAT[$contactALLFORMAT['contact_id']] = $contactALLFORMAT['contact_email'];
		}
	}


	#
	## mail txt format
	#
	foreach($contact_lists as $clTXT)
	{
		$resTXT =& $pearDB->query("SELECT contact_id, contact_email FROM contact WHERE contact_type_msg = 'txt' AND contact_id IN (SELECT rtde_id FROM reporting_email_list_relation where rtdl_id =".$clTXT.")");
		$contactTXT = array();
		while($resTXT->fetchInto($contactTXT))
		{
			$recipientsTXT[$contactTXT['contact_id']] = $contactTXT['contact_email'];
		}
	}
	if(count($recipientsTXT) > 0)
	{
		$text = 'Text version of email'.$body;
		$mimeTXT = new Mail_mime($crlf);
		$mimeTXT->setTXTBody($text);
		$bodyTXT = $mimeTXT->get();

		$headersTXT['From']    = $oreon->user->get_email();
		$headersTXT['Subject'] = $subject;
		$headersTXT['To'] = array_to_string($recipientsALLFORMAT);

		$headersTXT = $mimeTXT->headers($headersTXT);
		$paramsTXT['host'] = 'smtp.wanadoo.fr';
		$mail_objectTXT =& Mail::factory('smtp', $paramsTXT);
		$tmpTXT = $mail_objectTXT->send($recipientsTXT, $headersTXT, $bodyTXT);

		if (PEAR::isError($tmpTXT)) {
		  echo "message error:";
		  print($tmpTXT->getMessage());
		  echo "<br>code error:";
		  print($tmpTXT->getCode());
		 }
	}
	#
	## End mail Txt format
	#



	#
	## mail Html format
	#
	$recipientsHTML = array();
	foreach($contact_lists as $clHTML)
	{
		$resHTML =& $pearDB->query("SELECT contact_id, contact_email FROM contact WHERE contact_type_msg = 'html' AND contact_id IN (SELECT rtde_id FROM reporting_email_list_relation where rtdl_id =".$clHTML.")");
		$contactHTML = array();
		while($resHTML->fetchInto($contactHTML))
		{
			$recipientsHTML[$contactHTML['contact_id']] = $contactHTML['contact_email'];
		}
	}
	if(count($recipientsHTML) > 0)
	{
		$html = "<html><body>Test html version oreon<br>".$body."<img src=\"logo_oreon.gif\"></body></html>";
		$mimeHTML = new Mail_mime($crlf);


		$mimeHTML->setHTMLBody($html);
		#
		## image html
		#
		$image = $path = $oreon->optGen["oreon_web_path"]."img/logo_oreon.gif";
		$mimeHTML->addHTMLImage($image, 'image/gif');


		$bodyHTML = $mimeHTML->get();

		$headersHTML['From']    = $oreon->user->get_email();
		$headersHTML['Subject'] = $subject;
		$headersHTML['To'] = array_to_string($recipientsALLFORMAT);
		$headersHTML = $mimeHTML->headers($headersHTML);
		$paramsHTML['host'] = 'smtp.wanadoo.fr';
		$mail_objectHTML =& Mail::factory('smtp', $paramsHTML);
		$tmpHTML = $mail_objectHTML->send($recipientsHTML, $headersHTML, $bodyHTML);

		if (PEAR::isError($tmpHTML)) {
		  echo "message error:";
		  print($tmpHTML->getMessage());
		  echo "<br>code error:";
		  print($tmpHTML->getCode());
		 }
	}
	#
	## End mail Html format
	#


/*
		$mime->setHTMLBody($html);
		#
		## image html
		#
		$image = "/usr/local/oreon/www/img/logo_oreon.gif";
		$mime->addHTMLImage($image, 'image/gif');



	#
	## make body
	#
	$body = $mime->get();


	$headers['From']    = $oreon->user->get_email();
//	$headers['To']      = $recipients;
	$headers['Subject'] = $subject;
//	$headers['Content-Type'] = 'text/html; charset=windows-1250';

	$headers = $mime->headers($headers);


	$params['host'] = 'smtp.wanadoo.fr';


	$mail_object =& Mail::factory('smtp', $params);

	$tmp = $mail_object->send($recipients, $headers, $body);


	if (PEAR::isError($tmp)) {
	  echo "message error:";
	  print($tmp->getMessage());
	  echo "<br>code error:";
	  print($tmp->getCode());
	 }
*/
}

?>