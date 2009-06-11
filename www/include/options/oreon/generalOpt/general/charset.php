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

	$arrayCharSet = array(  "iso-8859-1", "ISO-8859-2"
							, "ISO-8859-3", "ISO-8859-4"
							, "ISO-8859-5", "ISO-8859-6"
							, "ISO-8859-7", "ISO-8859-8"
							, "ISO-8859-9", "utf-80"
							, "utf-83", "utf-84"
							, "utf-85", "utf-86"
							, "ISO-2022-JP", "ISO-2022-KR"
							, "ISO-2022-CN", "WINDOWS-1251"
							, "CP866", "KOI8"
							, "KOI8-E", "KOI8-r"
							, "KOI8-U", "KOI8-ru"
							, "ISO-10646-UCS-2"
							, "ISO-10646-UCS-4"
							, "UTF-7", "UTF-8"
							, "UTF-16", "UTF-16BE"
							, "UTF-16LE", "UTF-32"
							, "UTF-32BE", "UTF-32LE"
							, "euc-cn", "euc-gb"
							, "euc-jp", "euc-kr"
							, "EUC-TW", "gb2312"
							, "iso-10646-ucs-2"
							, "iso-10646-ucs-4"
							, "shift_jis");
?>

