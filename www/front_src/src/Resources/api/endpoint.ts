import { getSearchParam, OrSearchParam } from './searchObjects';

const endpoint = './api/beta';
const monitoringEndpoint = `${endpoint}/monitoring`;
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

interface Param {
  name: string;
  value?: string | number | OrSearchParam;
}

const buildParam = ({ name, value }): string => {
  return `${name}=${JSON.stringify(value)}`;
};

const buildParams = (params): Array<string> =>
  params
    .filter(({ value }) => value !== undefined && value.length !== 0)
    .map(buildParam)
    .join('&');

const getListingParams = ({
  sort,
  page,
  limit,
  search,
  searchOptions,
}): Array<Param> => {
  return [
    { name: 'page', value: page },
    { name: 'limit', value: limit },
    { name: 'sort_by', value: sort },
    {
      name: 'search',
      value: getSearchParam({ searchValue: search, searchOptions }),
    },
  ];
};

const buildEndpoint = ({ baseEndpoint, params }): string => {
  return `${baseEndpoint}?${buildParams(params)}`;
};

const buildResourcesEndpoint = (params): string => {
  const searchOptions = [
    'host.name',
    'host.alias',
    'host.address',
    'service.description',
  ];

  const listingParams = getListingParams({ searchOptions, ...params });

  return buildEndpoint({
    baseEndpoint: resourcesEndpoint,
    params: [
      ...listingParams,
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

  const listingParams = getListingParams({ searchOptions, ...params });

  return buildEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    params: listingParams,
  });
};

const buildServiceGroupsEndpoint = (params): string => {
  const searchOptions = ['name'];

  return buildEndpoint({
    baseEndpoint: serviceGroupsEndpoint,
    params: [...getListingParams({ searchOptions, ...params })],
  });
};

export {
  buildResourcesEndpoint,
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
};
