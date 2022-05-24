import {
  buildListingEndpoint,
  BuildListingEndpointParameters,
  ListingParameters,
} from '@centreon/ui';

import { baseEndpoint } from '../../../api/endpoint';
import { monitoringEndpoint } from '../../api/endpoint';

const hostCategoriesEndpoint = `${monitoringEndpoint}/hostcategories`;
const serviceCategoriesEndpoint = `${monitoringEndpoint}/servicecategories`;
const hostGroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;
const monitoringServersEndpoint = `${baseEndpoint}/monitoring/servers`;

const buildHostGroupsEndpoint = (parameters: ListingParameters): string => {
  return buildListingEndpoint({
    baseEndpoint: hostGroupsEndpoint,
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

const buildHostCategoriesEndpoint = (
  parameters: BuildListingEndpointParameters,
): string => {
  return buildListingEndpoint({
    baseEndpoint: hostCategoriesEndpoint,
    parameters,
  });
};

const buildServiceCategoriesEndpoint = (
  parameters: BuildListingEndpointParameters,
): string => {
  return buildListingEndpoint({
    baseEndpoint: serviceCategoriesEndpoint,
    parameters,
  });
};

export {
  buildHostCategoriesEndpoint,
  buildServiceCategoriesEndpoint,
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
  buildMonitoringServersEndpoint,
};
