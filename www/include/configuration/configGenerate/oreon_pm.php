<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	$file = $oreon->optGen["nagios_path_plugins"]."oreon.conf" ;
	$handle = fopen($file, 'rw');
	$ini = readINIfile ($file, ";");
	// We modify [GLOBAL] section
	$ini["GLOBAL"]["DIR_OREON"] = $oreon->optGen["oreon_path"];
	$ini["GLOBAL"]["NAGIOS_LIBEXEC"] = $oreon->optGen["nagios_path_plugins"];
	$ini["GLOBAL"]["NAGIOS_ETC"] = $oreon->Nagioscfg["cfg_dir"];
	$ini["GLOBAL"]["DIR_RRDTOOL"] = $oreon->optGen["oreon_rrdbase_path"];
	
	# other section
	$ini["NT"]["CPU"] = ".1.3.6.1.2.1.25.3.3.1.2";
	$ini["NT"]["HD_USED"] = ".1.3.6.1.2.1.25.2.3.1.6";
	$ini["NT"]["HD_NAME"] = ".1.3.6.1.2.1.25.2.3.1.3";
	
	$ini["CISCO"]["NB_CONNECT"] = ".1.3.6.1.4.1.9.9.147.1.2.2.2.1.5.40.6";
	
	$ini["UNIX"]["CPU_USER"] = ".1.3.6.1.4.1.2021.11.50.0";
	$ini["UNIX"]["CPU_SYSTEM"] = ".1.3.6.1.4.1.2021.11.52.0";
	$ini["UNIX"]["CPU_LOAD_1M"] = ".1.3.6.1.4.1.2021.10.1.3.1";
	$ini["UNIX"]["CPU_LOAD_5M"] = ".1.3.6.1.4.1.2021.10.1.3.2";
	$ini["UNIX"]["CPU_LOAD_15M"] = ".1.3.6.1.4.1.2021.10.1.3.3";
	
	$ini["DELL"]["TEMP"] = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.1";
	
	$ini["ALTEON"]["VIRT"] = "1.3.6.1.4.1.1872.2.1.8.2.7.1.3.1";
	$ini["ALTEON"]["FRONT"] = "1.3.6.1.4.1.1872.2.1.8.2.5.1.3.1";
	
	$ini["MIB2"]["SW_RUNNAME"] = ".1.3.6.1.2.1.25.4.2.1.2";
	$ini["MIB2"]["SW_RUNINDEX"] = ".1.3.6.1.2.1.25.4.2.1.1";
	$ini["MIB2"]["SW_RUNSTATUS"] = ".1.3.6.1.2.1.25.4.2.1.7";
	$ini["MIB2"]["HR_STORAGE_DESCR"] = ".1.3.6.1.2.1.25.2.3.1.3";
	$ini["MIB2"]["HR_STORAGE_ALLOCATION_UNITS"] = ".1.3.6.1.2.1.25.2.3.1.4";
	$ini["MIB2"]["HR_STORAGE_SIZE"] = ".1.3.6.1.2.1.25.2.3.1.5";
	$ini["MIB2"]["HR_STORAGE_USED"] = ".1.3.6.1.2.1.25.2.3.1.6";
	$ini["MIB2"]["OBJECTID"] = ".1.3.6.1.2.1.1.1.0";
	$ini["MIB2"]["UPTIME_WINDOWS"] = ".1.3.6.1.2.1.1.3.0";
	$ini["MIB2"]["UPTIME_OTHER"] = ".1.3.6.1.2.1.25.1.1.0";
	$ini["MIB2"]["IF_IN_OCTET"] = ".1.3.6.1.2.1.2.2.1.10";
	$ini["MIB2"]["IF_OUT_OCTET"] = ".1.3.6.1.2.1.2.2.1.16";
	$ini["MIB2"]["IF_SPEED"] = ".1.3.6.1.2.1.2.2.1.5";
	$ini["MIB2"]["IF_DESC"] = ".1.3.6.1.2.1.2.2.1.2";
	
	# We write conf file
	// We write conf file
	writeINIfile($file,$ini, "", "");
	fclose($handle);

/*
Function to replace PHP's parse_ini_file() with much fewer restritions, and
a matching function to write to a .INI file, both of which are binary safe.

Version 1.0

Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>

Sections can use any character excluding ASCII control characters and ASCII
DEL.  (You may even use [ and ] characters as literals!)

Keys can use any character excluding ASCII control characters, ASCII DEL,
ASCII equals sign (=), and not start with the user-defined comment
character.

Values are binary safe (encoded with C-style backslash escape codes) and may
be enclosed by double-quotes (to retain leading & trailing spaces).

User-defined comment character can be any non-white-space ASCII character
excluding ASCII opening bracket ([).

readINIfile() is case-insensitive when reading sections and keys, returning
an array with lower-case keys.
writeINIfile() writes sections and keys with first character capitalization.
Invalid characters are converted to ASCII dash / hyphen (-).  Values are
always enclosed by double-quotes.

writeINIfile() also provides a method to automatically prepend a comment
header from ASCII text with line breaks, regardless of whether CRLF, LFCR,
CR, or just LF line break sequences are used!  (All line breaks are
translated to CRLF)

Modified for Oreon by Christophe Coraboeuf
*/

function readINIfile ($filename, $commentchar) {
  $array1 = array();
  $array2 = array();
  $array1 = file($filename);
  $section = '';
  foreach ($array1 as $filedata) {
   $dataline = trim($filedata);
   $firstchar = substr($dataline, 0, 1);
   if ($firstchar!=$commentchar && $dataline!='') {
     //It's an entry (not a comment and not a blank line)
     if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
       //It's a section
       $section = strtoupper(substr($dataline, 1, -1));
     }else{
       //It's a key...
       $delimiter = strpos($dataline, '=');
       if ($delimiter > 0) {
         //...with a value
         $key = strtoupper(trim(substr($dataline, 0, $delimiter)));
         $value = trim(substr($dataline, $delimiter + 1));
         if (substr($value, 0, 1) == '"' && substr($value, -1, 1) == '"') { $value = substr($value, 1, -1); }
         $array2[$section][$key] = stripcslashes($value);
       }else{
         //...without a value
         $array2[$section][strtoupper(trim($dataline))]='';
       }
     }
   }else{
     //It's a comment or blank line.  Ignore.
   }
  }
  return $array2;
}

function writeINIfile ($filename, $array1, $commentchar, $commenttext) {
  $handle = fopen($filename, 'wb');
  if ($commenttext!='') {
   $comtext = $commentchar.
     str_replace($commentchar, "\r\n".$commentchar,
       str_replace ("\r", $commentchar,
         str_replace("\n", $commentchar,
           str_replace("\n\r", $commentchar,
             str_replace("\r\n", $commentchar, $commenttext)
           )
         )
       )
     )
   ;
   if (substr($comtext, -1, 1)==$commentchar && substr($comtext, -1, 1)!=$commentchar) {
     $comtext = substr($comtext, 0, -1);
   }
   fwrite ($handle, $comtext."\r\n");
  }
  foreach ($array1 as $sections => $items) {
   //Write the section
   if (isset($section)) { fwrite ($handle, "\r\n"); }
   //$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
   $section = strtoupper(preg_replace('/[\0-\37]|\177/', "-", $sections));
   fwrite ($handle, "[".$section."]\r\n");
   foreach ($items as $keys => $values) {
     //Write the key/value pairs
     $key = strtoupper(preg_replace('/[\0-\37]|=|\177/', "-", $keys));
     if (substr($key, 0, 1)==$commentchar) { $key = '-'.substr($key, 1); }
   //  if (substr($values, 0, 1) == '"' && substr($values, -1, 1) == '"') { $values = substr($values, 1, -1); }
     $value = ucfirst(addcslashes($values,''));
     fwrite ($handle, '    '.$key.'='.$value."\r\n");
   }
  }
  fclose($handle);
}
?>