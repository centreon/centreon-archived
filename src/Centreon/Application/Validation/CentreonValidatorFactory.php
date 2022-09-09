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

use Centreon\Application\Validation\Validator\Interfaces\CentreonValidatorInterface;
use Pimple\Psr11\ServiceLocator;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Pimple\Container;

class CentreonValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * Construct
     *
     * @param \Pimple\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if (!isset($this->validators[$className])) {
            if (class_exists($className)) {
                // validator as a class with dependencies from centreon
                $reflection = (new ReflectionClass($className));

                if ($reflection->implementsInterface(CentreonValidatorInterface::class)) {
                    $this->validators[$className] = new $className(new ServiceLocator(
                        $this->container,
                        $reflection->hasMethod('dependencies') ? $className::dependencies() : []
                    ));
                } else {
                    // validator as a class with empty property accessor
                    $this->validators[$className] = new $className();
                }
            } elseif (in_array($className, $this->container->keys())) {
                // validator as a service
                $this->validators[$className] = $this->container[$className];
            } else {
                throw new \RuntimeException(sprintf(_('The validator "%s" is not found'), $className));
            }
        }

        return $this->validators[$className];
    }
}
