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

namespace Centreon\Domain\Form;

use Centreon\Domain\Entity\Listener;

/**
 * Class ModuleForm
 *
 * @package Centreon\Domain\Form
 */
class ModuleForm
{
    public const EVENT_ADD    = 1;
    public const EVENT_UPDATE = 2;
    public const EVENT_DELETE = 4;
    public const EVENT_READ   = 8;

    /**
     * @var Listener[] List of all listeners defined for this module form.
     */
    private $listeners = [];

    /**
     * @var \HTML_QuickForm HTML_QuickForm instance that will be used in
     * listeners and form modifier.
     */
    private $form;

    /**
     * @var callable Function or method that will be used to interact with the
     * HTML_QuickForm instance.
     */
    private $formModifier;

    /**
     * @var string Template file name
     */
    private $template;

    /**
     * @param \HTML_QuickForm $form HTML_QuickForm instance
     * @see ModuleForm::$form
     */
    public function setForm(\HTML_QuickForm $form): void
    {
        $this->form = $form;
    }

    /**
     * @param callable $formModifier
     * @return ModuleForm
     * @see ModuleForm::$formModifier
     */
    public function setFormModifier(callable $formModifier):self
    {
        $this->formModifier = $formModifier;
        return $this;
    }

    /**
     * @return string Template file name
     * @see ModuleForm::$template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template Template file name
     * @return ModuleForm
     * @see ModuleForm::$template
     */
    public function setTemplate($template):self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Apply the form modifier.
     *
     * @return mixed The function result or FALSE on error
     * @see ModuleForm::$formModifier
     */
    public function applyFormModifier()
    {
        if (empty($this->formModifier)) {
            return false;
        }
        return call_user_func_array($this->formModifier, array(&$this->form));
    }

    /**
     * Add a listener. You can associate multiple events with an action using
     * a simple binary add.
     *
     * @param Listener $listener
     * @return ModuleForm
     * @see ModuleForm::$listeners
     */
    public function addListener(Listener $listener):self
    {
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * Retrieve all listeners for that given event id.
     *
     * @param int|null $event Event id. If null all listeners wil be retrieve.
     * @return Listener[] List of listeners for that given event
     */
    public function getListeners(int $event = null)
    {
        $listeners = [];
        if (! is_null($event)) {
            foreach ($this->listeners as $listener) {
                if ($listener->getEvent() & $event) {
                    $listeners[] = $listener;
                }
            }
            return $listeners;
        } else {
            return $this->listeners;
        }
    }
}
