<?php

namespace CentreonAutoDiscovery\tests;

use Centreon\Domain\HostConfiguration\Host;
use CentreonAutoDiscovery\Domain\Mapper\MapperEngine;
use CentreonAutoDiscovery\Domain\Mapper\Mapper\AssociationMapper;
use CentreonAutoDiscovery\Domain\Mapper\Mapper\SimpleMapper;
use CentreonAutoDiscovery\Domain\Mapper\MapperRule;
use CentreonAutoDiscovery\Domain\Mapper\MapperService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EngineModifierTest extends KernelTestCase
{
    /**
     * @var MapperService
     */
    private $modifierService;

    public function setUp (): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        //$this->modifierService = $container->get('CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperServiceInterface');
    }

    public function testSimpleMapper()
    {
        $discoveredHost  = json_decode(
            '{"public_dns_name":"ec2-54-154-206-176.eu-west-1.compute.amazonaws.com","name":"centreon-support","key_name":"prd-centreon","tags":[{"value":"prod","key":"Env"},{"value":"Support Kayako, Manager, SLA, Certif, Sondage.","key":"Desc"},{"value":"kayako, manager, certification, limesurvey","key":"Appli"},{"value":"centreon-support","key":"Name"}],"state":"running","private_dns_name":"ip-10-24-1-100.eu-west-1.compute.internal","vpc_id":"vpc-459b4c20","type":"ec2","id":"i-f15afe17","private_ip":"10.24.1.100","instance_type":"t2.small"}',
            true
        );

        //$modifiersToApply = $modifierService->findModifiersToApplyByJob(1);
        /**
         * @var $modifiersToApply MapperRule[]
         */
        $modifiersToApply = [
            (new MapperRule())->setOrder(1)->setName('simple_mapping')->setDetails('{"from" : "discovery.results.key_name", "to": "host.name"}'),
            (new MapperRule())->setOrder(2)->setName('simple_mapping')->setDetails('{"from" : "discovery.results.name", "to": "host.name"}'),
            (new MapperRule())->setOrder(3)->setName('simple_mapping')->setDetails('{"from" : "discovery.results.key_name", "to": "host.alias"}'),
        ];

        $engine = new MapperEngine();

        $modifier = new SimpleMapper();
        $engine->addMapper($modifier);

        $hostToModify = new Host();
        $hostToModify->setName('default name');

        $hostModified = $engine->process($hostToModify, $modifiersToApply, $discoveredHost);
        $this->assertEquals($hostModified->getName(), 'centreon-support');
        $this->assertEquals($hostModified->getAlias(), 'prd-centreon');
    }

    public function testAssociationMapper()
    {
        $discoveredHost  = json_decode(
            '{
            "public_dns_name":"ec2-54-154-206-176.eu-west-1.compute.amazonaws.com",
            "name":"centreon-support",
            "key_name":"prd-centreon",
            "tags":[
                {"value":"prod","key":"Env"},
                {"value":"Support Kayako, Manager, SLA, Certif, Sondage.","key":"Desc"},
                {"value":"kayako, manager, certification, limesurvey","key":"Appli"},
                {"value":"centreon-support","key":"Name"}
            ],
            "state":"running",
            "private_dns_name":"ip-10-24-1-100.eu-west-1.compute.internal",
            "vpc_id":"vpc-459b4c20",
            "type":"ec2",
            "id":"i-f15afe17",
            "private_ip":"10.24.1.100",
            "instance_type":"t2.small"
            }',
            true
        );

        //$modifiersToApply = $modifierService->findModifiersToApplyByJob(1);
        /**
         * @var $mapperRulesToApply MapperRule[]
         */
        $mapperRulesToApply = [
            (new MapperRule())
                ->setOrder(1)
                ->setName('association')
                ->setDetails(
                    '{
                    "source" : "discovery.results.name", 
                    "destination": "host.name", 
                    "conditions": [
                        {"source": "discovery.results.type", "value": "123456", "operator": "equal"},
                        {"source": "discovery.results.state", "value": "running", "operator": "equal"}
                    ]}'),
        ];

        $mapperEngine = new MapperEngine();

        $modifier = new AssociationMapper();
        $mapperEngine->addMapper($modifier);

        $hostToModify = new Host();
        $hostToModify->setName('default name');

        $hostModified = $mapperEngine->process($hostToModify, $mapperRulesToApply, $discoveredHost);
        $this->assertEquals($hostModified->getName(), 'centreon-support');
    }
}
