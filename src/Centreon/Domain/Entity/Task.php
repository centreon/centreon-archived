<?php

namespace Centreon\Domain\Entity;

use ReflectionClass;

class Task implements EntityInterface
{
    /**
     * Task types
     */
    public const TYPE_EXPORT = 'export';
    public const TYPE_IMPORT = 'import';
    public const TYPE_VERIFY = 'verify';

    /**
     * Task states
     */
    public const STATE_PENDING = 'pending';
    public const STATE_PROGRESS = 'inprogress';
    public const STATE_COMPLETED = 'completed';
    public const STATE_FAILED = 'failed';

    /**
     * Task type
     * @var string
     */
    private $type;

    /**
     * Autoincrement ID
     * @var integer
     */
    private $id;

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
     * @var int
     */
    private $parent_id;

    /**
     * @var \DateTime
     */
    private $completedAt;

    /**
     * Parameters to be serialized into DB that define task options
     * @var string
     */
    private $params;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
     * @return string
     */
    public function getParams(): string
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    /**
     * @return int
     */
    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId(?int $parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    /**
     * convert parameters to array
     * @return array<string,string|int>
     */
    public function toArray(): array
    {
        return [
            'params' => $this->getParams(),
            'status' => $this->getStatus(),
            'type' => $this->getType(),
            'parent_id' => $this->getParentId(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * return all statuses
     * @return array<mixed>
     */
    public function getStatuses()
    {
        $ref = new ReflectionClass(__CLASS__);
        $constants = $ref->getConstants();
        $statusConstants = $this->arrayFilterKey($constants, function ($key) {
            return strpos($key, 'STATE_') === 0;
        });
        $statuses = [];
        foreach ($statusConstants as $stKey => $stConstant) {
            $statuses[] = $ref->getConstant($stKey);
        }
        return $statuses;
    }

    /**
     * Filters array keys
     * @param array<mixed> $input
     * @param mixed $callback
     * @return array<mixed>|null
     */
    private function arrayFilterKey($input, $callback)
    {
        if (!is_array($input)) {
            trigger_error('arrayFilterKey() expects parameter 1 to be array, ' . gettype($input) .
                ' given', E_USER_WARNING);
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
