<?php

namespace Centreon\Infrastructure\CentreonLegacyDB\Interfaces;

interface PaginationRepositoryInterface
{

    /**
     * Get a list of elements by criteria
     * 
     * @param mixed $filters
     * @param int $limit
     * @param int $offset
     * @param array $ordering
     * @return array
     */
    public function getPaginationList($filters = null, int $limit = null, int $offset = null, $ordering = []): array;

    /**
     * Get total count of elements in the list
     *
     * @return int
     */
    public function getPaginationListTotal(): int;
}
