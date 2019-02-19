<?php
/*
 * Copyright 2005-2019 Centreon
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
 *
 */

namespace Centreon\Domain\Service;

use Centreon\Domain\Entity\ModuleForm;
use Centreon\Domain\Entity\ModuleFormLoaderInterface;

/**
 * This class is used to manage the forms created by the modules.
 * In this way we can create forms that will be used in the Centreon
 * open source code.
 *
 * @package Centreon\Domain\Service
 */
class ModuleFormManager
{
    /**
     * @var array Contains a list of module forms indexed by a context name
     */
    private $moduleForms = [];

    /**
     * @var ModuleFormLoaderInterface Module form loader that will be used to
     * load the modules forms configurations
     */
    private $moduleFormLoader;

    /**
     * @var \HTML_QuickForm HTML_QuickForm instance that will be used to interact
     * with the form
     */
    private $form;

    /**
     * ModuleFormManager constructor.
     * @param ModuleFormLoaderInterface $moduleFormLoader Module form loader
     * that will be used to load the modules forms configurations
     */
    public function __construct(ModuleFormLoaderInterface $moduleFormLoader)
    {
        $this->moduleFormLoader = $moduleFormLoader;
    }

    /**
     * Initialise the form loader.
     *
     * @see ModuleFormLoaderInterface::load()
     */
    public function init() {
        $this->moduleFormLoader->load();
    }

    /**
     * Add a module form that will be used for that given context.
     *
     * @param string $context Name of the context
     * @param ModuleForm $moduleForm Module form to add
     */
    public function addModuleForm(string $context, ModuleForm $moduleForm):void
    {
        if (! array_key_exists($context, $this->moduleForms)) {
            $this->moduleForms[$context] = [];
        }
        $this->moduleForms[$context][] = $moduleForm;
    }

    /**
     * Define an HTML_QuickForm instance that will be defined in all module
     * forms for that given context.
     *
     * @param string $context Name of the context
     * @param \HTML_QuickForm $form HTML_QuickForm instance
     * @return bool Return FALSE if the context does not exist
     * @see ModuleForm::setForm()
     */
    public function setForm(string $context, \HTML_QuickForm $form):bool
    {
        if(! array_key_exists($context, $this->moduleForms)) {
            return false;
        }
        $this->form = $form;
        /**
         * @var $moduleForm ModuleForm
         */
        foreach ($this->moduleForms[$context] as $moduleForm) {
            $moduleForm->setForm($form);
        }
        return true;
    }

    /**
     * Apply the form modifiers in all module form defined for that given context.
     *
     * @param string $context Name of the context containing the module forms
     * and for which we will apply the form modifier
     * @return bool Return FALSE if the context does not exist
     * @see ModuleFormManager::setForm()
     * @see ModuleForm::applyFormModifier()
     * @throws \Exception
     */
    public function applyFormModifiers(string $context):bool
    {
        if(! array_key_exists($context, $this->moduleForms)) {
            return false;
        }
        /**
         * @var $moduleForm ModuleForm
         */
        foreach ($this->moduleForms[$context] as $moduleForm) {
            if ($moduleForm->applyFormModifier() === false) {
                throw new \Exception(
                    'No form modifier defined in the context ' . $context
                );
            }
        }
        return true;
    }

    /**
     * Retrieve the template files list for that given context.
     *
     * @param string $context Name of the context that will be used to retrieve
     * the module form templates file names
     * @return string[] List of template file names
     */
    public function getModulesTemplates(string $context):array
    {
        $templates = [];
        /**
         * @var $module ModuleForm
         */
        foreach ($this->moduleForms[$context] as $module) {
            $templates[] = $module->getTemplate();
        }
        return $templates;
    }

    /**
     * Triggers a specific event in all module forms for that given context.
     *
     * @param string $context Name of the Context in which we will send the
     * event for all module forms
     * @param int $event Event id
     * @param array $args List of arguments that will be sent to all module
     * forms for that given context
     */
    public function trigger(string $context, int $event, ?array $args)
    {
        /**
         * @var $moduleForm ModuleForm
         */
        foreach($this->moduleForms[$context] as $moduleForm) {
            $listeners = $moduleForm->getListeners($event);
            foreach ($listeners as $listener) {
                call_user_func_array($listener->getAction(), array($this->form, $args));
            }
        }
    }
}