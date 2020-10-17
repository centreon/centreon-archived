import { buildListingEndpoint } from '@centreon/ui';

import { resourcesEndpoint } from '../../api/endpoint';
import { ListResourcesEndpointParams } from '../../models';

const buildResourcesEndpoint = (
  parameters: ListResourcesEndpointParams,
): string => {
  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    parameters,
    customQueryParameters: [
      { name: 'states', value: parameters.states },
      { name: 'types', value: parameters.resourceTypes },
      { name: 'statuses', value: parameters.statuses },
      { name: 'hostgroup_ids', value: parameters.hostGroupIds },
      { name: 'servicegroup_ids', value: parameters.serviceGroupIds },
    ],
  });
};

export { buildResourcesEndpoint };
