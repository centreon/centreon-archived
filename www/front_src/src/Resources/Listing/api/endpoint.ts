import { buildListingEndpoint, ListingParameters } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';

export type ListResourcesProps = {
  hostGroupIds: Array<number>;
  monitoringServerIds: Array<number>;
  onlyWithPerformanceData?: boolean;
  resourceTypes: Array<string>;
  serviceGroupIds: Array<number>;
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
      { name: 'hostgroup_ids', value: parameters.hostGroupIds },
      { name: 'servicegroup_ids', value: parameters.serviceGroupIds },
      { name: 'monitoring_server_ids', value: parameters.monitoringServerIds },
      {
        name: 'only_with_performance_data',
        value: parameters.onlyWithPerformanceData,
      },
    ],
    parameters,
  });
};

export { buildResourcesEndpoint };
