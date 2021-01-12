import { propEq, pipe } from 'ramda';

import { CriteriaValue, RawFilter, RawCriteria, Filter } from '../models';
import useFilterModels from '../useFilterModels';
import { SortOrder } from '../../Listing/models';

interface Adapters {
  toFilter: (rawFilter: RawFilter) => Filter;
  toRawFilter: (filter: Filter) => RawFilter;
  toFilterWithTranslatedCriterias: (filter: Filter) => Filter;
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
      id: filterId,
      name,
      criterias: {
        resourceTypes: getStandardMultiSelectCriteriaValue('resource_types'),
        states: getStandardMultiSelectCriteriaValue('states'),
        statuses: getStandardMultiSelectCriteriaValue('statuses'),
        hostGroups: findCriteriaByName('host_groups')
          .value as Array<CriteriaValue>,
        serviceGroups: findCriteriaByName('service_groups')
          .value as Array<CriteriaValue>,
        search: findCriteriaByName('search').value as string | undefined,
      },
      sort: findCriteriaByName('sort').value as [string, SortOrder],
    };
  };

  const toRawFilter = ({ id, name, criterias, sort }: Filter): RawFilter => {
    return {
      id,
      name,
      criterias: [
        {
          name: 'resource_types',
          value: criterias.resourceTypes,
          type: 'multi_select',
        },
        {
          name: 'states',
          value: criterias.states,
          type: 'multi_select',
        },
        {
          name: 'statuses',
          value: criterias.statuses,
          type: 'multi_select',
        },
        {
          name: 'host_groups',
          value: criterias.hostGroups,
          type: 'multi_select',
          object_type: 'host_groups',
        },
        {
          name: 'service_groups',
          value: criterias.serviceGroups,
          type: 'multi_select',
          object_type: 'service_groups',
        },
        {
          name: 'search',
          value: criterias.search || '',
          type: 'text',
        },
        {
          name: 'sort',
          value: sort,
          type: 'array',
        },
      ],
    };
  };

  const toFilterWithTranslatedCriterias = (filter): Filter => {
    return pipe(toRawFilter, toFilter)(filter);
  };

  return { toFilter, toRawFilter, toFilterWithTranslatedCriterias };
};

export default useAdapters;
