<?php

namespace Centreon\Domain\Entity;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
            /**
             * @var $metadata Symfony\Component\Validator\Constraint\MetadataInterface
             */
            $metadata = $this->validator->getMetadataFor($entityName);
            $constraints = [];
            foreach ($metadata->getConstrainedProperties() as $id) {
                $propertyMetadata = $metadata->getPropertyMetadata($id);
                if (!empty($propertyMetadata)) {
                    $constraints[$id] = $propertyMetadata[0]->findConstraints($groupName);
                }
            }
            $assertCollection = new Collection($constraints);
            $violations->addAll(
                $this->validator->validate($dataToValidate, $assertCollection)
            );
        }
        return $violations;
    }

    /**
     * Validate an entity.
     *
     * @param object $entity Entity to validate
     * @return ConstraintViolationListInterface
     */
    public function validateEntity($entity): ConstraintViolationListInterface
    {
        if (is_object($entity)) {
            return $this->validator->validate($entity);
        }
        return new ConstraintViolationList();
    }
}
