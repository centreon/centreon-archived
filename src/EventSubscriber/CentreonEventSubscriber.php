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

use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\VersionHelper;
use JMS\Serializer\Exception\ValidationFailedException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * If no version has been defined in the configuration,
     * this version will be used by default
     */
    const DEFAULT_API_VERSION = "2.0";

    /**
     * If no beta version has been defined in the configuration,
     * this version will be used by default
     */
    const DEFAULT_API_BETA_VERSION = "2.1";

    /**
     * If no API header name has been defined in the configuration,
     * this name will be used by default
     */
    const DEFAULT_API_HEADER_NAME = "version";

    /**
     * @var ContainerInterface
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
     * @return mixed[] The event names to listen to
     */
    public static function getSubscribedEvents(): array
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
     * @param ResponseEvent $event
     */
    public function addApiVersion(ResponseEvent $event): void
    {
        $defaultApiVersion = self::DEFAULT_API_VERSION;
        $defaultApiHeaderName = self::DEFAULT_API_HEADER_NAME;

        if ($this->container->hasParameter('api.version.latest')) {
            $defaultApiVersion = $this->container->getParameter('api.version.latest');
        }
        if ($this->container->hasParameter('api.header')) {
            $defaultApiHeaderName = $this->container->getParameter('api.header');
        }
        $event->getResponse()->headers->add([$defaultApiHeaderName => $defaultApiVersion]);
    }

    /**
     * Initializes the RequestParameters instance for later use in the service or repositories.
     *
     * @param RequestEvent $request
     * @throws \Exception
     */
    public function initRequestParameters(RequestEvent $request):void
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
            if ($json = json_encode($search)) {
                $this->requestParameters->setSearch($json);
            }
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
     * @param RequestEvent $event
     */
    public function defineApiVersionInAttributes(RequestEvent $event): void
    {
        if ($this->container->hasParameter('api.version.latest')) {
            $latestVersion = $this->container->getParameter('api.version.latest');
        } else {
            $latestVersion = self::DEFAULT_API_VERSION;
        }
        $event->getRequest()->attributes->set('version.latest', $latestVersion);
        $event->getRequest()->attributes->set('version.is_latest', false);

        if ($this->container->hasParameter('api.version.beta')) {
            $betaVersion = $this->container->getParameter('api.version.beta');
        } else {
            $betaVersion = self::DEFAULT_API_BETA_VERSION;
        }
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
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
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

        /*
         * If Yes and exception code !== 403 (Forbidden access),
         * we create a custom error message.
         * If we don't do that a HTML error will appeared.
         */
        if ($errorIsBeforeController) {
            if ($event->getException()->getCode() !== 403) {
                $errorCode = $event->getException()->getCode() > 0
                    ? $event->getException()->getCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR;
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $errorCode = $event->getException()->getCode();
                $statusCode = Response::HTTP_FORBIDDEN;
            }

            // Manage exception outside controllers
            $event->setResponse(
                new Response(
                    json_encode([
                        'code' => $errorCode,
                        'message' => $event->getException()->getMessage()
                    ]),
                    $statusCode
                )
            );
        } elseif (!$errorIsBeforeController) {
            $errorCode = $event->getException()->getCode() > 0
                ? $event->getException()->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            if ($event->getException() instanceof EntityNotFoundException) {
                $errorMessage = null;
                $httpCode = Response::HTTP_NOT_FOUND;
            } elseif ($event->getException() instanceof ValidationFailedException) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => EntityValidator::formatErrors(
                        $event->getException()->getConstraintViolationList(),
                        true
                    )
                ]);
                $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } elseif ($event->getException() instanceof \PDOException) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => 'An error has occurred in a repository'
                ]);
            } elseif($event->getException() instanceof AccessDeniedException) {
                $httpCode = $event->getException()->getCode();
                $errorMessage = null;
            } elseif (get_class($event->getException()) == \Exception::class) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => 'Internal error'
                ]);
            } else {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => $event->getException()->getMessage()
                ]);
            }
            $event->setResponse(
                new Response($errorMessage, $httpCode)
            );
        }
    }
}
