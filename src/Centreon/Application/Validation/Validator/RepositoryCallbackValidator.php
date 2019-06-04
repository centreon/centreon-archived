<?php

namespace Centreon\Application\Validation\Validator;

use Centreon\Application\Validation\Constraints\RepositoryCallback;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraints\CallbackValidator;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\ServiceProvider;

class RepositoryCallbackValidator extends CallbackValidator
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
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof RepositoryCallback) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\RepositoryCallback');
        }

        $method = $constraint->repoMethod;
        $repo = $this->db->getRepository($constraint->repository);
        $fieldAccessor = $constraint->fieldAccessor;
        $value = $object->$fieldAccessor();
        $field = $constraint->fields;

        if (!\is_callable($constraint->repository, $method)) {
            throw new ConstraintDefinitionException(
                sprintf('%s targeted by Callback constraint is not a valid callable in the repository',
                json_encode($method)));
        } elseif (null !== $object && !$repo->$method($object)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($field)
                    ->setInvalidValue($value)
                    ->setCode(RepositoryCallback::NOT_VALID_REPO_CALLBACK)
                    ->setCause('Not Satisfying method:'.$method)
                    ->addViolation();
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