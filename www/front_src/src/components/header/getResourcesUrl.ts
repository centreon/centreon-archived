import { CriteriaValue } from '../../Resources/Filter/Criterias/models';

const hostCriterias = {
  name: 'resource_types',
  value: [{ id: 'host', name: 'Host' }],
};
const serviceCriteria = {
  name: 'resource_types',
  value: [{ id: 'service', name: 'Service' }],
};

interface StatusCriterias {
  name: string;
  value: CriteriaValue;
}

const getStatusCriterias = (status): StatusCriterias => {
  return { name: 'statuses', value: [status] };
};

const downCriterias = getStatusCriterias({ id: 'DOWN', name: 'Down' });
const unreachableCriterias = getStatusCriterias({
  id: 'UNREACHABLE',
  name: 'Unreachable',
});
const upCriterias = getStatusCriterias({ id: 'UP', name: 'Up' });
const pendingCriterias = getStatusCriterias({ id: 'PENDING', name: 'Pending' });
const criticalCriterias = getStatusCriterias({
  id: 'CRITICAL',
  name: 'Critical',
});
const warningCriterias = getStatusCriterias({ id: 'WARNING', name: 'Warning' });
const unknownCriterias = getStatusCriterias({ id: 'UNKNOWN', name: 'Unknown' });
const okCriterias = getStatusCriterias({ id: 'OK', name: 'Ok' });

const unhandledStateCriterias = {
  name: 'states',
  value: [{ id: 'unhandled_problems', name: 'Unhandled' }],
};

const getResourcesUrl = ({
  resourceTypeCriterias,
  statusCriterias,
  stateCriterias,
}): string => {
  const filterQueryParameter = {
    criterias: [
      resourceTypeCriterias,
      statusCriterias,
      stateCriterias,
      { name: 'search', value: '' },
    ],
  };

  return `/monitoring/resources?filter=${JSON.stringify(
    filterQueryParameter,
  )}&fromTopCounter=true`;
};

const getHostResourcesUrl = ({
  statusCriterias = { name: 'statuses', value: [] },
  stateCriterias = { name: 'states', value: [] },
} = {}): string => {
  return getResourcesUrl({
    resourceTypeCriterias: hostCriterias,
    stateCriterias,
    statusCriterias,
  });
};

const getServiceResourcesUrl = ({
  statusCriterias = { name: 'statuses', value: [] },
  stateCriterias = { name: 'states', value: [] },
} = {}): string => {
  return getResourcesUrl({
    resourceTypeCriterias: serviceCriteria,
    stateCriterias,
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
  unhandledStateCriterias,
  getServiceResourcesUrl,
};
