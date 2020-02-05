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

namespace Centreon\Tests\Application\Validation\Constraints;

use PHPUnit\Framework\TestCase;
use Centreon\Application\Validation\Constraints\UniqueEntity;
use Centreon\Application\Validation\Validator\UniqueEntityValidator;

/**
 * @group Centreon
 * @group DataRepresenter
 */
class UniqueEntityTest extends TestCase
{
    public function testCheckDefaultValueOfProperties()
    {
        $constraint = new UniqueEntity();

        $this->assertObjectHasAttribute('validatorClass', $constraint);
        $this->assertObjectHasAttribute('entityIdentificatorMethod', $constraint);
        $this->assertObjectHasAttribute('entityIdentificatorColumn', $constraint);
        $this->assertObjectHasAttribute('repository', $constraint);
        $this->assertObjectHasAttribute('repositoryMethod', $constraint);
        $this->assertObjectHasAttribute('fields', $constraint);

        $this->assertEquals('getId', $constraint->entityIdentificatorMethod);
        $this->assertEquals('id', $constraint->entityIdentificatorColumn);
        $this->assertNull($constraint->repository);
        $this->assertEquals('findOneBy', $constraint->repositoryMethod);
        $this->assertEquals([], $constraint->fields);
    }

    public function testValidatedBy()
    {
        $this->assertEquals(
            UniqueEntityValidator::class,
            (new UniqueEntity())->validatedBy()
        );
    }

    public function testGetTargets()
    {
        $this->assertEquals(
            UniqueEntity::CLASS_CONSTRAINT,
            (new UniqueEntity())->getTargets()
        );
    }

    public function testGetDefaultOption()
    {
        $this->assertEquals(
            'fields',
            (new UniqueEntity())->getDefaultOption()
        );
    }
}
