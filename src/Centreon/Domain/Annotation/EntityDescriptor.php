<?php

namespace Centreon\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class EntityDescriptor
 * @Annotation
 * @Target({"PROPERTY"})
 */
class EntityDescriptor
{
    /**
     * @var string Name of the column
     */
    public $column;

    /**
     * @var string Name of the setter method
     */
    public $modifier;
}