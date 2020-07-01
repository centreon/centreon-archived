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

    /**
     * PollerController constructor.
     * @param FilterServiceInterface $filterService
     */
    public function __construct(FilterServiceInterface $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Entry point to save a filter for a user.
     *
     * @param Request $request
     * @return View
     */
    public function addFilter(Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $filterToAdd = json_decode((string) $request->getContent(), true);
        if (!is_array($filterToAdd)) {
            throw new FilterException('Error when decoding your sent data');
        }

        $filter = (new Filter())
            ->setName($filterToAdd['name'])
            ->setPageName($filterToAdd['page_name'])
            ->setCriterias($filterToAdd['criterias']);

        $this->filterService->addFilter($filter);

        return View::create(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Entry point to get filters saved by the user.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     */
    public function getFilters(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $filters = $this->filterService->findFilters();
        $context = (new Context())->setGroups(self::SERIALIZER_GROUPS_MAIN);

        return $this->view([
            'result' => $filters,
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }
}
