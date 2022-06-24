import {
  buildListingEndpoint,
  BuildListingEndpointParameters,
  ListingParameters,
} from '@centreon/ui';

import { baseEndpoint } from '../../../api/endpoint';
import { monitoringEndpoint } from '../../api/endpoint';

const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;
const monitoringServersEndpoint = `${baseEndpoint}/monitoring/servers`;
const hostSeveritiesEndpoint = `${monitoringEndpoint}/hosts/severities`;
const serviceSeveritiesEndpoint = `${monitoringEndpoint}/severities/service`;

const buildHostGroupsEndpoint = (parameters: ListingParameters): string => {
  return buildListingEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    parameters,
  });
};

const buildHostServeritiesEndpoint = (parameters): string => {
  return buildListingEndpoint({
    baseEndpoint: hostSeveritiesEndpoint,
    parameters,
  });
};

const buildServiceSeveritiesEndpoint = (parameters): string => {
  return buildListingEndpoint({
    baseEndpoint: serviceSeveritiesEndpoint,
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
  buildServiceSeveritiesEndpoint,
  buildHostServeritiesEndpoint,
};
