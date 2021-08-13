import { prop, toLower } from 'ramda';

import { CriteriaNames, selectableStatuses } from '../models';

export interface CriteriaId {
  id: string;
}

export interface CriteriaValueSuggestionsProps {
  criterias: Array<CriteriaId>;
  selectedValues: Array<string>;
}

export const criteriaNameSortOrder = {
  [CriteriaNames.hostGroups]: 4,
  [CriteriaNames.monitoringServers]: 6,
  [CriteriaNames.resourceTypes]: 1,
  [CriteriaNames.serviceGroups]: 5,
  [CriteriaNames.states]: 2,
  [CriteriaNames.statuses]: 3,
};

export const searchableFields = [
  'h.name',
  'h.alias',
  'h.address',
  's.description',
  'name',
  'alias',
  'parent_name',
  'parent_alias',
  'fqdn',
  'information',
];

const statusMapping = selectableStatuses
  .map(prop('id'))
  .reduce((previous, current) => {
    return { ...previous, [current]: toLower(current) };
  }, {});

export const criteriaIdToQueryLanguageId = {
  ...statusMapping,
  resource_type: 'type',
  unhandled_problems: 'unhandled',
};
