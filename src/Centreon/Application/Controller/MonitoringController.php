<?php


namespace Centreon\Application\Controller;

use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Service\Interfaces\MonitoringServiceInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
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
     * The pagination data had been created by a custom event listener.
     * See the App\Service\MonitoringService constructor to see how to inject it
     *
     * @IsGranted("ROLE_USER")
     *
     * @Rest\Get("/monitoring/service")
     * @return View
     */
    public function services()
    {
        /**
         * @var $user Contact
         */
        $contact = $this->getUser();
        /**
         * @var $services Host[]
         */
        $services = $this
            ->monitoring
            ->findServicesFromContact($contact);
        $context = (new Context())
            ->setGroups(['Default', 'realtime_services'])
            ->setVersion('1.0.0');
        return $this
            ->view($services, Response::HTTP_OK)
            ->setContext($context);
    }
}
