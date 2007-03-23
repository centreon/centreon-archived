<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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

	if (!file_exists("./oreon.conf.php"))
		header("Location: ./install/setup.php");
	else if (file_exists("./oreon.conf.php") && is_dir('install'))
		header("Location: ./install/upgrade.php");
	else
		require_once ("./oreon.conf.php");

	require_once ("$classdir/Session.class.php");
	require_once ("$classdir/Oreon.class.php");
	require_once("DBconnect.php");

	// detect installation dir
	$file_install_acces = 0;
	if (file_exists("./install/setup.php")){
		$error_msg = "Installation Directory '". getcwd() ."/install/' is accessible. Delete this directory to prevent security problem.";
		$file_install_acces = 1;
	}

	Session::start();
	if (isset($_GET["disconnect"])) {
		$oreon = & $_SESSION["oreon"];
		$pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
		Session::stop();
		Session::start();
	}

	if (isset($_SESSION["oreon"])) {	// already connected
		$oreon = & $_SESSION["oreon"];
		$pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
		Session::stop();
		Session::start();
	}

	if (isset($_POST["submit"]) || (isset($_GET["autologin"]) && isset($_GET["p"]) && $_GET["autologin"])) {
		require_once("DBconnect.php");
		isset($_GET["useralias"]) ? $useraliasG = $_GET["useralias"] : $useraliasG = NULL;
		isset($_POST["useralias"]) ? $useraliasP = $_POST["useralias"] : $useraliasP = NULL;
		$useraliasG ? $useralias = $useraliasG : $useralias = $useraliasP;
		isset($_GET["password"]) ? $passwordG = $_GET["password"] : $passwordG = NULL;
		isset($_POST["password"]) ? $passwordP = $_POST["password"] : $passwordP = NULL;
		$passwordG ? $password = $passwordG : $password = $passwordP;
		# BugFix  #224
		$password = ($password == '' ? time() : $password  );
	    #

		if (!isset($_POST["submit"]))
			$res =& $pearDB->query("SELECT * FROM contact WHERE MD5(contact_alias)='".htmlentities($useralias, ENT_QUOTES)."' AND contact_activate = '1' LIMIT 1");
		else
			$res =& $pearDB->query("SELECT * FROM contact WHERE contact_alias='".htmlentities($useralias, ENT_QUOTES)."' AND contact_activate = '1' LIMIT 1");

		if($res->numRows()) {
			$contact = $res->fetchRow();
				if ($contact["contact_oreon"]){
					$res =& $pearDB->query("SELECT debug_path, debug_auth  FROM general_opt LIMIT 1");
					if (PEAR::isError($res))
		    			die($res->getMessage());
				$debug = $res->fetchRow();
				$res =& $pearDB->query("SELECT ldap_host, ldap_port, ldap_base_dn, ldap_login_attrib, ldap_ssl, ldap_auth_enable FROM general_opt LIMIT 1");
				$ldap_auth = $res->fetchRow();
				$debug_auth = $debug['debug_auth'];
				$debug_path = $debug['debug_path'];
				if (!isset($debug_auth))
					$debug_auth = 0;
				$connect = true;
				$fallback = false;
				if ($ldap_auth['ldap_auth_enable'] == 1 && $contact['contact_auth_type'] == "ldap" ) {
					$connect = true;

					# BugFix  #265
					if  ((!(isset($contact['contact_ldap_dn'] )) || $contact['contact_ldap_dn']  == '' ) ) {
						$contact['contact_ldap_dn']  = "anonymous" ;
						if ($debug_auth == 1)
							error_log("[" . date("d/m/Y H:s") ."] LDAP User Mapping : ". $useralias ." don't have LDAP DN information ! Switching to anonymous\n", 3, $debug_path."auth.log");
					}
	   				#

					if ($debug_auth == 1)
						error_log("[" . date("d/m/Y H:s") ."] LDAP User Mapping : ". $useralias ." => " . $contact['contact_ldap_dn'] . "\n", 3, $debug_path."auth.log");

					if ($ldap_auth['ldap_ssl'])
						$ldapuri = "ldaps://" ;
					else
						$ldapuri = "ldap://" ;

					$ds = ldap_connect($ldapuri . $ldap_auth['ldap_host'].":".$ldap_auth['ldap_port']);
					if ($debug_auth == 1)
						error_log("[" . date("d/m/Y H:s") ."] LDAP Auth Cnx  : ". $ldapuri . $ldap_auth['ldap_host'].":".$ldap_auth['ldap_port']  ." : " . ldap_error($ds) . " (" . ldap_errno($ds) . ")" . "\n", 3, $debug_path."auth.log");
					@ldap_bind($ds, $contact['contact_ldap_dn'], $password);
					if ($debug_auth == 1)
						error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH Bind : ". $contact['contact_ldap_dn'] ." : " . ldap_error($ds) . " (" . ldap_errno($ds) . ")" . "\n", 3, $debug_path."auth.log");

					/* In some case, we fallback to local Auth
					  0 : Bind succesfull => Default case
					 -1 : Can't contact LDAP server (php4) => Fallback
					 51 : Server is busy => Fallback
					 52 : Server is unavailable => Fallback
					 81 : Can't contact LDAP server (php5) => Fallback
					 Else : Go away !!
					*/
					if ($ds) {
						switch (ldap_errno($ds)) {
						case 0:
						   $connect = true;
						   $fallback = false;
							if ($debug_auth == 1)
								error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH : OK, let's go to Local AUTH\n", 3, $debug_path."auth.log");
						   break;
						case -1:
						case 51:
						case 52:
						case 81:
							$connect = false;
							$fallback = true;
							if ($debug_auth == 1)
								error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH : Error, Fallback to Local AUTH\n", 3, $debug_path."auth.log");
						   break;
						default:
						   $connect = false;
						   $fallback = false;
						   if ($debug_auth == 1)
								error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH : LDAP don't like you, sorry \n", 3, $debug_path."auth.log");
						   break;
						}

					//if ($ds && ((ldap_errno($ds) == 0 ) || (ldap_errno($ds) == -1 )  || (ldap_errno($ds) == 51 ) || (ldap_errno($ds) == 52 ) || (ldap_errno($ds) == 81 ) )) {
					//	$connect = true;
					//	if ($debug_auth == 1)
					//		error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH : OK, let's go to Local AUTH\n", 3, $debug_path."auth.log");
					} else {
						$connect = false;
						$fallback = false;
					}
					ldap_close($ds);
				}
				$res->free();
				//update password in mysql database to provide login even if there is LDAP connection
				if (isset($_POST["submit"]) && $ldap_auth['ldap_auth_enable'] == 1 && $contact['contact_auth_type'] == "ldap" && $connect && !$fallback) {
					$pearDB->query("UPDATE contact set contact_passwd = '".md5($password)."' WHERE contact_alias ='".$useralias."' ");
					if ($debug_auth == 1)
						error_log("[" . date("d/m/Y H:s") ."] LDAP AUTH : Local password updated with LDAP password for $useralias \n", 3, $debug_path."auth.log");
				}
				if ($connect || $fallback) {
					if ($debug_auth == 1)
						error_log("[" . date("d/m/Y H:s") ."] Local AUTH : Local Auth or LDAP Fallback\n", 3, $debug_path."auth.log");
					// Autologin case => contact_alias is MD5 format
					if (!isset($_POST["submit"]))
						$res =& $pearDB->query("SELECT * FROM contact WHERE MD5(contact_alias)='".htmlentities($useralias, ENT_QUOTES)."' and contact_passwd='".htmlentities($password, ENT_QUOTES)."' AND contact_activate = '1' LIMIT 1");
					// Normal auth
					else
						$res =& $pearDB->query("SELECT * FROM contact WHERE contact_alias='".htmlentities($useralias, ENT_QUOTES)."' and contact_passwd='".md5(htmlentities($password, ENT_QUOTES))."' AND contact_activate = '1' LIMIT 1");
					if ($res->numRows() ) {
						if ($debug_auth == 1)
							error_log("[" . date("d/m/Y H:s") ."] Local AUTH : User " . $useralias ." Successfully authentificated\n", 3, $debug_path."auth.log");
						global $oreon;
						$res2 =& $pearDB->query("SELECT nagios_version FROM general_opt");
						$version = $res2->fetchRow();
						$user =& new User($res->fetchRow(), $version["nagios_version"]);
						//$user->createLCA($pearDB);
						$oreon = new Oreon($user);
						$_SESSION["oreon"] =& $oreon;
						$res =& $pearDB->query("SELECT session_expire FROM general_opt LIMIT 1");
						$session_expire =& $res->fetchRow();
						$res =& $pearDB->query("SELECT * FROM session");
						while ($session =& $res->fetchRow())
							if ($session["last_reload"] + ($session_expire["session_expire"] * 60) <= time())
								$pearDB->query("DELETE FROM session WHERE session_id = '".$session["session_id"]."'");
							$pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
						if (!isset($_POST["submit"]))	{
							$args = NULL;
							foreach($_GET as $key=>$value)
								$args ? $args .= "&".$key."=".$value : $args = $key."=".$value;
							header("Location: ./oreon.php?".$args."");
						}
						else
							header("Location: ./oreon.php");
						$connect = true;
					}
				}
			}
		}
	}

	$res =& $pearDB->query("SELECT template FROM general_opt LIMIT 1");
	$res->fetchInto($data);
	$skin = "./Themes/".$data["template"]."/";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Oreon, Revisited Experience Of Nagios</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="<? echo $skin; ?>login.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="./img/iconOreon.ico">
</head>
<body OnLoad="document.login.useralias.focus();">
<? include_once("./login.php"); ?>
</body>
</html>