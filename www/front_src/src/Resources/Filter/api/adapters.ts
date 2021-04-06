import { propEq, pipe } from 'ramda';

import {
  criteriaValueNameById,
  CriteriaValue,
  RawFilter,
  RawCriteria,
  Filter,
} from '../models';

const toFilter = ({ id: filterId, name, criterias }: RawFilter): Filter => {
  const findCriteriaByName = (criteriaName): RawCriteria =>
    criterias.find(propEq('name', criteriaName)) as RawCriteria;

  const toStandardMultiSelectCriteriaValue = (criteria): Array<CriteriaValue> =>
    criteria.value.map(({ id: criteriaId }) => ({
      id: criteriaId,
      name: criteriaValueNameById[criteriaId],
    }));

  const getStandardMultiSelectCriteriaValue = (rawName): Array<CriteriaValue> =>
    pipe(findCriteriaByName, toStandardMultiSelectCriteriaValue)(rawName);

  return {
    criterias: {
      hostGroups: findCriteriaByName('host_groups')
        .value as Array<CriteriaValue>,
      resourceTypes: getStandardMultiSelectCriteriaValue('resource_types'),
      search: findCriteriaByName('search').value as string | undefined,
      serviceGroups: findCriteriaByName('service_groups')
        .value as Array<CriteriaValue>,
      states: getStandardMultiSelectCriteriaValue('states'),
      statuses: getStandardMultiSelectCriteriaValue('statuses'),
    },
    id: filterId,
    name,
  };
};

const toRawFilter = ({ name, criterias }: Filter): Omit<RawFilter, 'id'> => {
  return {
    criterias: [
      {
        name: 'resource_types',
        type: 'multi_select',
        value: criterias.resourceTypes,
      },
      {
        name: 'states',
        type: 'multi_select',
        value: criterias.states,
      },
      {
        name: 'statuses',
        type: 'multi_select',
        value: criterias.statuses,
      },
      {
        name: 'service_groups',
        object_type: 'service_groups',
        type: 'multi_select',
        value: criterias.serviceGroups,
      },
      {
        name: 'host_groups',
        object_type: 'host_groups',
        type: 'multi_select',
        value: criterias.hostGroups,
      },
      {
        name: 'search',
        type: 'text',
        value: criterias.search || '',
      },
    ],
    name,
  };
};

export { toRawFilter, toFilter };
