<?php

namespace Centreon\Domain\Entity;

use ReflectionClass;

class Task implements EntityInterface
{
    /**
     * Task types
     */
    final public const TYPE_EXPORT = 'export';
    final public const TYPE_IMPORT = 'import';
    final public const TYPE_VERIFY = 'verify';

    /**
     * Task states
     */
    final public const STATE_PENDING = 'pending';
    final public const STATE_PROGRESS = 'inprogress';
    final public const STATE_COMPLETED = 'completed';
    final public const STATE_FAILED = 'failed';

    /**
     * Task type
     */
    private ?string $type = null;

    /**
     * Autoincrement ID
     */
    private ?int $id = null;

    /**
     * Task status
     */
    private ?string $status = null;

    private \DateTime $createdAt;

    private ?int $parent_id = null;

    private ?\DateTime $completedAt = null;

    /**
     * Parameters to be serialized into DB that define task options
     */
    private ?string $params = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCompletedAt(): \DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTime $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getParams(): string
    {
        return $this->params;
    }

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
        $ref = new ReflectionClass(self::class);
        $constants = $ref->getConstants();
        $statusConstants = $this->arrayFilterKey($constants, fn($key) => str_starts_with($key, 'STATE_'));
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
            return [];
        }

        $input = array_intersect_key(array_flip($filteredKeys), $input);

        return $input;
    }
}
