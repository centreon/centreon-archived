<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Tests\Core\Resources\Infrastructure\Repository\DbReadResourceRepository\FindResources;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\Domain\RealTime\Model\ResourceTypes\ServiceResourceType;
use Core\Resources\Infrastructure\Repository\DbReadResourceRepository;

function generateExpectedSQLQuery(string $searchSubRequest = '', string $sortParameter = ''): string
{
    $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
            resources.resource_id,
            resources.name,
            resources.alias,
            resources.address,
            resources.id,
            resources.internal_id,
            resources.parent_id,
            resources.parent_name,
            parent_resource.status AS `parent_status`,
            parent_resource.alias AS `parent_alias`,
            parent_resource.status_ordered AS `parent_status_ordered`,
            parent_resource.address AS `parent_fqdn`,
            severities.id AS `severity_id`,
            severities.level AS `severity_level`,
            severities.name AS `severity_name`,
            severities.type AS `severity_type`,
            severities.icon_id AS `severity_icon_id`,
            resources.type,
            resources.status,
            resources.status_ordered,
            resources.status_confirmed,
            resources.in_downtime,
            resources.acknowledged,
            resources.passive_checks_enabled,
            resources.active_checks_enabled,
            resources.notifications_enabled,
            resources.last_check,
            resources.last_status_change,
            resources.check_attempts,
            resources.max_check_attempts,
            resources.notes,
            resources.notes_url,
            resources.action_url,
            resources.output,
            resources.poller_id,
            resources.has_graph,
            instances.name AS `monitoring_server_name`,
            resources.enabled,
            resources.icon_id,
            resources.severity_id
        FROM `:dbstg`.`resources`
        LEFT JOIN `:dbstg`.`resources` parent_resource
            ON parent_resource.id = resources.parent_id
        LEFT JOIN `:dbstg`.`severities`
            ON `severities`.severity_id = `resources`.severity_id
        LEFT JOIN `:dbstg`.`resources_tags` AS rtags
            ON `rtags`.resource_id = `resources`.resource_id
        INNER JOIN `:dbstg`.`instances`
            ON `instances`.instance_id = `resources`.poller_id';

    $request .= ! empty($searchSubRequest) ? $searchSubRequest . ' AND ' : ' WHERE ';

    $request .= " resources.name NOT LIKE '\_Module\_%'
            AND resources.parent_name NOT LIKE '\_Module\_BAM%'
            AND resources.enabled = 1 AND resources.type != 3";

    $request .= $sortParameter
        ?: ' ORDER BY resources.status_ordered DESC, resources.name ASC';

    return $request;
}

it('should fetch resources by filter', function () {
    $statement = $this->createMock(\PDOStatement::class);
    $statement->method('fetchColumn')->willReturn(10);

    $dbConnection = $this->createMock(DatabaseConnection::class);
    $dbConnection->expects($this->once())->method('getStorageDbName')->willReturn(':dbstg');
    $dbConnection->expects($this->once())->method('getCentreonDbName')->willReturn(':db');
    $dbConnection->expects($this->once())->method('prepare')->with(generateExpectedSQLQuery())
        ->willReturn($statement);
    $dbConnection->expects($this->once())->method('query')->with('SELECT FOUND_ROWS()')
        ->willReturn($statement);
    $serviceResourceType = $this->createMock(ServiceResourceType::class);
    $requestParams = $this->createMock(RequestParametersInterface::class);
    $requestParams
        ->expects($this->once())
        ->method('setConcordanceStrictMode')
        ->with(RequestParameters::CONCORDANCE_MODE_STRICT)
        ->willReturn($requestParams);
    $requestParams
        ->expects($this->once())
        ->method('setConcordanceErrorMode')
        ->with(RequestParameters::CONCORDANCE_ERRMODE_SILENT)
        ->willReturn($requestParams);
    $paramsTranslator = $this->createMock(SqlRequestParametersTranslator::class);
    $paramsTranslator->method('getRequestParameters')->willReturn($requestParams);
    $repository = new DbReadResourceRepository(
        $dbConnection,
        $paramsTranslator,
        new \ArrayObject([$serviceResourceType])
    );

    $resources = $repository->findResources(new ResourceFilter());

    expect($resources)->toBe([]);
});
