<?php
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Pagination\Pagination;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

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
     * @Rest\Get("/monitoring/services/{serviceId}")
     * @param int $serviceId
     * @return View
     */
    public function oneService(int $serviceId): View
    {
        $service = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneService($serviceId);

        $context = (new Context())->setGroups(['Default','service_full']);

        return $this->view($service)->setContext($context);
    }

    /**
     * The pagination data had been created by a custom event listener.
     * See the App\Service\MonitoringService constructor to see how to inject it
     *
     * @IsGranted("ROLE_USER")
     * @Rest\Get("/monitoring/services")
     * @param Pagination $pagination
     * @return View
     */
    public function services(Pagination $pagination): View
    {
        /**
         * @var $user Contact
         */
        $contact = $this->getUser();

        $services = $this->monitoring
            ->filterByContact($contact)
            ->findServices($pagination);

        $context = (new Context())
            ->setGroups(['Default', 'service_main'])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $services,
                'meta' => [
                    'pagination' => $pagination->toArray()
                ]
            ],
            $this->getStatusCode($pagination)
        )->setContext($context);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Rest\Get("/monitoring/hosts")
     * @param Pagination $pagination
     * @return View
     */
    public function hosts(Pagination $pagination)
    {
        /**
         * @var $user Contact
         */
        $contact = $this->getUser();

        $hosts = $this->monitoring
            ->filterByContact($contact)
            ->findHosts($pagination);

        $context = (new Context())
            ->setGroups(['host_main', 'Default']);

        return $this->view(
            [
                'result' => $hosts,
                'meta' => [
                    'pagination' => $pagination->toArray()
                ]
            ],
            $this->getStatusCode($pagination)
        )->setContext($context);
    }

    /**
     * Returns the correct HTTP code based on the pagination result.
     *
     * @param Pagination $pagination
     * @return int
     */
    private function getStatusCode(Pagination $pagination): int
    {
        return ($pagination->getTotal() > $pagination->getLimit())
            ? Response::HTTP_PARTIAL_CONTENT
            : Response::HTTP_OK;
    }
}
