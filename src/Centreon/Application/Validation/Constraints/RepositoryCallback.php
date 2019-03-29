<?php

namespace Centreon\Application\Validation\Constraints;


use Centreon\Application\Validation\Validator\RepositoryCallbackValidator;
use Symfony\Component\Validator\Constraint;

class RepositoryCallback extends Constraint
{
    public $fieldAccessor = null;
    public $repoMethod = null;
    public $repository = null;
    public $fields = [];

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return RepositoryCallbackValidator::class;
    }

    /**
     * @inheritdoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

