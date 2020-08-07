import { buildListingEndpoint } from '@centreon/ui';

const baseEndpoint = './api/beta';
const monitoringEndpoint = `${baseEndpoint}/monitoring`;
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const acknowledgeEndpoint = `${resourcesEndpoint}/acknowledge`;
const downtimeEndpoint = `${resourcesEndpoint}/downtime`;
const checkEndpoint = `${resourcesEndpoint}/check`;
const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;
const userEndpoint =
  './api/internal.php?object=centreon_topcounter&action=user';

const buildResourcesEndpoint = (params): string => {
  const searchOptions = [
    'h.name',
    'h.alias',
    'h.address',
    's.description',
    'information',
  ];

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

export {
  baseEndpoint,
  buildResourcesEndpoint,
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
  userEndpoint,
};
