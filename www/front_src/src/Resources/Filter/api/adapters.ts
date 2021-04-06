import { propEq, pipe } from 'ramda';

import { CriteriaValue, RawFilter, RawCriteria, Filter } from '../models';
import useFilterModels from '../useFilterModels';

interface Adapters {
  toFilter: (rawFilter: RawFilter) => Filter;
  toFilterWithTranslatedCriterias: (filter: Filter) => Filter;
  toRawFilter: (filter: Filter) => RawFilter;
}

const useAdapters = (): Adapters => {
  const { criteriaValueNameById } = useFilterModels();

  const toFilter = ({ id: filterId, name, criterias }: RawFilter): Filter => {
    const findCriteriaByName = (criteriaName): RawCriteria =>
      criterias.find(propEq('name', criteriaName)) as RawCriteria;

    const toStandardMultiSelectCriteriaValue = (
      criteria,
    ): Array<CriteriaValue> =>
      criteria.value.map(({ id: criteriaId }) => ({
        id: criteriaId,
        name: criteriaValueNameById[criteriaId],
      }));

    const getStandardMultiSelectCriteriaValue = (
      rawName,
    ): Array<CriteriaValue> =>
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

  const toRawFilter = ({ id, name, criterias }: Filter): RawFilter => {
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
          name: 'host_groups',
          object_type: 'host_groups',
          type: 'multi_select',
          value: criterias.hostGroups,
        },
        {
          name: 'service_groups',
          object_type: 'service_groups',
          type: 'multi_select',
          value: criterias.serviceGroups,
        },
        {
          name: 'search',
          type: 'text',
          value: criterias.search || '',
        },
      ],
      id,
      name,
    };
  };

  const toFilterWithTranslatedCriterias = (filter): Filter => {
    return pipe(toRawFilter, toFilter)(filter);
  };

  return { toFilter, toFilterWithTranslatedCriterias, toRawFilter };
};

export default useAdapters;
