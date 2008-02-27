<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
	if (!isset($oreon))
		exit();

	// Comments

	function add_host_comment($oreon, $cmt, $lang){
		$check = array("on" => 1, "off" => 0);
		if (isset($cmt["pers"]))
			$str = "echo '[" . time() . "] ADD_HOST_COMMENT;".$cmt["host_name"].";".$check[$cmt["pers"]].";".$cmt["auther"].";".$cmt["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		else
			$str = "echo '[" . time() . "] ADD_HOST_COMMENT;".$cmt["host_name"].";0;".$cmt["auther"].";".$cmt["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		print "<div style='padding-top: 50px' class='text11b'><center>"._("Comment added successfully. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ")."</center></div>";
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=307'\",2000)</SCRIPT>";
	
	}
	
	function add_svc_comment($oreon, $cmt, $lang)
	{
		$check["on"] = 1;
		$check["off"] = 0;
		if (isset($cmt["pers"]))
			$str = "echo '[" . time() . "] ADD_SVC_COMMENT;".$oreon->hosts[$cmt["host_id"]]->get_name().";".$cmt["svc"].";".$check[$cmt["pers"]].";".$cmt["auther"].";".$cmt["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		else
			$str = "echo '[" . time() . "] ADD_SVC_COMMENT;".$oreon->hosts[$cmt["host_id"]]->get_name().";".$cmt["svc"].";0;".$cmt["auther"].";".$cmt["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		print "<div style='padding-top: 50px' class='text11b'><center>"._("Comment added successfully. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ")."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=307'\",2000)</SCRIPT>";
	}
	
	function del_host_comment($oreon, $arg, $lang)
	{
		$str = "echo '[" . time() . "] DEL_HOST_COMMENT;".$arg."' >> " . $oreon->Nagioscfg->command_file;
		print "<div style='padding-top: 50px' class='text11b'><center>"._("Comment deleted successfully. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ")."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=307'\",2000)</SCRIPT>";
	}
	
	function del_all_host_comment($oreon, $arg, $lang)
	{
		$str = "echo '[" . time() . "] DEL_ALL_HOST_COMMENTS;".$arg."' >> " . $oreon->Nagioscfg->command_file;
		print "<div style='padding-top: 50px' class='text11b'><center>"._("All Comments deleted successfully. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ")."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=307'\",2000)</SCRIPT>";
	}
	
	function del_svc_comment($oreon, $arg, $lang)
	{
		$str = "echo '[" . time() . "] DEL_SVC_COMMENT;".$arg."' >> " . $oreon->Nagioscfg->command_file;
		print "<div style='padding-top: 50px' class='text11b'><center>"._("Comment deleted successfully. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ")."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=307'\",2000)</SCRIPT>";
	}
	
?>	