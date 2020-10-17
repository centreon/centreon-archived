import { buildListingEndpoint } from '@centreon/ui';
import { Parameters } from '@centreon/ui/src/api/buildListingEndpoint/models';

import { monitoringEndpoint } from '../../api/endpoint';

const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

const buildHostGroupsEndpoint = (parameters: Parameters): string => {
  return buildListingEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    parameters,
  });
};

const buildServiceGroupsEndpoint = (parameters: Parameters): string => {
  return buildListingEndpoint({
    baseEndpoint: serviceGroupsEndpoint,
    parameters,
  });
};

export { buildHostGroupsEndpoint, buildServiceGroupsEndpoint };
