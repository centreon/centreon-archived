<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Filter\Interfaces\FilterServiceInterface;
use Centreon\Domain\Filter\Filter;
use Centreon\Domain\Filter\FilterException;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

/**
 * Used to manage filters of the current user
 *
 * @package Centreon\Application\Controller
 */
class FilterController extends AbstractController
{
    /**
     * @var FilterServiceInterface
     */
    private $filterService;

    public const SERIALIZER_GROUPS_MAIN = ['filter_main'];
    public const SERIALIZER_GROUPS_EXTENDED = ['filter_extended'];

    /**
     * PollerController constructor.
     * @param FilterServiceInterface $filterService
     */
    public function __construct(FilterServiceInterface $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * validate filter data according to json schema
     *
     * @param array $filter sent json
     * @return void
     * @throws Exception
     * @throws FilterException
     */
    private function validateFilterSchema(array $filter): void
    {
        $filterToValidate = Validator::arrayToObjectRecursive($filter);
        $validator = new Validator();
        $centreonPath = $this->getParameter('centreon_path');
        $validator->validate(
            $filterToValidate,
            (object) [
                '$ref' => 'file://' . realpath(
                    $centreonPath . 'config/json_validator/latest/Centreon/AddOrUpdateFilter.json'
                )
            ],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new FilterException($message);
        }
    }

    /**
     * Entry point to save a filter for a user.
     *
     * @param Request $request
     * @param string $pageName
     * @return View
     */
    public function addFilter(Request $request, string $pageName): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        $filterToAdd = json_decode((string) $request->getContent(), true);
        if (!is_array($filterToAdd)) {
            throw new FilterException('Error when decoding your sent data');
        }

        $this->validateFilterSchema($filterToAdd);

        $filter = (new Filter())
            ->setPageName($pageName)
            ->setUserId($user->getId())
            ->setName($filterToAdd['name'])
            ->setCriterias($filterToAdd['criterias']);

        $this->filterService->addFilter($filter);

        return View::create(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Entry point to save a filter for a user.
     *
     * @param Request $request
     * @param string $pageName
     * @param int $filterId
     * @return View
     */
    public function updateFilter(Request $request, string $pageName, int $filterId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        $filterToUpdate = json_decode((string) $request->getContent(), true);
        if (!is_array($filterToUpdate)) {
            throw new FilterException('Error when decoding your sent data');
        }

        $this->validateFilterSchema($filterToUpdate);

        $filter = (new Filter())
            ->setId($filterId)
            ->setPageName($pageName)
            ->setUserId($user->getId())
            ->setName($filterToUpdate['name'])
            ->setCriterias($filterToUpdate['criterias']);

        $this->filterService->updateFilter($filter);

        return View::create(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Entry point to delete a filter for a user.
     *
     * @param Request $request
     * @param string $pageName
     * @param int $filterId
     * @return View
     */
    public function deleteFilter(string $pageName, int $filterId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        $this->filterService->deleteFilterByUserId($user->getId(), $pageName, $filterId);

        return View::create(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Entry point to get filters saved by the user.
     *
     * @param RequestParametersInterface $requestParameters
     * @param string $pageName
     * @return View
     */
    public function getFilters(RequestParametersInterface $requestParameters, string $pageName): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        $filters = $this->filterService->findFiltersByUserId($user->getId(), $pageName);
        $context = (new Context())->setGroups(self::SERIALIZER_GROUPS_MAIN);

        return $this->view([
            'result' => $filters,
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }

    /**
     * Entry point to get filter details by id.
     *
     * @param string $pageName
     * @param int $filterId
     * @return View
     */
    public function getFilter(string $pageName, int $filterId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $user = $this->getUser();

        $filter = $this->filterService->findFilterByUserId($user->getId(), $pageName, $filterId);
        $context = (new Context())->setGroups(self::SERIALIZER_GROUPS_EXTENDED);

        return $this->view($filter)->setContext($context);
    }
}
