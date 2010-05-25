<?php 
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonMedia.class.php $
 * SVN : $Id: centreonMedia.class.php 9342 2009-11-10 23:10:03Z jmathis $
 * 
 */

/*
 *  Class used for managing images
 */
class CentreonMedia {
	private $DB;
	
	/*
	 *  Constructor
	 */
	function CentreonMedia($DB) {
		$this->DB = $DB;
	}
	
	/*
	 *  Returns ID of target directory
	 */
	function getDirectoryId($dirname) {
		$query = "SELECT dir_id FROM view_img_dir WHERE dir_name = '".$dirname."' LIMIT 1";
		$RES =& $this->DB->query($query);
		$dir_id = NULL;
		if ($RES->numRows()) {
			$row =& $RES->fetchRow();
			$dir_id = $row['dir_id'];
		}
		return $dir_id;
	}
	
	/*
	 *  Returns ID of target Image
	 */
	function getImageId($imagename, $dirname = NULL) {
		if (!isset($dirname)) {
			$tab = split("/", $imagename);
			isset($tab[0]) ? $dirname = $tab[0] : $dirname = NULL;
			isset($tab[1]) ? $imagename = $tab[1] : $imagename = NULL;			
		}
		
		if (!isset($imagename) || !isset($dirname)) {
			return NULL;
		}
		
		$query = "SELECT img.img_id ". 
				"FROM view_img_dir dir, view_img_dir_relation rel, view_img img ". 
				"WHERE dir.dir_id = rel.dir_dir_parent_id " .
				"AND rel.img_img_id = img.img_id ".
				"AND img.img_path = '".$imagename."' ".
				"AND dir.dir_name = '".$dirname."' " .  
				"LIMIT 1";		
		$RES =& $this->DB->query($query);
		$img_id = NULL;
		if ($RES->numRows()) {
			$row =& $RES->fetchRow();
			$img_id = $row['img_id'];
		}
		return $img_id;
	}
}

?>