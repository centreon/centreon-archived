<?php

namespace Centreon\Domain\Entity;

use ReflectionClass;

class Task implements EntityInterface
{
    /**
     * Task types
     */
    CONST TYPE_EXPORT = 'export';
    CONST TYPE_IMPORT = 'import';
    CONST TYPE_VERIFY = 'verify';

    /**
     * Task states
     */
    CONST STATE_PENDING = 'pending';
    CONST STATE_PROGRESS = 'inprogress';
    CONST STATE_COMPLETED = 'completed';

    /**
     * Task type
     * @var string
     */
    private $type;

    /**
     * Task status
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $completedAt;

    /**
     * Parameters to be serialized into DB that define task options
     * @var array
     */
    private $params;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCompletedAt(): \DateTime
    {
        return $this->completedAt;
    }

    /**
     * @param \DateTime $completedAt
     */
    public function setCompletedAt(\DateTime $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }


    /**
     * convert parameters to array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * return all statuses
     */
    public function getStatuses(): array
    {
        $ref = new ReflectionClass(__CLASS__);
        $constants = $ref->getConstants();
        return self::array_filter_key($constants, function($key){
            return strpos($key,'STATE_') === 0;
        });
    }

    /**
     * Filters array keys
     * @param $input
     * @param $callback
     * @return array|null
     */
    private function array_filter_key($input, $callback)
    {
        if (!is_array($input)) {
            trigger_error('array_filter_key() expects parameter 1 to be array, ' . gettype($input) . ' given', E_USER_WARNING);
            return null;
        }

        if (empty($input)) {
            return $input;
        }

        $filteredKeys = array_filter(array_keys($input), $callback);
        if (empty($filteredKeys)) {
            return array();
        }

        $input = array_intersect_key(array_flip($filteredKeys), $input);

        return $input;
    }
}