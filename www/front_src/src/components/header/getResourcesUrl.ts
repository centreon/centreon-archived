const hostCriterias = { resourceTypes: [{ id: 'host' }] };
const serviceCriteria = { resourceTypes: [{ id: 'service' }] };

interface StatusCriterias {
  statuses: Array<{ id: string; name: string }>;
}

const getStatusCriterias = (status): StatusCriterias => {
  return { statuses: [status] };
};

const downCriterias = getStatusCriterias({ id: 'DOWN' });
const unreachableCriterias = getStatusCriterias({
  id: 'UNREACHABLE',
});
const upCriterias = getStatusCriterias({ id: 'UP' });
const pendingCriterias = getStatusCriterias({ id: 'PENDING' });
const criticalCriterias = getStatusCriterias({
  id: 'CRITICAL',
});
const warningCriterias = getStatusCriterias({ id: 'WARNING' });
const unknownCriterias = getStatusCriterias({ id: 'UNKNOWN' });
const okCriterias = getStatusCriterias({ id: 'OK' });

const unhandledStateCriterias = {
  states: [{ id: 'unhandled_problems' }],
};

const getResourcesUrl = ({
  resourceTypeCriterias,
  statusCriterias,
  stateCriterias,
}): string => {
  const filterQueryParameter = {
    criterias: {
      ...resourceTypeCriterias,
      ...statusCriterias,
      ...stateCriterias,
    },
  };

  return `/monitoring/resources?filter=${JSON.stringify(
    filterQueryParameter,
  )}&fromTopCounter=true`;
};

const getHostResourcesUrl = ({
  statusCriterias = {},
  stateCriterias = {},
} = {}): string => {
  return getResourcesUrl({
    resourceTypeCriterias: hostCriterias,
    statusCriterias,
    stateCriterias,
  });
};

const getServiceResourcesUrl = ({
  statusCriterias = {},
  stateCriterias = {},
} = {}): string => {
  return getResourcesUrl({
    resourceTypeCriterias: serviceCriteria,
    statusCriterias,
    stateCriterias,
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
  unhandledStateCriterias,
  getServiceResourcesUrl,
};
