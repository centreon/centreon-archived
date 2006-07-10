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
	elseif (file_exists("./oreon.conf.php") && is_dir('install'))
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

	if (isset($_POST["submit"])) {
		require_once("DBconnect.php");
		$res =& $pearDB->query("SELECT ldap_host, ldap_port, ldap_base_dn, ldap_login_attrib, ldap_ssl, ldap_auth_enable  FROM general_opt LIMIT 1");
		$res->fetchInto($ldap_auth);

		$res =& $pearDB->query("SELECT * FROM contact WHERE contact_alias='".htmlentities($_POST["useralias"], ENT_QUOTES)."' AND contact_activate = '1' LIMIT 1");
		if($res->numRows()) {
			while($contact =& $res->fetchRow()) {
				if(isset($contact['contact_auth_type']) && $contact['contact_auth_type']=='ldap' && $ldap_auth['ldap_auth_enable']=='1' ) {

					if ($ldap_auth['ldap_ssl'] == "0") $ldapuri = "ldap://" ;
					if ($ldap_auth['ldap_ssl'] == "1") $ldapuri = "ldaps://" ;
     				// if no LDAP connection, use local mysql authentication
					$ds = ldap_connect($ldapuri . $ldap_auth['ldap_host'].":".$ldap_auth['ldap_port']) or $contact['contact_auth_type']='local' ;
					if($ds) {
						$userdn = $contact['contact_ldap_dn'];
						$r = @ldap_bind($ds,$userdn,$_POST['password']) ;
						if($r) {
							//update password in mysql database to provide login even if there is LDAP connection
							$pearDB->query("UPDATE contact set contact_passwd = '".md5($_POST['password'])."' WHERE contact_alias ='".$contact['contact_alias']."' ");

							$res =& $pearDB->query("SELECT * FROM contact WHERE contact_alias='".htmlentities($_POST["useralias"], ENT_QUOTES)."' and contact_passwd='".md5($_POST["password"])."' AND contact_activate = '1' LIMIT 1");

							if ($res->numRows()) {
								global $oreon;
								$res2 =& $pearDB->query("SELECT nagios_version FROM general_opt");
								$version = $res2->fetchRow();
								$user =& new User($res->fetchRow(), $version["nagios_version"]);
								$user->createLCA($pearDB);
								$oreon = new Oreon($user);
								$_SESSION["oreon"] =& $oreon;
								$res =& $pearDB->query("SELECT session_expire FROM general_opt LIMIT 1");
								$session_expire =& $res->fetchRow();
								$res =& $pearDB->query("SELECT * FROM session");
								while ($session =& $res->fetchRow())
									if ($session["last_reload"] + ($session_expire["session_expire"] * 60) <= time())
										$pearDB->query("DELETE FROM session WHERE session_id = '".$session["session_id"]."'");
								$res =& $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
								header("Location: ./oreon.php?p=1");
							}
						}
					ldap_close($ds);
					} else {
						$msg_error = "Enable to connect to LDAP Server for authentification";
					}
				}
				if((!isset($contact['contact_auth_type'])) || ($contact['contact_auth_type']=='') || (isset($contact['contact_auth_type']) && $contact['contact_auth_type']=='local')) {

					$res =& $pearDB->query("SELECT * FROM contact WHERE contact_alias='".htmlentities($_POST["useralias"], ENT_QUOTES)."' and contact_passwd='".md5($_POST["password"])."' AND contact_activate = '1' LIMIT 1");
					if ($res->numRows()) {
						global $oreon;
						$res2 =& $pearDB->query("SELECT nagios_version FROM general_opt");
						$version = $res2->fetchRow();
						$user =& new User($res->fetchRow(), $version["nagios_version"]);
						$user->createLCA($pearDB);
						$oreon = new Oreon($user);
						$_SESSION["oreon"] =& $oreon;
						$res =& $pearDB->query("SELECT session_expire FROM general_opt LIMIT 1");
						$session_expire =& $res->fetchRow();
						$res =& $pearDB->query("SELECT * FROM session");
						while ($session =& $res->fetchRow())
							if ($session["last_reload"] + ($session_expire["session_expire"] * 60) <= time())
								$pearDB->query("DELETE FROM session WHERE session_id = '".$session["session_id"]."'");
						$res =& $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");

						header("Location: ./oreon.php?p=1");
					}
				}
			}
		}
	}
	else if (isset($_GET["autologin"]) && isset($_GET["p"]) && $_GET["autologin"] && $_GET["p"])	{
		require_once("DBconnect.php");
		$res =& $pearDB->query("SELECT * FROM contact WHERE MD5(contact_alias)='".$_GET["useralias"]."' and contact_passwd='".$_GET["password"]."' AND contact_activate = '1' LIMIT 1");
		if ($res->numRows()) {
			global $oreon;
			$res2 =& $pearDB->query("SELECT nagios_version FROM general_opt");
			$version = $res2->fetchRow();
			$user =& new User($res->fetchRow(), $version["nagios_version"]);
			$user->createLCA($pearDB);
			$oreon = new Oreon($user);
			$_SESSION["oreon"] =& $oreon;
			$res =& $pearDB->query("SELECT session_expire FROM general_opt LIMIT 1");
			$session_expire =& $res->fetchRow();
			$res =& $pearDB->query("SELECT * FROM session");
			while ($session =& $res->fetchRow())
				if ($session["last_reload"] + ($session_expire["session_expire"] * 60) <= time())
					$pearDB->query("DELETE FROM session WHERE session_id = '".$session["session_id"]."'");
			$res =& $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
			$args = NULL;
			foreach($_GET as $key=>$value)
				$args ? $args .= "&".$key."=".$value : $args = $key."=".$value;
			header("Location: ./oreon.php?".$args."");
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Oreon - Nagios Solution</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="./Themes/Basic/login.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="./img/iconOreon.ico">
</head>
<body OnLoad="document.login.useralias.focus();">
<? include_once("./login.php"); ?>
</body>
</html>

