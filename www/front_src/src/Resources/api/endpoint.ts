const resourcesEndpoint = 'monitoring/resources';

const buildParam = (param): string => {
  if (!param) {
    return '';
  }
  return JSON.stringify(param);
};

const buildResourcesEndpoint = ({ state, sort }): string => {
  const stateParam = buildParam([state]);
  const sortParam = buildParam(sort);

  return [`${resourcesEndpoint}?state=${stateParam}`, sortParam]
    .filter((part) => part !== '')
    .join('&');
};

export { buildResourcesEndpoint };
