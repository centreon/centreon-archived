<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
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
$helps[] = array('name' => $tag . '[' . $pos . '][name]', 'desc' => _('The name of block configuration'));
$helps[] = array('name' => $tag . '[' . $pos . '][type]', 'desc' => _('The type of block configuration'));
$cbObj->nbSubGroup = 1;
textdomain('help');
foreach ($fields as $field) {
    $fieldname = '';
    if ($field['group'] !== '') {
        $fieldname .= $cbObj->getParentGroups($field['group']);
    }
    $fieldname .= $field['fieldname'];
    $helps[] = array('name' => $tag . '[' . $pos . '][' . $fieldname . ']', 'desc' => _($field['description']));
}
textdomain('messages');

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
