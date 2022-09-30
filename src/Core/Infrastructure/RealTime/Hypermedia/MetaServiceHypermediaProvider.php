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

namespace Core\Infrastructure\RealTime\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;

class MetaServiceHypermediaProvider extends AbstractHypermediaProvider implements HypermediaProviderInterface
{
    public const ENDPOINT_TIMELINE = 'centreon_application_monitoring_gettimelinebymetaservices',
                 ENDPOINT_TIMELINE_DOWNLOAD = 'centreon_application_monitoring_download_timeline_by_metaservice',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getMetaServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getMetaServiceStatusMetrics',
                 ENDPOINT_METRIC_LIST = 'centreon_application_find_meta_service_metrics',
                 ENDPOINT_DETAILS = 'centreon_application_monitoring_resource_details_meta_service',
                 ENDPOINT_SERVICE_DOWNTIME = 'monitoring.downtime.addMetaServiceDowntime',
                 ENDPOINT_ACKNOWLEDGEMENT = 'centreon_application_acknowledgement_addmetaserviceacknowledgement',
                 ENDPOINT_NOTIFICATION_POLICY = 'configuration.metaservice.notification-policy',
                 URI_CONFIGURATION = '/main.php?p=60204&o=c&meta_id={metaId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}';

    /**
     * @inheritDoc
     */
    public function isValidFor(string $resourceType): bool
    {
        return $resourceType === MetaServiceResourceType::TYPE_NAME;
    }

    /**
     * @inheritDoc
     */
    public function createForConfiguration(array $parameters): ?string
    {
        $roles = [
            Contact::ROLE_CONFIGURATION_SERVICES_WRITE,
            Contact::ROLE_CONFIGURATION_SERVICES_READ
        ];

        if (! $this->canContactAccessPages($this->contact, $roles)) {
            return null;
        }

        return $this->generateUri(self::URI_CONFIGURATION, ['{metaId}' => $parameters['internalId']]);
    }

    /**
     * @inheritDoc
     */
    public function createForReporting(array $parameters): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForEventLog(array $parameters): ?string
    {
        $urlParams = ['{hostId}' => $parameters['hostId'], '{serviceId}' => $parameters['serviceId']];

        return $this->createUrlForEventLog($urlParams);
    }

    /**
     * @param array<string, int> $parameters
     * @return string
     */
    private function generateAcknowledgementEndpoint(array $parameters): string
    {
        $acknowledgementFilter = ['limit' => 1];

        return $this->generateEndpoint(
            self::ENDPOINT_ACKNOWLEDGEMENT,
            array_merge($parameters, $acknowledgementFilter)
        );
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(array $parameters): array
    {
        $urlParams = ['metaId' => $parameters['internalId']];

        return [
            'details' => $this->generateEndpoint(self::ENDPOINT_DETAILS, $urlParams),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($urlParams),
            'downtime' => $this->generateDowntimeEndpoint($urlParams),
            'timeline' => $this->generateEndpoint(self::ENDPOINT_TIMELINE, $urlParams),
            'timeline_download' => $this->generateEndpoint(self::ENDPOINT_TIMELINE_DOWNLOAD, $urlParams),
            'status_graph' => $this->generateEndpoint(self::ENDPOINT_STATUS_GRAPH, $urlParams),
            'metrics' => $this->generateEndpoint(self::ENDPOINT_METRIC_LIST, $urlParams),
            'performance_graph' => $parameters['hasGraphData']
                ? $this->generateEndpoint(self::ENDPOINT_PERFORMANCE_GRAPH, $urlParams)
                : null,
            'notification_policy' => $this->generateEndpoint(self::ENDPOINT_NOTIFICATION_POLICY, $urlParams),
        ];
    }

    /**
     * @inheritDoc
     */
    public function convertGroupsForPresenter(array $groups): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function convertCategoriesForPresenter(array $categories): array
    {
        return [];
    }
}
