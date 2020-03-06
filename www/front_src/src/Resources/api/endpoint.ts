import { getSearchParam } from './searchObjects';

const monitoringEndpoint = 'monitoring';
const resourcesEndpoint = `${monitoringEndpoint}/resources`;
const hostgroupsEndpoint = `${monitoringEndpoint}/hostgroups`;
const serviceGroupsEndpoint = `${monitoringEndpoint}/servicegroups`;

const buildParam = ({ name, value }): string => {
  return `${name}=${JSON.stringify(value)}`;
};

interface ListingParams {
  state?: string;
  sort?: string;
  page?: number;
  limit?: number;
  search?: string;
}

const buildListingEndpoint = ({
  baseEndpoint,
  state,
  sort,
  page,
  limit,
  search,
  searchOptions,
}: ListingParams & {
  baseEndpoint: string;
  searchOptions: Array<string>;
}): string => {
  const params = [
    {
      name: 'state',
      value: state,
    },
    { name: 'sort_by', value: sort },
    { name: 'page', value: page },
    { name: 'limit', value: limit },
    {
      name: 'search',
      value: getSearchParam({ searchValue: search, searchOptions }),
    },
  ]
    .filter(({ value }) => value !== undefined)
    .map(buildParam)
    .join('&');

  return `${baseEndpoint}?${params}`;
};

const buildResourcesEndpoint = (params: ListingParams): string => {
  const searchOptions = [
    'host.name',
    'host.alias',
    'host.address',
    'service.description',
  ];

  return buildListingEndpoint({
    baseEndpoint: resourcesEndpoint,
    searchOptions,
    ...params,
  });
};

const buildHostGroupsEndpoint = (params: ListingParams): string => {
  const searchOptions = ['name'];

  return buildListingEndpoint({
    baseEndpoint: hostgroupsEndpoint,
    searchOptions,
    ...params,
  });
};

const buildServiceGroupsEndpoint = (params: ListingParams): string => {
  const searchOptions = ['name'];

  return buildListingEndpoint({
    baseEndpoint: serviceGroupsEndpoint,
    searchOptions,
    ...params,
  });
};

export {
  buildResourcesEndpoint,
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
};
