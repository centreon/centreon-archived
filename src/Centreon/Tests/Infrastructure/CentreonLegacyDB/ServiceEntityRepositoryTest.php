<?php

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;
use PDOStatement;

class ServiceEntityRepositoryTest extends TestCase
{

    public function testEntityClass()
    {
        $this->assertEquals(
            'Centreon\\Infrastructure\\CentreonLegacyDB\\ServiceEntity',
            ServiceEntityRepository::entityClass()
        );
    }
}
