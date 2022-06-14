import { buildListingEndpoint, ListingParameters } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';

export type ListResourcesProps = {
  hostCategories: Array<string>;
  hostGroups: Array<string>;
  monitoringServers: Array<string>;
  onlyWithPerformanceData?: boolean;
  resourceTypes: Array<string>;
  serviceCategories: Array<string>;
  serviceGroups: Array<string>;
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
        value: parameters.statusTypes,
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
        value: parameters.onlyWithPerformanceData,
      },
    ],
    parameters,
  });
};

export { buildResourcesEndpoint };
