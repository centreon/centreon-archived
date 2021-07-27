<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 */
declare(strict_types=1);

namespace EventSubscriber;

use Centreon\Application\ApiPlatform;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\RequestParameters\RequestParametersException;
use Centreon\Domain\VersionHelper;
use JMS\Serializer\Exception\ValidationFailedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * We defined an event subscriber on the kernel event request to create a
 * RequestParameters class according to query parameters and then used in the services
 * or repositories.
 *
 * This class is automatically calls by Symfony through the dependency injector
 * and because it's defined as a service.
 *
 * @package EventSubscriber
 */
class CentreonEventSubscriber implements EventSubscriberInterface
{
    /**
     * If no version has been defined in the configuration,
     * this version will be used by default
     */
    public const DEFAULT_API_VERSION = "21.10";

    /**
     * If no API header name has been defined in the configuration,
     * this name will be used by default
     */
    public const DEFAULT_API_HEADER_NAME = "version";

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestParametersInterface
     */
    private $requestParameters;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var ApiPlatform
     */
    private $apiPlatform;
    /**
     * @var ContactInterface
     */
    private $contact;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestParametersInterface $requestParameters
     * @param ContainerInterface $container
     * @param Security $security
     * @param ApiPlatform $apiPlatform
     * @param ContactInterface $contact
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestParametersInterface $requestParameters,
        ContainerInterface $container,
        Security $security,
        ApiPlatform $apiPlatform,
        ContactInterface $contact,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->requestParameters = $requestParameters;
        $this->security = $security;
        $this->apiPlatform = $apiPlatform;
        $this->contact = $contact;
        $this->logger = $logger;
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
                ['defineApiVersionInAttributes', 33],
                ['initUser', 7],
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
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
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
    public function initRequestParameters(RequestEvent $request): void
    {
        $query = $request->getRequest()->query->all();

        $limit = filter_var(
            $query[RequestParameters::NAME_FOR_LIMIT] ?? RequestParameters::DEFAULT_LIMIT,
            FILTER_VALIDATE_INT
        );
        if (empty($limit)) {
            throw RequestParametersException::integer(RequestParameters::NAME_FOR_LIMIT);
        }
        $this->requestParameters->setLimit((int) $limit);

        $page = filter_var(
            $query[RequestParameters::NAME_FOR_PAGE] ?? RequestParameters::DEFAULT_PAGE,
            FILTER_VALIDATE_INT
        );
        if (empty($page)) {
            throw RequestParametersException::integer(RequestParameters::NAME_FOR_PAGE);
        }
        $this->requestParameters->setPage((int) $page);

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
                if (
                    in_array($parameterName, $reservedFields)
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
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
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

        $event->getRequest()->attributes->set('version.is_beta', false);
        $event->getRequest()->attributes->set('version.not_beta', true);

        $uri = $event->getRequest()->getRequestUri();
        if (preg_match('/\/api\/([^\/]+)/', $uri, $matches)) {
            $requestApiVersion = $matches[1];
            if ($requestApiVersion[0] === 'v') {
                $requestApiVersion = substr($requestApiVersion, 1);
                $requestApiVersion = VersionHelper::regularizeDepthVersion(
                    $requestApiVersion,
                    1
                );
            }

            if (
                $requestApiVersion === 'latest'
                || VersionHelper::compare($requestApiVersion, $latestVersion, VersionHelper::EQUAL)
            ) {
                $event->getRequest()->attributes->set('version.is_latest', true);
                $requestApiVersion = $latestVersion;
            }
            if ($requestApiVersion === 'beta') {
                $event->getRequest()->attributes->set('version.is_beta', true);
                $event->getRequest()->attributes->set('version.not_beta', false);
            }

            /**
             * Used for the routing conditions.
             * @todo We need to use an other name because after routing,
             *       its value is overwritten by the value of the 'version' property from uri
             */
            $event->getRequest()->attributes->set('version', (float) $requestApiVersion);

            // Used for controllers
            $event->getRequest()->attributes->set('version_number', (float) $requestApiVersion);
            $this->apiPlatform->setVersion((float) $requestApiVersion);
        }
    }

    /**
     * Used to manage exceptions outside controllers.
     *
     * @param ExceptionEvent $event
     * @throws \InvalidArgumentException
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $flagController = 'Controller';
        $errorIsBeforeController = true;

        // We detect if the exception occurred before the kernel called the controller
        foreach ($event->getThrowable()->getTrace() as $trace) {
            if (
                array_key_exists('class', $trace)
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
            if ($event->getThrowable()->getCode() !== 403) {
                $errorCode = $event->getThrowable()->getCode() > 0
                    ? $event->getThrowable()->getCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR;
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $errorCode = $event->getThrowable()->getCode();
                $statusCode = Response::HTTP_FORBIDDEN;
            }
            $this->logException($event->getThrowable());
            // Manage exception outside controllers
            $event->setResponse(
                new Response(
                    json_encode([
                        'code' => $errorCode,
                        'message' => $event->getThrowable()->getMessage()
                    ]),
                    $statusCode
                )
            );
        } else {
            $errorCode = $event->getThrowable()->getCode() > 0
                ? $event->getThrowable()->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $httpCode = ($event->getThrowable()->getCode() >= 100 && $event->getThrowable()->getCode() < 600)
                ? $event->getThrowable()->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            if ($event->getThrowable() instanceof EntityNotFoundException) {
                $errorMessage = json_encode([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $event->getThrowable()->getMessage()
                ]);
                $httpCode = Response::HTTP_NOT_FOUND;
            } elseif ($event->getThrowable() instanceof ValidationFailedException) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => EntityValidator::formatErrors(
                        $event->getThrowable()->getConstraintViolationList(),
                        true
                    )
                ]);
            } elseif ($event->getThrowable() instanceof \PDOException) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => 'An error has occurred in a repository'
                ]);
            } elseif ($event->getThrowable() instanceof AccessDeniedException) {
                $errorMessage = null;
            } elseif (get_class($event->getThrowable()) == \Exception::class) {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => 'Internal error'
                ]);
            } else {
                $errorMessage = json_encode([
                    'code' => $errorCode,
                    'message' => $event->getThrowable()->getMessage()
                ]);
            }
            $this->logException($event->getThrowable());
            $event->setResponse(
                new Response($errorMessage, $httpCode)
            );
        }
    }

    /**
     * Used to log the message according to the code and type of exception.
     *
     * @param \Throwable $exception
     */
    private function logException(\Throwable $exception): void
    {
        if (!$exception instanceof HttpExceptionInterface || $exception->getCode() >= 500) {
            $this->logger->critical($exception->getMessage(), ['context' => $exception]);
        } else {
            $this->logger->error($exception->getMessage(), ['context' => $exception]);
        }
    }

    /**
     * Set contact if he is logged in
     */
    public function initUser(): void
    {
        if ($user = $this->security->getUser()) {
            /**
             * @var Contact $user
             */
            EntityCreator::setContact($user);
            /**
             * @var ContactInterface $user
             */
            $this->initLanguage($user);
            $this->initGlobalContact($user);
        }
    }

    /**
     * Init language to manage translation
     *
     * @param ContactInterface $user
     * @return void
     */
    private function initLanguage(ContactInterface $user): void
    {
        $locale = $user->getLocale() ?? $this->getBrowserLocale();
        $lang = $locale . '.' . Contact::DEFAULT_CHARSET;

        putenv('LANG=' . $lang);
        setlocale(LC_ALL, $lang);
        bindtextdomain('messages', $this->container->getParameter('translation_path'));
        bind_textdomain_codeset('messages', Contact::DEFAULT_CHARSET);
        textdomain('messages');
    }

    /**
     * Get browser locale if set in http header
     *
     * @return string The browser locale
     */
    private function getBrowserLocale(): string
    {
        $locale = Contact::DEFAULT_LOCALE;

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        return $locale;
    }

    /**
     * Initialize the contact for the global context.
     *
     * @param ContactInterface $user Local contact with information to be used
     */
    private function initGlobalContact(ContactInterface $user): void
    {
        /**
         * @var Contact $globalContact
         */
        $globalContact = $this->contact;
        $globalContact->setId($user->getId())
            ->setName($user->getName())
            ->setAlias($user->getAlias())
            ->setEmail($user->getEmail())
            ->setTemplateId($user->getTemplateId())
            ->setIsActive($user->isActive())
            ->setAdmin($user->isAdmin())
            ->setTimezone($user->getTimezone())
            ->setLocale($user->getLocale());

        foreach ($user->getRoles() as $role) {
            if (substr($role, 0, 8) === 'ROLE_API') {
                $globalContact->addRole($role);
            } else {
                $globalContact->addTopologyRule($role);
            }
        }
    }
}
