<?php

namespace CentreonModule\Tests\Infrastructure\Source;

use PHPUnit\Framework\TestCase;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Infrastructure\Source\SourceAbstract;

class SourceAbstractTest extends TestCase
{
    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceAbstract::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
    }

    public function testIsEligible()
    {
        $entity = new Module;
        $entity->setName('tesat');
        $entity->setKeywords('test,module,lorem');
        $entity->setInstalled(true);
        $entity->setUpdated(false);

        $this->assertTrue($this->source->isEligible($entity));

        // search
        $this->assertTrue($this->source->isEligible($entity, 'sat'));
        $this->assertTrue($this->source->isEligible($entity, 'lor'));
        $this->assertFalse($this->source->isEligible($entity, 'rom'));

        // installed filter
        $this->assertTrue($this->source->isEligible($entity, null, true));
        $this->assertFalse($this->source->isEligible($entity, null, false));

        // updated filter
        $this->assertFalse($this->source->isEligible($entity, null, null, true));
        $this->assertTrue($this->source->isEligible($entity, null, null, false));
    }
}
