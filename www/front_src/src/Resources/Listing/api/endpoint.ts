import { buildListingEndpoint, ListingParameters } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';

export type ListResourcesProps = {
  states: Array<string>;
  resourceTypes: Array<string>;
  statuses: Array<string>;
  hostGroupIds: Array<number>;
  serviceGroupIds: Array<number>;
  monitoringServerIds: Array<number>;
  onlyWithPerformanceData?: boolean;
} & ListingParameters;

const buildResourcesEndpoint = (parameters: ListResourcesProps): string => {
  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    parameters,
    customQueryParameters: [
      { name: 'states', value: parameters.states },
      { name: 'types', value: parameters.resourceTypes },
      { name: 'statuses', value: parameters.statuses },
      { name: 'hostgroup_ids', value: parameters.hostGroupIds },
      { name: 'servicegroup_ids', value: parameters.serviceGroupIds },
      { name: 'monitoring_server_ids', value: parameters.monitoringServerIds },
      {
        name: 'only_with_performance_data',
        value: parameters.onlyWithPerformanceData,
      },
    ],
  });
};

export { buildResourcesEndpoint };
