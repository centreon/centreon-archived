<?php

namespace Centreon\Application\Validation\Constraints;

use Centreon\Application\Validation\Validator\UniqueEntityValidator;
use Symfony\Component\Validator\Constraint;

class UniqueEntity extends Constraint
{

    const NOT_UNIQUE_ERROR = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077c';
    public $validatorClass = UniqueEntityValidator::class;
    public $message = 'This value is already used.';
    public $entityIdentificatorMethod = 'getId';
    public $entityIdentificatorColumn = 'id';
    public $repository = null;
    public $repositoryMethod = 'findOneBy';
    public $fields = [];
    public $errorPath = null;
    public $ignoreNull = true;


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function getDefaultOption()
    {
        return 'fields';
    }
    /**
     * The validator class name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->validatorClass;
    }

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];
}