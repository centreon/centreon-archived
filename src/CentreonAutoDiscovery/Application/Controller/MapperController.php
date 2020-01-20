<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Application\Controller;

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use CentreonAutoDiscovery\Domain\Mapper\DiscoveredHost;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperInterface;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperServiceInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MapperController extends AbstractFOSRestController
{
    /**
     * @var MapperInterface[]
     */
    private $mappers = [];
    /**
     * @var MapperServiceInterface
     */
    private $mapperService;

    public function addMappers(iterable $mappers): void
    {
        array_push($this->mappers, ...$mappers);
        $this->mapperService->setMappers($this->mappers);
    }

    /**
     * ModifierController constructor.
     * @param MapperServiceInterface $mapperService
     */
    public function __construct (MapperServiceInterface $mapperService)
    {
        $this->mapperService = $mapperService;
    }

    /**
     *  Entry point to get all available mappers
     *
     * IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/mappers",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="autodis.downtime.addHostDowntime")
     * @return View
     * @throws \Exception
     */
    public function getAvailableMappers(): View
    {
        $mappers = [];
        foreach ($this->mappers as $mapper) {
            $mappers[] = [
                'name' => $mapper->getName(),
                'mapping' => $mapper->getMapping()
            ];
        }
        return $this->view($mappers);
    }

    /**
     * @param int $jobId Job id for which we want to retrieve the mappers
     * @return View
     * @throws \Exception
     */
    public function getMappersByJob(int $jobId): View
    {
        $mappersToApply = $this->mapperService->findMappersToApplyByJob($jobId);
        return $this->view($mappersToApply);
    }

    /**
     * @param RequestParametersInterface $requestParameters
     * @param int $jobId
     * @return View
     * @throws \Exception
     */
    public function findDiscoveredHostsByJob(RequestParametersInterface $requestParameters, int $jobId): View
    {
        $discoveredHosts = $this->mapperService->findDiscoveredHostsByJob($jobId);
        return $this->view([
            'result' => $discoveredHosts,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext((new Context())->setGroups(['discovery_host']));
    }

    /**
     * @param RequestParametersInterface $requestParameters
     * @param int $jobId
     * @return View
     * @throws \Exception
     */
    public function simulateMappersOfDiscoveredHostsByJob(RequestParametersInterface $requestParameters, int $jobId): View
    {
        $discoveredHosts = $this->mapperService->findDiscoveredHostsByJob($jobId);
        $modifiedHosts = [];
        if (!empty($discoveredHosts)) {
            $mappersToApply = $this->mapperService->findMappersToApplyByJob($jobId);
            $modifiedHosts = $this->mapperService->applyMapperRulesOnDiscoveredHosts(
                $discoveredHosts,
                $mappersToApply
            );
        } else {
            foreach ($discoveredHosts as $discoveredHost) {
                $modifiedHosts[] = DiscoveredHost::createHostConfiguration($discoveredHost);
            }
        }

        return $this->view([
            'result' => $modifiedHosts,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ]);
    }
}