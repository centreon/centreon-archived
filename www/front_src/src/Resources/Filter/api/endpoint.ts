import { buildListingEndpoint } from '@centreon/ui';
import { monitoringEndpoint } from '../../api/endpoint';

const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

const buildHostGroupsEndpoint = (params): string => {
  const searchOptions = ['name'];

  return buildListingEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    searchOptions,
    params,
  });
};

const buildServiceGroupsEndpoint = (params): string => {
  const searchOptions = ['name'];

  return buildListingEndpoint({
    baseEndpoint: serviceGroupsEndpoint,
    params,
    searchOptions,
  });
};

export { buildHostGroupsEndpoint, buildServiceGroupsEndpoint };
