const hostCriterias = { resourceTypes: [{ id: 'host', name: 'Host' }] };
const servicesCriterias = { resourceTypes: [{ id: 'services', name: 'Host' }] };

const getStatusCriterias = (status) => {
  return { statuses: [status] };
};

const downCriterias = getStatusCriterias({ id: 'DOWN', name: 'Down' });
const unreachableCriterias = getStatusCriterias({
  id: 'UNREACHABLE',
  name: 'Unreachable',
});
const upCriterias = getStatusCriterias({ id: 'UP', name: 'Up' });
const pendingCriterias = getStatusCriterias({ id: 'PENDING' });

const getResourcesUrl = ({ resourceTypeCriterias, statusCriterias = {} }) => {
  const filterQueryParameter = {
    criterias: { ...resourceTypeCriterias, ...statusCriterias },
  };

  return `/monitoring/resources?filter=${JSON.stringify(
    filterQueryParameter,
  )}&fromTopCounter=true`;
};

const getHostResourcesUrl = ({ statusCriterias }) => {
  return getResourcesUrl({
    resourceTypeCriterias: hostCriterias,
    statusCriterias,
  });
};

const getServiceResourcesUrl = ({ statusCriterias }) => {
  return getResourcesUrl({
    resourceTypeCriterias: servicesCriterias,
    statusCriterias,
  });
};
