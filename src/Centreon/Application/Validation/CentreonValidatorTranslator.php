<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

namespace Centreon\Application\Validation;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorInterface as DeprecatedTranslatorInterface;

/**
 * The interface Symfony\Component\Translation\TranslatorInterface will be deprecated
 * since Symfony 4.2 (use Symfony\Contracts\Translation\TranslatorInterface instead)
 * and will be removed on version 5.0
 *
 * @todo remove implementation of Symfony\Component\Translation\TranslatorInterface interface
 * @todo remove transChoice, setLocale, and getLocale methods
 */
class CentreonValidatorTranslator implements TranslatorInterface, DeprecatedTranslatorInterface
{

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $message = gettext($id);

        foreach ($parameters as $key => $val) {
            $message = str_replace($key, $val, $message);
        }

        return $message;
    }

    /**
     * Remove when upgrading to 5.0 version of symfony/validator package
     *
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        // ...
    }

    /**
     * Remove when upgrading to 5.0 version of symfony/validator package
     *
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        // ...
    }

    /**
     * Remove when upgrading to 5.0 version of symfony/validator package
     *
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function getLocale()
    {
        // ...
    }
}
