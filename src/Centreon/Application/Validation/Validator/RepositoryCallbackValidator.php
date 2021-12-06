<?php

/*
 * Copyright 2005-2021 Centreon
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

namespace Centreon\Application\Validation\Validator;

use Centreon\Application\Validation\Constraints\RepositoryCallback;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraints\CallbackValidator;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\ServiceProvider;
use Centreon\Application\Validation\Validator\Interfaces\CentreonValidatorInterface;

class RepositoryCallbackValidator extends CallbackValidator implements CentreonValidatorInterface
{
    /**
     * @var CentreonDBManagerService
     */
    private $db;

    /**
     * Construct
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get(ServiceProvider::CENTREON_DB_MANAGER);
    }

    /**
     * {@inheritdoc}
     * @return void
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof RepositoryCallback) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\RepositoryCallback');
        }

        $method = $constraint->repoMethod;
        $repo = $this->db->getRepository($constraint->repository);
        $fieldAccessor = $constraint->fieldAccessor;
        $value = $object->$fieldAccessor();
        $field = $constraint->fields;

        if (!method_exists($constraint->repository, $method)) {
            throw new ConstraintDefinitionException(sprintf(
                '%s targeted by Callback constraint is not a valid callable in the repository',
                json_encode($method)
            ));
        } elseif (null !== $object && !$repo->$method($object)) {
            $this->context->buildViolation($constraint->message)
                ->atPath($field)
                ->setInvalidValue($value)
                ->setCode(RepositoryCallback::NOT_VALID_REPO_CALLBACK)
                ->setCause('Not Satisfying method:' . $method)
                ->addViolation();
        }
    }

    /**
     * List of required services
     *
     * @return string[]
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_DB_MANAGER,
        ];
    }
}
