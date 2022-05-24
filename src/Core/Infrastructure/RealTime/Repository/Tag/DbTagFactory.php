<?php

namespace Core\Infrastructure\RealTime\Repository\Tag;

use Core\Domain\RealTime\Model\Tag;

class DbTagFactory
{
    /**
     * Create ServiceCategory model using data from database
     *
     * @param array<string, mixed> $data
     * @return Tag
     */
    public static function createFromRecord(array $data): Tag
    {
        return new Tag((int) $data['id'], $data['name'], $data['type']);
    }
}