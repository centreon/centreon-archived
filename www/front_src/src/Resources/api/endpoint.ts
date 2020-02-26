const resourcesEndpoint = 'monitoring/resources';

const buildParam = ({ name, value }): string => {
  return `${name}=${JSON.stringify(value)}`;
};

const buildResourcesEndpoint = ({ state, sort, page, limit }): string => {
  const params = [
    {
      name: 'state',
      value: [state],
    },
    { name: 'sort_by', value: sort },
    { name: 'page', value: page },
    { name: 'limit', value: limit },
  ]
    .filter(({ value }) => value !== undefined)
    .map(buildParam)
    .join('&');

  return `${resourcesEndpoint}?${params}`;
};

export { buildResourcesEndpoint };
