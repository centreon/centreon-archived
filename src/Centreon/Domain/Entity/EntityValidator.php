<?php

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
     * @param string $entityName
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
     * @param string $entityName
     * @param array $dataToValidate
     * @param string $groupName
     * @return ConstraintViolationListInterface
     */
    public function validateEntityByArray(
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
     * @param string $entityName
     * @param string $groupName
     * @return Collection
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
                            $type = $this->getConstraintType($constraint->constraints);
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
     * @param Constraint[] $constraints
     * @return string
     */
    private function getConstraintType(array $constraints): string
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof Type) {
                return $constraint->type;
            }
        }
        return null;
    }

    private function convertCamelCaseToSnakeCase(string $propertyName): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($propertyName)));
    }
}
