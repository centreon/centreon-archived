<?php


namespace Centreon\Application\Controller;

use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Pagination;
use Centreon\Domain\Service\Interfaces\MonitoringServiceInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

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
     *
     * @return View
     */
    public function services(Pagination $pagination): View
    {
        /**
         * @var $user Contact
         */
        $contact = $this->getUser();

        $services = $this
            ->monitoring
            ->findServicesFromContact($contact);

        $statusCode = ($pagination->getTotal() > $pagination->getLimit())
            ? 206
            : 200;
        return $this->view([
            'result' => $services,
            '_meta' => [
                'pagination' => $pagination->toArray()
            ]
        ], $statusCode);
    }
}
