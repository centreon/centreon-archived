<?php

namespace Tests\Centreon\Domain\Entity;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Host;
use PHPUnit\Framework\TestCase;

class EntityCreatorTest extends TestCase
{
    public function testCreateByArray()
    {
        $now = new \DateTime();

        $data = [
            'host_id' => 1,
            'last_notification' => $now->getTimestamp()
        ];
        $host = EntityCreator::createEntityByArray(
            Host::class,
            $data
        );
        $this->assertEquals(1, $host->getId());
        $this->assertEquals($host->getLastNotification()->getTimestamp(), $now->getTimestamp());
    }
}
