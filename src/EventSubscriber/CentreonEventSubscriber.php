<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace App\EventSubscriber;

use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\VersionHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * We defined an event subscriber on the kernel event request to create a
 * RequestParameters class according to query parameters and then used in the services
 * or repositories.
 *
 * This class is automatically calls by Symfony through the dependency injector
 * and because it's defined as a service.
 *
 * @package App\EventSubscriber
 */
class CentreonEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var RequestParametersInterface
     */
    private $requestParameters;

    /**
     * @param RequestParametersInterface $requestParameters
     * @param ContainerInterface $container
     */
    public function __construct(
        RequestParametersInterface $requestParameters,
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->requestParameters = $requestParameters;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['initRequestParameters', 9],
                ['defineApiVersionInAttributes', 33]
            ],
            KernelEvents::RESPONSE => [
                ['addApiVersion', 10]
            ],
            KernelEvents::EXCEPTION => [
                ['onKernelException', 10]
            ]
        ];
    }

    /**
     * Use to update the api version into all responses
     *
     * @param FilterResponseEvent $event
     */
    public function addApiVersion(FilterResponseEvent $event)
    {
        $apiVersion = '1.0';
        $apiHeaderName = 'version';

        if ($this->container->hasParameter('api.version.lastest')) {
            $apiVersion = $this->container->getParameter('api.version.lastest');
        }
        if ($this->container->hasParameter('api.header')) {
            $apiHeaderName = $this->container->getParameter('api.header');
        }
        $event->getResponse()->headers->add([$apiHeaderName => $apiVersion]);
    }

    /**
     * Initializes the RequestParameters instance for later use in the service or repositories.
     *
     * @param GetResponseEvent $request
     * @throws \Exception
     */
    public function initRequestParameters(GetResponseEvent $request):void
    {
        $query = $request->getRequest()->query->all();

        $limit = (int) ($query[RequestParameters::NAME_FOR_LIMIT] ?? RequestParameters::DEFAULT_LIMIT);
        $this->requestParameters->setLimit($limit);

        $page = (int) ($query[RequestParameters::NAME_FOR_PAGE] ?? RequestParameters::DEFAULT_PAGE);
        $this->requestParameters->setPage($page);

        if (isset($query[RequestParameters::NAME_FOR_SORT])) {
            $this->requestParameters->setSort($query[RequestParameters::NAME_FOR_SORT]);
        }

        if (isset($query[RequestParameters::NAME_FOR_SEARCH])) {
            $this->requestParameters->setSearch($query[RequestParameters::NAME_FOR_SEARCH]);
        } else {
            /*
             * Create search by using parameters in query
             */
            $reservedFields = [
                RequestParameters::NAME_FOR_LIMIT,
                RequestParameters::NAME_FOR_PAGE,
                RequestParameters::NAME_FOR_SEARCH,
                RequestParameters::NAME_FOR_SORT,
                RequestParameters::NAME_FOR_TOTAL];

            $search = [];
            foreach ($query as $parameterName => $parameterValue) {
                if (in_array($parameterName, $reservedFields)
                    || $parameterName !== 'filter'
                    || !is_array($parameterValue)
                ) {
                    continue;
                }
                foreach ($parameterValue as $subParameterName => $subParameterValues) {
                    if (strpos($subParameterValues, '|') !== false) {
                        $subParameterValues = explode('|', urldecode($subParameterValues));
                        foreach ($subParameterValues as $value) {
                            $search[RequestParameters::AGGREGATE_OPERATOR_OR][] = [$subParameterName => $value];
                        }
                    } else {
                        $search[RequestParameters::AGGREGATE_OPERATOR_AND][$subParameterName] =
                            urldecode($subParameterValues);
                    }
                }
            }
            $this->requestParameters->setSearch(json_encode($search));
        }

        /**
         * Add extra parameters
         */
        $reservedFields = [
            RequestParameters::NAME_FOR_LIMIT,
            RequestParameters::NAME_FOR_PAGE,
            RequestParameters::NAME_FOR_SEARCH,
            RequestParameters::NAME_FOR_SORT,
            RequestParameters::NAME_FOR_TOTAL,
            'filter'
        ];

        foreach ($request->getRequest()->query->all() as $parameter => $value) {
            if (!in_array($parameter, $reservedFields)) {
                $this->requestParameters->addExtraParameter(
                    $parameter,
                    $value
                );
            }
        }
    }

    /**
     * We retrieve the API version from url to put it in the attributes to allow
     * the kernel to use it in routing conditions.
     *
     * @param GetResponseEvent $event
     */
    public function defineApiVersionInAttributes(GetResponseEvent $event)
    {
        $latestVersion = $this->container->getParameter('api.version.latest');
        $event->getRequest()->attributes->set('version.latest', $latestVersion);
        $event->getRequest()->attributes->set('version.is_latest', false);

        $betaVersion = $this->container->getParameter('api.version.beta');
        $event->getRequest()->attributes->set('version.beta', $betaVersion);
        $event->getRequest()->attributes->set('version.is_beta', false);
        $event->getRequest()->attributes->set('version.not_beta', true);

        $uri = $event->getRequest()->getRequestUri();
        $paths = explode('/', $uri);
        array_shift($paths);
        if (count($paths) >= 3) {
            $requestApiVersion = $paths[2];
            if ($requestApiVersion[0] == 'v') {
                $requestApiVersion = substr($requestApiVersion, 1);
                $requestApiVersion = VersionHelper::regularizeDepthVersion(
                    $requestApiVersion,
                    1
                );
            }

            if ($requestApiVersion == 'latest'
                || VersionHelper::compare($requestApiVersion, $latestVersion, VersionHelper::EQUAL)
            ) {
                $event->getRequest()->attributes->set('version.is_latest', true);
                $requestApiVersion = $latestVersion;
            }
            if ($requestApiVersion == 'beta'
                || VersionHelper::compare($requestApiVersion, $betaVersion, VersionHelper::EQUAL)
            ) {
                $event->getRequest()->attributes->set('version.is_beta', true);
                $event->getRequest()->attributes->set('version.not_beta', false);
                $requestApiVersion = $betaVersion;
            }

            $event->getRequest()->attributes->set('version', (float) $requestApiVersion);
        }
    }

    /**
     * Used to manage exceptions outside controllers.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $flagController = 'Controller';
        $errorIsBeforeController = true;

        // We detect if the exception occurred before the kernel called the controller
        foreach ($event->getException()->getTrace() as $trace) {
            if (array_key_exists('class', $trace)
                && strlen($trace['class']) > strlen($flagController)
                && substr($trace['class'], -strlen($flagController)) === $flagController
            ) {
                $errorIsBeforeController = false;
                break;
            }
        }

        // If Yes and exception code !== 403, we create a custom error message
        // If we don't do that a HTML error will appeared.
        if ($errorIsBeforeController && $event->getException()->getCode() !== 403) {
            $errorCode = $event->getException()->getCode() > 0
                ? $event->getException()->getCode()
                : 500;

            // Manage exception outside controllers
            $event->setResponse(
                new Response(
                    json_encode(
                        [
                            'code' => $errorCode,
                            'message' => $event->getException()->getMessage()
                        ]
                    ),
                    500
                )
            );
        }
    }
}
