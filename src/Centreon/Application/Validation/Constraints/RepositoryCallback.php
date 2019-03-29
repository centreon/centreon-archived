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
    const NOT_VALID_REPO_CALLBACK = '13bd9dbf-6b9b-41cd-a99e-4844bcf3077z';
    public $message = 'Does not satisfy validation callback. Check Repository.';

    protected static $errorNames = [
        self::NOT_VALID_REPO_CALLBACK => 'NOT_VALID_REPO_CALLBACK',
    ];

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

