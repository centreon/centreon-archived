<?php
/**
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
declare(strict_types=1);

namespace Centreon\Domain\Entity;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Composite;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\CascadingStrategy;
use Symfony\Component\Validator\Mapping\Loader\YamlFileLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * EntityValidator constructor.
     *
     * @param string $validationFilePath Path of the validator configuration file
     */
    public function __construct(string $validationFilePath)
    {
        $validation = Validation::createValidatorBuilder();
        $validation->addLoader(
            new YamlFileLoader($validationFilePath)
        );
        $this->validator = $validation->getValidator();
    }

    /**
     * Indicates whether validation rules exist for the name of the given entity.
     *
     * @param string $entityName Entity name
     * @return bool
     */
    public function hasValidatorFor(string $entityName): bool
    {
        return $this->validator->hasMetadataFor($entityName);
    }

    /**
     * We validate a list of parameters according to the path of the given entity.
     * The purpose is to translate the configuration of the validator entity so
     * that it can be used with a list of parameters.
     *
     * @param string $entityName Entity name
     * @param array $dataToValidate Data to validate
     * @param string $groupName Name of the rule group
     * @return ConstraintViolationListInterface
     */
    public function validateEntity(
        string $entityName,
        array $dataToValidate,
        string $groupName = 'Default'
    ): ConstraintViolationListInterface {
        $violations = new ConstraintViolationList();
        if ($this->hasValidatorFor($entityName)) {
            $assertCollection = $this->getConstraints($entityName, $groupName);
            $violations->addAll(
                $this->validator->validate($dataToValidate, $assertCollection)
            );
        }
        return $violations;
    }

    /**
     * Gets contraints found in the validation rules.
     *
     * @param string $entityName Entity name for which we want to get constraints
     * @param string $groupName Name of the rule group
     * @return Collection Returns a contraints collection object
     */
    private function getConstraints(string $entityName, string $groupName, string $type = null): Composite
    {
        /**
         * @var $metadata \Symfony\Component\Validator\Mapping\ClassMetadata
         */
        $metadata = $this->validator->getMetadataFor($entityName);
        $constraints = [];
        foreach ($metadata->getConstrainedProperties() as $id) {
            $propertyMetadatas = $metadata->getPropertyMetadata($id);

            // We need to convert camel case to snake case because the data sent
            // are in snake case format whereas the validation definition file
            // use the real name of properties (camel case)
            $id = $this->convertCamelCaseToSnakeCase($id);

            if (!empty($propertyMetadatas)) {
                $propertyMetadata = $propertyMetadatas[0];
                if ($propertyMetadata->getCascadingStrategy() == CascadingStrategy::CASCADE) {
                    foreach ($propertyMetadata->getConstraints() as $constraint) {
                        if ($constraint instanceof Type) {
                            $constraints[$id] = $this->getConstraints($constraint->type, $groupName);
                        } elseif ($constraint instanceof All) {
                            $type = $this->findTypeConstraint($constraint->constraints);
                            if ($type !== null) {
                                $constraints[$id] = new All($this->getConstraints($type, $groupName));
                            }
                        }
                    }
                } else {
                    $constraints[$id] = $propertyMetadata->findConstraints($groupName);
                }
            }
        }
        return new Collection($constraints);
    }

    /**
     * Find the Type' contraint from the contraints list.
     *
     * @param Constraint[] $constraints Contraints list for which we want to find the 'Type' contraint
     * @return string|null
     */
    private function findTypeConstraint(array $constraints): ?string
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof Type) {
                return $constraint->type;
            }
        }
        return null;
    }

    /**
     * Convert a string from camel case to snake case.
     *
     * @param string $stringToConvert String to convert
     * @return string
     */
    private function convertCamelCaseToSnakeCase(string $stringToConvert): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($stringToConvert)));
    }
}
