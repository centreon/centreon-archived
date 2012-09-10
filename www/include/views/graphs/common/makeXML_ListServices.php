<?php
/*
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');

	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";
	require_once $centreon_path."/www/class/centreonXML.class.php";
	/*
 	 * Get session
     */
    require_once ($centreon_path . "www/class/centreonSession.class.php");
    require_once ($centreon_path . "www/class/centreon.class.php");
    if(!isset($_SESSION['centreon'])) {
            CentreonSession::start();
    }
    if (isset($_SESSION['centreon'])) {
        $oreon = $_SESSION['centreon'];
    } else {
        exit;
    }

	$pearDBO = new CentreonDB("centstorage");

	/*
	 * Get language
	 */
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages",  $centreon_path . "www/locale/");;
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");

	# replace array
	$a_this = array( "#S#", "#BS#" );
	$a_that = array( "/", "\\" );

	#
	# Existing services data comes from DBO -> Store in $s_datas Array
	$s_datas = array(""=> sprintf("%s%s", _("Service list"), "&nbsp;&nbsp;&nbsp;"));
	$mx_l = strlen($s_datas[""]);

	if (isset($_GET["host_id"]) && $_GET["host_id"] != 0) {
		$pq_sql = $pearDBO->query("SELECT id index_id, service_description FROM index_data WHERE host_id='".$_GET['host_id']."'ORDER BY service_description");
		while($fw_sql = $pq_sql->fetchRow()) {
			$fw_sql["service_description"] = str_replace($a_this, $a_that, $fw_sql["service_description"]);
			$s_datas[$fw_sql["index_id"]] = $fw_sql["service_description"]."&nbsp;&nbsp;&nbsp;";
			$sd_l = strlen($fw_sql["service_description"]);
			if ( $sd_l > $mx_l)
				$mx_l = $sd_l;
    	}
		$pq_sql->free();
	}
    for ($i = strlen($s_datas[""]); $i != $mx_l; $i++)
		$s_datas[""] .= "&nbsp;";

	/*
	 *  The first element of the select is empty
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("options_data");
	$buffer->writeElement("td_id", "td_list_hsr");
	$buffer->writeElement("select_id", "sl_list_services");

	/*
	 *  Now we fill out the select with templates id and names
	 */
	foreach ($s_datas as $o_id => $o_alias){
		$buffer->startElement("option");
		$buffer->writeElement("o_id", $o_id);
		$buffer->writeElement("o_alias", $o_alias);
		$buffer->endElement();
	}
	$buffer->endElement();
	$buffer->output();
?>
