<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
declare(strict_types=1);

namespace Centreon\Domain\Entity;

use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Composite;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\CascadingStrategy;
use Symfony\Component\Validator\Mapping\Loader\YamlFileLoader;
use Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidator
{
    public const ACKNOWLEDGEMENT_VALIDATION_GROUPS_ADD_HOST_ACK = ['add_host_ack'];
    public const ACKNOWLEDGEMENT_VALIDATION_GROUPS_ADD_SERVICE_ACK = ['add_service_ack'];
    public const DOWNTIME_VALIDATION_GROUPS_ADD_DOWNTIME = ['Default'];
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var bool
     */
    private $allowExtraFields;
    /**
     * @var bool
     */
    private $allowMissingFields;

    /**
     * EntityValidator constructor.
     *
     * @param string $validationFilePath Path of the validator configuration file
     */
    public function __construct(string $validationFilePath)
    {
        $validation = Validation::createValidatorBuilder();
        if (is_file($validationFilePath)) {
            $validation->addLoader(
                new YamlFileLoader($validationFilePath)
            );
        } elseif (is_dir($validationFilePath)) {
            $finder = (new Finder())
                ->in($validationFilePath)
                ->filter(function (\SplFileInfo $file) {
                    return $file->getExtension() == 'yaml';
                })
                ->files();
            if ($finder->hasResults()) {
                $paths = [];
                foreach ($finder as $yamlConfigurationFiles) {
                    /**
                     * @var $yamlConfigurationFiles \SplFileInfo
                     */
                    $paths[] = $yamlConfigurationFiles->getRealPath();
                }
                $validation->addLoader(
                    new YamlFilesLoader($paths)
                );
            }
        }

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
     * @param array $groups Rule groups
     * @param bool $allowExtraFields If TRUE, errors will show on not expected fields (by default)
     * @param bool $allowMissingFields If FALSE, errors will show on missing fields (by default)
     * @return ConstraintViolationListInterface
     */
    public function validateEntity(
        string $entityName,
        array $dataToValidate,
        array $groups = ['Default'],
        bool $allowExtraFields = true,
        bool $allowMissingFields = false
    ): ConstraintViolationListInterface {
        if (empty($groups)) {
            $groups[] = Constraint::DEFAULT_GROUP;
        }
        $this->allowExtraFields = $allowExtraFields;
        $this->allowMissingFields = $allowMissingFields;
        $violations = new ConstraintViolationList();
        if ($this->hasValidatorFor($entityName)) {
            $assertCollection = $this->getConstraints($entityName, $groups, true);

            $violations->addAll(
                $this->validator->validate(
                    $dataToValidate,
                    $assertCollection,
                    $groups
                )
            );
        }
        return $this->removeDuplicatedViolation($violations);
    }


    /**
     * @param $object
     * @param Constraint|Constraint[] $constraints
     * @param string|GroupSequence|(string|GroupSequence)[]|null $groups
     * @return ConstraintViolationListInterface
     */
    public function validate($object, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        return $this->validator->validate($object, $constraints, $groups);
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * Gets constraints found in the validation rules.
     *
     * @param string $entityName Entity name for which we want to get constraints
     * @param array $groups NRule groups
     * @param bool $firstCall
     * @return Collection Returns a constraints collection object
     */
    private function getConstraints(string $entityName, array $groups, bool $firstCall = false): Composite
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
            $id = self::convertCamelCaseToSnakeCase($id);

            if (!empty($propertyMetadatas)) {
                $propertyMetadata = $propertyMetadatas[0];
                if ($propertyMetadata->getCascadingStrategy() == CascadingStrategy::CASCADE) {
                    foreach ($propertyMetadata->getConstraints() as $constraint) {
                        if ($constraint instanceof Type) {
                            $constraints[$id] = $this->getConstraints($constraint->type, $groups);
                        } elseif ($constraint instanceof All) {
                            $type = $this->findTypeConstraint($constraint->constraints);
                            if ($type !== null) {
                                $constraints[$id] = new All($this->getConstraints($type, $groups));
                            }
                        }
                    }
                } else {
                    foreach ($groups as $group) {
                        $currentConstraint = $propertyMetadata->findConstraints($group);
                        if (empty($currentConstraint)) {
                            continue;
                        }
                        if (array_key_exists($id, $constraints)) {
                            $constraints[$id] = array_merge(
                                $constraints[$id],
                                $propertyMetadata->findConstraints($group)
                            );
                        } else {
                            $constraints[$id] = $propertyMetadata->findConstraints($group);
                        }
                    }
                }
            }
        }
        if ($firstCall) {
            return new Collection([
                'fields' => $constraints,
                'allowExtraFields' => $this->allowExtraFields,
                'allowMissingFields' => $this->allowMissingFields
            ]);
        } else {
            return new Collection($constraints);
        }
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @return ConstraintViolationListInterface
     */
    private function removeDuplicatedViolation(
        ConstraintViolationListInterface $violations
    ): ConstraintViolationListInterface {
        /**
         * @var $violation ConstraintViolationInterface
         */
        $violationCodes = [];
        $violationNumber = count($violations);
        for ($index = 0; $index < $violationNumber; $index++) {
            $violation = $violations[$index];
            if (!array_key_exists($violation->getPropertyPath(), $violationCodes)
                || (
                    isset($violationCodes[$violation->getPropertyPath()])
                    && !in_array($violation->getCode(), $violationCodes[$violation->getPropertyPath()])
                    )
            ) {
                $violationCodes[$violation->getPropertyPath()][] = $violation->getCode();
            } else {
                $violations->remove($index);
            }
        }
        return $violations;
    }

    /**
     * Find the 'Type' constraint from the constraints list.
     *
     * @param Constraint[] $constraints Constraints list for which we want to find the 'Type' constraint
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
     * Formats errors to be more readable.
     *
     * @param ConstraintViolationListInterface $violations
     * @param bool $showPropertiesInSnakeCase Set TRUE to convert the properties name into snake case
     * @return string List of error messages
     */
    public static function formatErrors(
        ConstraintViolationListInterface $violations,
        bool $showPropertiesInSnakeCase = false
    ): string {
        $errorMessages = '';
        /**
         * @var $violation ConstraintViolationInterface
         */
        foreach ($violations as $violation) {
            if (!empty($errorMessages)) {
                $errorMessages .= "\n";
            }
            $propertyName = $violation->getPropertyPath();
            if ($propertyName[0] == '[' && $propertyName[strlen($propertyName) - 1] == ']') {
                $propertyName = str_replace('][', '.', $propertyName);
                $propertyName = substr($propertyName, 1, -1);
            }
            $errorMessages .= sprintf(
                'Error on \'%s\': %s',
                (($showPropertiesInSnakeCase)
                    ? self::convertCamelCaseToSnakeCase($propertyName)
                    : $violation->getPropertyPath()),
                $violation->getMessage()
            );
        }

        return $errorMessages;
    }

    /**
     * Convert a string from camel case to snake case.
     *
     * @param string $stringToConvert String to convert
     * @return string
     */
    private static function convertCamelCaseToSnakeCase(string $stringToConvert): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($stringToConvert)));
    }
}
