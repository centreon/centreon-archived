import { buildListingEndpoint, ListingParameters } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';

export type ListResourcesProps = {
  hostGroups: Array<string>;
  monitoringServers: Array<string>;
  onlyWithPerformanceData?: boolean;
  resourceTypes: Array<string>;
  serviceGroups: Array<number>;
  states: Array<string>;
  statuses: Array<string>;
} & ListingParameters;

const buildResourcesEndpoint = (parameters: ListResourcesProps): string => {
  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    customQueryParameters: [
      { name: 'states', value: parameters.states },
      { name: 'types', value: parameters.resourceTypes },
      { name: 'statuses', value: parameters.statuses },
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
