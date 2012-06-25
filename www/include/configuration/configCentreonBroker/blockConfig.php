<?php
/*
 * Copyright 2005-2011 MERETHIS
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

    if (!isset($oreon)) {
		exit();
	}

	if (!isset($_GET['tag']) || !isset($_GET['pos']) || !isset($_GET['blockId'])) {
	    exit();
	}

	/*
	 * Cast the block id in int
	 */
	try {
	    $id = (string)$_GET['blockId'];
	    $tag = (string)$_GET['tag'];
	    $pos = (int)$_GET['pos'];
	} catch (Exception $e) {
	    exit();
	}

	$cbObj = new CentreonConfigCentreonBroker($pearDB);

	$form = $cbObj->quickFormById($id, $_GET['p'], $pos);

	$helps = array();
	list($tagId, $typeId) = explode('_', $id);
	$typeName = $cbObj->getTypeName($typeId);
	$fields = $cbObj->getBlockInfos($typeId);
	$helps[] = array('name' => $tag . '[' . $pos . '][name]', 'desc' => _('The name of block configuration'));;
	$helps[] = array('name' => $tag . '[' . $pos . '][type]', 'desc' => _('The type of block configuration'));;
	foreach ($fields as $field) {
	    $helps[] = array('name' => $tag . '[' . $pos . '][' . $field['fieldname'] . ']', 'desc' => _($field['description']));
	}

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('formBlock', $renderer->toArray());
	$tpl->assign('typeName', $typeName);
	$tpl->assign('tagBlock', $tag);
	$tpl->assign('posBlock', $pos);
	$tpl->assign('helps', $helps);

	$tpl->display("blockConfig.ihtml");
?>