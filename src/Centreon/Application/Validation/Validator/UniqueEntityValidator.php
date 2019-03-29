<?php

namespace Centreon\Application\Validation\Validator;

use Centreon\Application\Validation\Constraints\UniqueEntity;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\ServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

class UniqueEntityValidator extends ConstraintValidator
{

    /**
     * @var CentreonDBManagerService;
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
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueEntity');
        } elseif (!\is_array($constraint->fields) && !\is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        } elseif (null !== $constraint->errorPath && !\is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        //define fields to check
        $fields = (array) $constraint->fields;
        $methodRepository = $constraint->repositoryMethod;
        $methodIdGetter = $constraint->entityIdentificatorMethod;

        if (0 === \count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        } elseif (null === $entity) {
            return;
        }

        $unique = true;

        foreach ($fields as $field) {
            $methodValueGetter = 'get'. ucfirst($field);
            $value = $entity->$methodValueGetter();

            $result = $this->db->getRepository($constraint->repository)
                ->$methodRepository([$field => $value]);

            if ($result && $result->$methodIdGetter() !== $entity->$methodIdGetter()) {

                $this->context->buildViolation($constraint->message)
                    ->atPath($field)
                    ->setInvalidValue($value)
                    ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
                    ->setCause($result)
                    ->addViolation();

                $unique = false;
            }
        }

        if ($unique) {
            return;
        }
    }

    /**
     * List of required services
     *
     * @return array
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_DB_MANAGER,
        ];
    }
}
