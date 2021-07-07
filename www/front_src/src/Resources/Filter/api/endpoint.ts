import {
  buildListingEndpoint,
  BuildListingEndpointParameters,
} from '@centreon/ui';

import { baseEndpoint, monitoringEndpoint } from '../../api/endpoint';

const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;
const monitoringServersEndpoint = `${baseEndpoint}/monitoring/servers`;

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

const buildMonitoringServersEndpoint = (
  parameters: BuildListingEndpointParameters,
): string => {
  return buildListingEndpoint({
    baseEndpoint: monitoringServersEndpoint,
    parameters,
  });
};

export {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
  buildMonitoringServersEndpoint,
};
