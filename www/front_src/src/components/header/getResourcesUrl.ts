const hostCriterias = { resourceTypes: [{ id: 'host', name: 'Host' }] };
const serviceCriteria = { resourceTypes: [{ id: 'service', name: 'Host' }] };

interface StatusCriterias {
  statuses: Array<{ id: string; name: string }>;
}

const getStatusCriterias = (status): StatusCriterias => {
  return { statuses: [status] };
};

const downCriterias = getStatusCriterias({ id: 'DOWN', name: 'Down' });
const unreachableCriterias = getStatusCriterias({
  id: 'UNREACHABLE',
  name: 'Unreachable',
});
const upCriterias = getStatusCriterias({ id: 'UP', name: 'Up' });
const pendingCriterias = getStatusCriterias({ id: 'PENDING' });
const criticalCriterias = getStatusCriterias({
  id: 'CRITICAL',
  name: 'Critical',
});
const warningCriterias = getStatusCriterias({ id: 'WARNING', name: 'Warning' });
const unknownCriterias = getStatusCriterias({ id: 'UNKNOWN', name: 'Unknown' });
const okCriterias = getStatusCriterias({ id: 'OK', name: 'Ok' });

const getResourcesUrl = ({
  resourceTypeCriterias,
  statusCriterias = {},
}): string => {
  const filterQueryParameter = {
    criterias: { ...resourceTypeCriterias, ...statusCriterias },
  };

  return `/monitoring/resources?filter=${JSON.stringify(
    filterQueryParameter,
  )}&fromTopCounter=true`;
};

const getHostResourcesUrl = (statusCriterias): string => {
  return getResourcesUrl({
    resourceTypeCriterias: hostCriterias,
    statusCriterias,
  });
};

const getServiceResourcesUrl = (statusCriterias): string => {
  return getResourcesUrl({
    resourceTypeCriterias: serviceCriteria,
    statusCriterias,
  });
};

export {
  downCriterias,
  unreachableCriterias,
  upCriterias,
  pendingCriterias,
  getHostResourcesUrl,
  criticalCriterias,
  unknownCriterias,
  warningCriterias,
  okCriterias,
  getServiceResourcesUrl,
};
