import {
  buildListingEndpoint,
  BuildListingEndpointParameters,
} from '@centreon/ui';

import { monitoringEndpoint } from '../../api/endpoint';

const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

const buildHostGroupsEndpoint = (
  parameters: BuildListingEndpointParameters,
): string => {
  return buildListingEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    parameters,
  });
};

const buildServiceGroupsEndpoint = (
  parameters: BuildListingEndpointParameters,
): string => {
  return buildListingEndpoint({
    baseEndpoint: serviceGroupsEndpoint,
    parameters,
  });
};

export { buildHostGroupsEndpoint, buildServiceGroupsEndpoint };
