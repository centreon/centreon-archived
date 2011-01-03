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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/comments/comments.php $
 * SVN : $Id: comments.php 10505 2010-05-25 21:28:36Z jmathis $
 *
 */

	if (!isset($oreon)) {
		exit ();
	}

	isset($_GET["contact_id"]) ? $cG = $_GET["contact_id"] : $cG = NULL;
	isset($_POST["contact_id"]) ? $cP = $_POST["contact_id"] : $cP = NULL;
	$cG ? $contact_id = $cG : $contact_id = $cP;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/monitoring/comments/";

	/*
	 * PHP functions
	 */
	require_once "./include/common/common-Func.php";
	require_once "./include/monitoring/comments/common-Func.php";
	require_once "./include/monitoring/external_cmd/functions.php";

	switch ($o)	{
		case "as" :
			require_once($path."AddSvcComment.php");
			break;
		case "ds" :
			DeleteComment("SVC",isset($_GET["select"]) ? $_GET["select"] : array());
			require_once($path."viewServiceComment.php");
			break;
		case "vs" :
			require_once($path."viewServiceComment.php");
			break;
		default :
			require_once($path."viewServiceComment.php");
			break;
	}
?>