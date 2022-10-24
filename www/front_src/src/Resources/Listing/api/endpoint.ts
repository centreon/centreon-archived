import { buildListingEndpoint, ListingParameters } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';

export type ListResourcesProps = {
  hostCategories: Array<string>;
  hostGroups: Array<string>;
  hostSeverities: Array<string>;
  hostSeverityLevels: Array<number>;
  monitoringServers: Array<string>;
  onlyWithPerformanceData?: boolean;
  resourceTypes: Array<string>;
  serviceCategories: Array<string>;
  serviceGroups: Array<string>;
  serviceSeverities: Array<string>;
  serviceSeverityLevels: Array<number>;
  states: Array<string>;
  statusTypes: Array<string>;
  statuses: Array<string>;
} & ListingParameters;

const buildResourcesEndpoint = (parameters: ListResourcesProps): string => {
  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    customQueryParameters: [
      { name: 'states', value: parameters.states },
      {
        name: 'status_types',
        value: parameters.statusTypes
      },
      { name: 'types', value: parameters.resourceTypes },
      { name: 'statuses', value: parameters.statuses },
      { name: 'host_category_names', value: parameters.hostCategories },
      { name: 'service_category_names', value: parameters.serviceCategories },
      { name: 'hostgroup_names', value: parameters.hostGroups },
      { name: 'servicegroup_names', value: parameters.serviceGroups },
      { name: 'monitoring_server_names', value: parameters.monitoringServers },
      {
        name: 'only_with_performance_data',
        value: parameters.onlyWithPerformanceData
      },
      { name: 'service_severity_names', value: parameters.serviceSeverities },
      {
        name: 'service_severity_levels',
        value: parameters.serviceSeverityLevels
      },
      { name: 'host_severity_names', value: parameters.hostSeverities },
      { name: 'host_severity_levels', value: parameters.hostSeverityLevels }
    ],
    parameters
  });
};

export { buildResourcesEndpoint };
