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

	aff_header("Centreon Upgrade Wizard", "Licence", 2);
	$license_file_name = "./LICENSE.txt";
	$fh = fopen( $license_file_name, 'r' ) or die( "License file not found!" );
	$license_file = fread( $fh, filesize( $license_file_name ) );
	fclose($fh);
	$str = "<textarea cols='80' rows='20' readonly>".$license_file."</textarea>";
	$str .= "</td>
	</tr>
	<tr>
	  <td align=\"left\">
		<input type='checkbox' class='checkbox' name='setup_license_accept' onClick='LicenceAccepted();' value='0' /><a href=\"javascript:void(0)\" onClick=\"LicenceAcceptedByLink();\">I Accept</a>
	  </td>
	  <td align=right>
		&nbsp;
	  </td>
	</tr>";
	print $str;
	aff_middle();
	print "<input class='button' type='submit' name='goto' value='Next' id='button_next' disabled='disabled' />";
	aff_footer();

?>