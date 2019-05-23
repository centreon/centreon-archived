<?php

namespace App\EventSubscriber;

use Centreon\Domain\Pagination;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * We defined an event subscriber on the kernel event request to create a
 * Pagination class according to query parameters and then used in the services
 * or repositories.
 *
 * This class is automatically calls by Symfony through the dependency injector
 * and because it's defined as a service.
 *
 * @package App\EventSubscriber
 */
class RequestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Pagination
     */
    private $pagination;

    public function __construct(Pagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['createPagination', 9]
            ]
        ];
    }

    /**
     * Create a Pagination class to use later in service or repositories
     *
     * @param GetResponseEvent $request
     */
    public function createPagination(GetResponseEvent $request):void
    {
        $query = $request->getRequest()->query->all();

        $this->pagination->init($query);
    }
}
