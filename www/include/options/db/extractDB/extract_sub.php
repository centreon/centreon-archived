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

	$oreon = & $_SESSION["oreon"];

	if (!isset($oreon))
		exit();

	///////////////////
	$host   = $conf_oreon["host"];        /// NOM DU SERVEUR SQL
	$user   = $conf_oreon["user"];        /// LOGIN
	$pass   = $conf_oreon["password"];    /// PASS
	$db     = $conf_oreon["db"];          /// NOM DE LA DATABASE
	///////////////////

	@set_time_limit(600);

	@mysql_connect($host,$user,$pass)
	    or die("Impossible de se connecter - Pb sur le 'Hostname' ou sur le 'User' ou sur le 'Password'");

	@mysql_select_db("$db")
	    or die("Impossible de se connecter - Pb sur le 'Nom de la Data Base'");

	function get_table_def($db, $table, $crlf) {
	    global $drop;
	    $schema_create = "";
	    if(!empty($drop))
	        $schema_create .= "DROP TABLE IF EXISTS `$table`;$crlf";
	    $schema_create .= "CREATE TABLE `$table` ($crlf";
	    $result = mysql_db_query($db, "SHOW FIELDS FROM $table") or mysql_die();
	    while($row = mysql_fetch_array($result)) {
	        $schema_create .= "   `$row[Field]` $row[Type]";
	        if (isset($row["Default"])
	           && (!empty($row["Default"]) || $row["Default"] == "0"))
	            $schema_create .= " DEFAULT '$row[Default]'";
	        if ($row["Null"] != "YES")
	            $schema_create .= " NOT NULL";
	        if ($row["Extra"] != "")
	            $schema_create .= " $row[Extra]";
	        $schema_create .= ",$crlf";
	    }
	    $schema_create = ereg_replace(",".$crlf."$", "", $schema_create);
	    $result = mysql_db_query($db, "SHOW KEYS FROM $table") or mysql_die();
	    while ($row = mysql_fetch_array($result)) {
	        $kname=$row['Key_name'];
	        if (($kname != "PRIMARY") && ($row['Non_unique'] == 0))
	            $kname="UNIQUE|$kname";
	        if (!isset($index[$kname]))
	            $index[$kname] = array();
	        $index[$kname][] = $row['Column_name'];
	    }
	    while (list($x, $columns) = @each($index)){
	        $schema_create .= ",$crlf";
	        if ($x == "PRIMARY")
	            $schema_create .= " PRIMARY KEY (`" . implode($columns, ", ") . "`)";

	        else if (substr($x,0,6) == "UNIQUE")
	            $schema_create .= " UNIQUE `".substr($x,7)."` (`".implode($columns,", ")."`)";
	        else
	            $schema_create .= " KEY `$x` (`" . implode($columns, ", ") . "`)";
	    }
	    $schema_create .= "$crlf)";
	    return (stripslashes($schema_create));
	}

	function get_table_content($db, $table, $handler) {
		$str = "";
	    $result = mysql_db_query($db, "SELECT * FROM $table") or mysql_die();
	    $i = 0;
	    while($row = mysql_fetch_row($result)) {
	        $table_list = "(";
	        for($j=0; $j<mysql_num_fields($result);$j++)
	            $table_list .= mysql_field_name($result,$j).", ";
	        $table_list = substr($table_list,0,-2);
	        $table_list .= ")";
	        if(isset($GLOBALS["showcolumns"]))
	            $schema_insert = "INSERT INTO `$table` $table_list VALUES (";
	        else
	            $schema_insert = "INSERT INTO `$table` VALUES (";
	        for($j=0; $j<mysql_num_fields($result);$j++)  {
	            if(!isset($row[$j]))
	                $schema_insert .= " NULL,";
	           elseif($row[$j] != "")
	                $schema_insert .= " '".addslashes($row[$j])."',";
	            else
	            $schema_insert .= " '',";
	        }
	        $schema_insert = ereg_replace(",$", "", $schema_insert);
	        $schema_insert .= ")";
	        $str = $handler(trim($schema_insert), $str);
	        $i++;
	    }
	    return ($str);
	}

	function my_handler($sql_insert, $str) {
	    global $crlf, $asfile;
	    $str .= "$sql_insert;$crlf";
		return $str;
	}


	///////////////////

	///////////////////

	$crlf="\n";
	$strTableStructure = "Table structure for table";
	$strDumpingData = "Dumping data for table";
	$tables = mysql_list_tables($db);
	$num_tables = @mysql_numrows($tables);
	$i = 0;
	$str = "";


	while($i < $num_tables){
		$table = mysql_tablename($tables, $i);
		$str .= $crlf;
		$str .= "# --------------------------------------------------------$crlf";
		$str .= "#$crlf";
		$str .= "# $strTableStructure '$table'$crlf";
		$str .= "#$crlf";
		$str .= $crlf;
		$str .= get_table_def($db, $table, $crlf).";$crlf$crlf";
		$str .= "#$crlf";
		$str .= "# $strDumpingData '$table'$crlf";
		$str .= "#$crlf";
		$str .= $crlf;
		$str .= get_table_content($db, $table, "my_handler");
		$i++;
	}
	echo $str;
	mysql_close();
?>
