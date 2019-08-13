<?php
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Pagination\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MonitoringController
 * @package Centreon\Application\Controller
 */
class MonitoringController extends AbstractFOSRestController
{
    /**
     * @var MonitoringServiceInterface
     */
    private $monitoring;

    public function __construct(MonitoringServiceInterface $monitoringService)
    {
        $this->monitoring = $monitoringService;
    }

    /**
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}/services/{serviceId}",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.is_beta') == false")
     *
     * @param int $serviceId
     * @param int $hostId
     * @return View
     */
    public function oneService(int $serviceId, int $hostId): View
    {
        $service = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneService($hostId, $serviceId);

        if ($service !== null) {
            $context = (new Context())
                ->setGroups(['service_full'])
                ->enableMaxDepth();

            return $this->view($service)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Rest\Get(
     *     "/monitoring/services",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function services(RequestParametersInterface $requestParameters): View
    {
        $services = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServices();

        $context = (new Context())
            ->setGroups(['service_main', 'service_with_host', 'host_min'])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $services,
                'meta' => [
                    'pagination' => $requestParameters->toArray()
                ]
            ]
        )->setContext($context);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Rest\Get(
     *     "/monitoring/servicegroups",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function servicesByServiceGroups(RequestParametersInterface $requestParameters): View
    {
        $servicesByServiceGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServiceGroups();

        $context = (new Context())
            ->setGroups(['sg_main', 'host_min', 'host_with_services', 'service_min'])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $servicesByServiceGroups,
                'meta' => [
                    'pagination' => $requestParameters->toArray()
                ]
            ]
        )->setContext($context);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Rest\Get(
     *     "/monitoring/hostgroups",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function servicesByHostGroups(RequestParametersInterface $requestParameters)
    {
        $hostGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHostGroups();

        $context = (new Context())
            ->setGroups(['hg_main', 'host_min', 'host_with_services', 'service_min'])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $hostGroups,
                'meta' => [
                    'pagination' => $requestParameters->toArray()
                ]
            ]
        )->setContext($context);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Rest\Get(
     *     "/monitoring/hosts",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function hosts(RequestParametersInterface $requestParameters)
    {
        $hosts = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHosts();

        $context = (new Context())
            ->setGroups(['host_main', 'service_min']);

        return $this->view(
            [
                'result' => $hosts,
                'meta' => [
                    'pagination' => $requestParameters->toArray()
                ]
            ]
        )->setContext($context);
    }

    /**
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}",
     *     condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param int $hostId
     * @return View
     */
    public function oneHost(int $hostId)
    {
        $host = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneHost($hostId);

        if ($host !== null) {
            $context = (new Context())
                ->setGroups(['host_full', 'service_min'])
                ->enableMaxDepth();

            return $this->view($host)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * @Rest\Get(
     *      "/monitoring/hosts/{hostId}/services",
     *      condition="request.attributes.get('version') >= 1.0 && request.attributes.get('version.not_beta')")
     *
     * @param int $hostId
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function servicesByHost(int $hostId, RequestParametersInterface $requestParameters)
    {
        $this->monitoring->filterByContact($this->getUser());

        if ($this->monitoring->isHostExists($hostId)) {
            $services = $this->monitoring->findServicesByHost($hostId);

            $context = (new Context())
                ->setGroups(['service_main'])
                ->enableMaxDepth();

            return $this->view(
                [
                    'result' => $services,
                    'meta' => [
                        'pagination' => $requestParameters->toArray()
                    ]
                ]
            )->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }
}
