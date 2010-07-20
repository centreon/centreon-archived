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
 * For more information : contact@centreon.com
 * 
 * File : Image-Func.php D.Porte
 * 
 */

	function subsRPN($rpn, $vname) {
		$l_list = split(",",$rpn);
		$l_rpn = "";
		foreach( $l_list as $l_m) {
			if ( isset($vname[$l_m]) )
				$l_rpn .= $vname[$l_m].",";
			else
				$l_rpn .= $l_m.",";
		}
		return substr($l_rpn,0,strlen($l_rpn) - 1);
	}

?>
