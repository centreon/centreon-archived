import { buildListingEndpoint } from '@centreon/ui';
import { resourcesEndpoint } from '../../api/endpoint';

const buildResourcesEndpoint = (params): string => {
  const searchOptions = ['h.name', 'h.alias', 'h.address', 's.description'];

  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    searchOptions,
    params,
    extraParams: [
      { name: 'states', value: params.states },
      { name: 'types', value: params.resourceTypes },
      { name: 'statuses', value: params.statuses },
      { name: 'hostgroup_ids', value: params.hostGroupIds },
      { name: 'servicegroup_ids', value: params.serviceGroupIds },
    ],
  });
};

export { buildResourcesEndpoint };
