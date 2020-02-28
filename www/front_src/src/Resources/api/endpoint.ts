import { getSearchParam } from './searchObjects';

const resourcesEndpoint = 'monitoring/resources';

const buildParam = ({ name, value }): string => {
  return `${name}=${JSON.stringify(value)}`;
};

const buildResourcesEndpoint = ({
  state,
  sort,
  page,
  limit,
  search,
}): string => {
  const params = [
    {
      name: 'state',
      value: state,
    },
    { name: 'sort_by', value: sort },
    { name: 'page', value: page },
    { name: 'limit', value: limit },
    { name: 'search', value: getSearchParam(search) },
  ]
    .filter(({ value }) => value !== undefined)
    .map(buildParam)
    .join('&');

  return `${resourcesEndpoint}?${params}`;
};

export { buildResourcesEndpoint };
