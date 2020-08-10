import { buildListingEndpoint } from '@centreon/ui';
import { resourcesEndpoint } from '../../api/endpoint';

const buildResourcesEndpoint = (options): string => {
  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    options,
    filters: [
      { name: 'states', value: options.states },
      { name: 'types', value: options.resourceTypes },
      { name: 'statuses', value: options.statuses },
      { name: 'hostgroup_ids', value: options.hostGroupIds },
      { name: 'servicegroup_ids', value: options.serviceGroupIds },
    ],
  });
};

export { buildResourcesEndpoint };
