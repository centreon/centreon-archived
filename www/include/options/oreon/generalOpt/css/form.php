<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
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

	if (!isset($oreon))
		exit();

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	
	while ($opt =& $DBRESULT->fetchRow()) {
		$data[$opt["key"]] = myDecode($opt["value"]);
	}
	
	$skin = "./Themes/".$data["template"]."/";
	
	$tab_file_css = array();
	$i = 0;
	if ($handle  = @opendir($skin."Color"))	{
		while ($file = @readdir($handle)){
			if (is_file($skin."Color"."/$file"))	{
				$tab_file_css[$i++] = $file;
			}
		}
		@closedir($handle);
	}

	$css_default = $tab_file_css[0];
	$rq = "SELECT * FROM css_color_menu";
	$DBRESULT =& $pearDB->query($rq);
	$tab_css = array();
	for ($i = 0; $DBRESULT->numRows() && $elem =& $DBRESULT->fetchRow();$i++){
		$tab_css[$elem["menu_nb"]] = $elem;
		if(isset($_GET["css_color_".$elem["id_css_color_menu"]])){
			$name = $_GET["css_color_".$elem["id_css_color_menu"]];			
			$id = $elem["id_css_color_menu"];
			$rq = "UPDATE `css_color_menu` SET `css_name` = '".$name."' WHERE `id_css_color_menu` = $id";
			$res =& $pearDB->query($rq);
		}		
	}
	
	$rq = "SELECT topology_id, topology_name, topology_page FROM topology WHERE topology_parent IS NULL AND topology_show = '1' ORDER BY topology_order";
	$DBRESULT =& $pearDB->query($rq);
	$tab_menu = array();
	while ($DBRESULT->numRows() && $elem =& $DBRESULT->fetchRow()){
		$tab_menu[$elem["topology_page"]] = $elem;
	}
	## insert new menu in table css_color_menu
	$tab_create_menu = array();
	foreach ($tab_menu as $key => $val)	{
		if(!isset($tab_css[$tab_menu[$key]["topology_page"]]))	{
			$rq = "INSERT INTO `css_color_menu` ( `id_css_color_menu` , `menu_nb` , `css_name` )" .
					"VALUES ( NULL , ".$tab_menu[$key]["topology_page"].", '".$css_default."' )";
			$DBRESULT =& $pearDB->query($rq);
		}
	}
	
	/*
	 * Get menu_css_bdd list
	 */
	$rq = "SELECT * FROM css_color_menu";
	$DBRESULT =& $pearDB->query($rq);
	$elemArr = array();
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	if ($DBRESULT->numRows())
		for ($i = 0; $elem =& $DBRESULT->fetchRow();$i++)	{
				$select_list =	'<select name="css_color_'. $elem["id_css_color_menu"] .'">';
				for ($j=0 ; isset($tab_file_css[$j]) ; $j++){
					
					if($elem["css_name"] == $tab_file_css[$j]) {
						$selected = "selected";
					} else {
						$selected = "";	
					}
					
					$select_list .= '<option value="'.$tab_file_css[$j].'" ' . $selected . '>'.$tab_file_css[$j].'</option>';
				}
				$select_list .= '</select>';
				$elemArr[$i] = array("MenuClass"=>"list_".$style,
									 "select"=> $select_list,
									 "menuName"=> _($tab_menu[$elem["menu_nb"]]["topology_name"]),
									 "css_name"=> $elem["css_name"]);
				$style != "two" ? $style = "two" : $style = "one";
		}
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'css/', $tpl);

	/*
	 * Apply a template definition
	 */
	
	$tpl->assign("elemArr", $elemArr);
	$tpl->assign('submitTitle', _("Save"));
	$tpl->assign('nameTitle', _("Menu"));
	$tpl->assign('fileTitle', _("CSS File"));
	$tpl->assign('o', $o);
	$tpl->assign("CSS_configuration", _("CSS configuration"));
	$tpl->assign("CSS_File", _("CSS File"));
	$tpl->assign("Menu", _("Menu"));
	$tpl->assign('p', $p);
	$tpl->display("form.ihtml");	
?>