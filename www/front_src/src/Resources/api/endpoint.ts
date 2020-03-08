import isEmpty from 'lodash/isEmpty';

import { getSearchParam, OrSearchParam } from './searchObjects';
import { Filter } from '../Filter/models';

const monitoringEndpoint = 'monitoring';
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

interface ListingParams {
  state?: string;
  sort?: string;
  page?: number;
  limit?: number;
  search?: string;
}

interface Param {
  name: string;
  value?: string | number | OrSearchParam;
}

const buildParam = ({ name, value }): string => {
  return `${name}=${JSON.stringify(value)}`;
};

const buildParams = (params): Array<string> =>
  params
    .filter(({ value }) => value !== undefined && !isEmpty(value))
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
    { name: 'sort_by', value: sort },
    { name: 'page', value: page },
    { name: 'limit', value: limit },
    {
      name: 'search',
      value: getSearchParam({ searchValue: search, searchOptions }),
    },
  ];
};

const buildEndpoint = ({ baseEndpoint, params }): string => {
  return `${baseEndpoint}?${buildParams(params)}`;
};

interface FilterParams {
  states?: Array<Filter>;
  resourceTypes?: Array<Filter>;
  statuses?: Array<Filter>;
}

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
      { name: 'resourceTypes', value: params.resourceTypes },
      { name: 'statuses', value: params.statuses },
      { name: 'host_group_ids', value: params.hostGroupIds },
      { name: 'service_group_ids', value: params.serviceGroupIds },
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
