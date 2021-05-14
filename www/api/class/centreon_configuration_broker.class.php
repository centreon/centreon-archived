<?php
/*
 * Copyright 2005-2015 Centreon
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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationBroker extends CentreonConfigurationObjects
{
    /**
     * @return array
     * @throws Exception
     */
    public function getBlock()
    {
        if (!isset($this->arguments['page']) ||
            !isset($this->arguments['position']) ||
            !isset($this->arguments['blockId']) ||
            !isset($this->arguments['tag'])
        ) {
            throw new \Exception('Missing argument');
        }

        $page = filter_var((int)$this->arguments['page'], FILTER_VALIDATE_INT);
        $position = filter_var((int)$this->arguments['position'], FILTER_VALIDATE_INT);
        $blockId = filter_var((string)$this->arguments['blockId'], FILTER_SANITIZE_STRING);
        $tag = filter_var((string)$this->arguments['tag'], FILTER_SANITIZE_STRING);
        if (empty($tag) || empty($blockId) || $page === false || $position === false) {
            throw new \InvalidArgumentException('Invalid Parameters');
        }

        $cbObj = new CentreonConfigCentreonBroker($this->pearDB);

        $form = $cbObj->quickFormById($blockId, $page, $position, "new_" . rand(100, 1000));

        $helps = array();
        list($tagId, $typeId) = explode('_', $blockId);
        $typeName = $cbObj->getTypeName($typeId);
        $fields = $cbObj->getBlockInfos($typeId);
        $helps[] = array('name' => $tag . '[' . $position . '][name]', 'desc' => _('The name of block configuration'));
        $helps[] = array('name' => $tag . '[' . $position . '][type]', 'desc' => _('The type of block configuration'));
        $cbObj->nbSubGroup = 1;
        textdomain('help');
        foreach ($fields as $field) {
            $fieldname = '';
            if ($field['group'] !== '') {
                $fieldname .= $cbObj->getParentGroups($field['group']);
            }
            $fieldname .= $field['fieldname'];
            $helps[] = array(
                'name' => $tag . '[' . $position . '][' . $fieldname . ']',
                'desc' => _($field['description'])
            );
        }
        textdomain('messages');

        /*
         * Smarty template Init
         */
        $libDir = __DIR__ . "/../../../GPL_LIB";
        $smartyDir = __DIR__ . '/../../../vendor/smarty/smarty/';
        require_once $smartyDir . 'libs/SmartyBC.class.php';
        $tpl = new \SmartyBC();
        $tpl->setTemplateDir(_CENTREON_PATH_ . '/www/include/configuration/configCentreonBroker/');
        $tpl->setCompileDir($libDir . '/SmartyCache/compile');
        $tpl->setConfigDir($libDir . '/SmartyCache/config');
        $tpl->setCacheDir($libDir . '/SmartyCache/cache');
        $tpl->addPluginsDir($libDir . '/smarty-plugins');
        $tpl->loadPlugin('smarty_function_eval');
        $tpl->setForceCompile(true);
        $tpl->setAutoLiteral(false);

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
        $tpl->assign('posBlock', $position);
        $tpl->assign('helps', $helps);

        return $tpl->fetch("blockConfig.ihtml");
    }
}
