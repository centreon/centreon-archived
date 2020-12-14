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

namespace Centreon\Tests\Application\Validation\Validator;

use PHPUnit\Framework\TestCase;
use Centreon\Application\Validation\Validator\RepositoryCallbackValidator;
use Centreon\Application\Validation\Constraints\RepositoryCallback;
use Centreon\ServiceProvider;
use Centreon\Tests\Resource\Dependency;
use Centreon\Tests\Resource\Mock;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;

/**
 * @group Centreon
 * @group DataRepresenter
 */
class RepositoryCallbackValidatorTest extends TestCase
{

    use Dependency\CentreonDbManagerDependencyTrait;

    public function setUp(): void
    {
        $this->container = new Container;
        $this->executionContext = $this->createMock(ExecutionContext::class);

        // dependency
        $this->setUpCentreonDbManager($this->container);

        $this->validator = new RepositoryCallbackValidator(new Psr11Container($this->container));
        $this->validator->initialize($this->executionContext);
    }

    public function testValidateWithDifferentConstraint()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidate()
    {
        $constraint = $this->createMock(RepositoryCallback::class);
        $constraint->repository = Mock\RepositoryMock::class;
        $constraint->repoMethod = 'validateEntity';
        $constraint->fieldAccessor = 'getName';

        $entity = new Mock\EntityMock();
        $entity->setId(1);
        $entity->setName('my name');

        // mock repository
        $repository = $this->createMock($constraint->repository);
        $repository->method($constraint->repoMethod)
            ->will($this->returnCallback(function (Mock\EntityMock $object) use ($entity) {
                // check argument
                $this->assertEquals($entity, $object);

                return false;
            }));

        // register mocked repository in DB manager
        $this->container[ServiceProvider::CENTREON_DB_MANAGER]
            ->addRepositoryMock($constraint->repository, $repository);

        $usedMethods = [];

        // mock execution context object
        $this->executionContext
            ->method('buildViolation')
            ->will($this->returnCallback(
                function ($value) use ($constraint, $entity, &$usedMethods) {
                    $this->assertEquals($constraint->message, $value);
                    $usedMethods['buildViolation'] = true;

                    $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);

                    $violationBuilder
                        ->method('atPath')
                        ->will($this->returnCallback(
                            function ($value) use ($constraint, $violationBuilder, &$usedMethods) {
                                $this->assertEquals($constraint->fields, $value);
                                $usedMethods['atPath'] = true;

                                return $violationBuilder;
                            }
                        ));

                    $violationBuilder
                        ->method('setInvalidValue')
                        ->will($this->returnCallback(
                            function ($value) use ($entity, $violationBuilder, &$usedMethods) {
                                $this->assertEquals($entity->getName(), $value);
                                $usedMethods['setInvalidValue'] = true;

                                return $violationBuilder;
                            }
                        ));

                    $violationBuilder
                        ->method('setCode')
                        ->will($this->returnCallback(
                            function ($value) use ($constraint, $violationBuilder, &$usedMethods) {
                                $this->assertEquals($constraint::NOT_VALID_REPO_CALLBACK, $value);
                                $usedMethods['setCode'] = true;

                                return $violationBuilder;
                            }
                        ));

                    $violationBuilder
                        ->method('setCause')
                        ->will($this->returnCallback(
                            function ($value) use ($violationBuilder, &$usedMethods) {
                                $this->assertNotNull($value);
                                $usedMethods['setCause'] = true;

                                return $violationBuilder;
                            }
                        ));

                    $violationBuilder
                        ->method('addViolation')
                        ->will($this->returnCallback(
                            function () use (&$usedMethods) {
                                $usedMethods['addViolation'] = true;
                            }
                        ));

                    return $violationBuilder;
                }
            ));

        $this->assertNull($this->validator->validate($entity, $constraint));

        // check list of used methods
        $this->assertEquals([
            'buildViolation' => true,
            'atPath' => true,
            'setInvalidValue' => true,
            'setCode' => true,
            'setCause' => true,
            'addViolation' => true,
        ], $usedMethods);
    }

    public function testDependencies()
    {
        $this->assertEquals([
            ServiceProvider::CENTREON_DB_MANAGER,
        ], $this->validator::dependencies());
    }
}
