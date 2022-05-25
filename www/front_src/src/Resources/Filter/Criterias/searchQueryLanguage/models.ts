import { prop, toLower } from 'ramda';

import {
  CriteriaNames,
  selectableResourceTypes,
  selectableStates,
  selectableStateTypes,
  selectableStatuses,
} from '../models';

export interface CriteriaId {
  id: string;
}

export interface CriteriaValueSuggestionsProps {
  criterias: Array<CriteriaId>;
  selectedValues: Array<string>;
}

export const criteriaNameSortOrder = {
  [CriteriaNames.hostGroups]: 5,
  [CriteriaNames.monitoringServers]: 7,
  [CriteriaNames.resourceTypes]: 1,
  [CriteriaNames.serviceGroups]: 6,
  [CriteriaNames.states]: 2,
  [CriteriaNames.statuses]: 3,
  [CriteriaNames.statusTypes]: 4,
  [CriteriaNames.serviceCategory]: 8,
  [CriteriaNames.hostCategory]: 9,
};

export interface AutocompleteSuggestionProps {
  cursorPosition: number;
  search: string;
}

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

const statusNameToQueryLanguageName = selectableStatuses
  .map(prop('id'))
  .reduce((previous, current) => {
    return { ...previous, [current]: toLower(current) };
  }, {});

export const criteriaNameToQueryLanguageName = {
  ...statusNameToQueryLanguageName,
  resource_type: 'type',
  unhandled_problems: 'unhandled',
};

const staticCriteriaValuesByName = {
  resource_type: selectableResourceTypes,
  state: selectableStates,
  status: selectableStatuses,
  status_type: selectableStateTypes,
};

export const dynamicCriteriaValuesByName = [
  CriteriaNames.hostGroups,
  CriteriaNames.monitoringServers,
  CriteriaNames.serviceGroups,
];

export const getSelectableCriteriasByName = (
  name: string,
): Array<{ id: string; name: string }> => {
  return staticCriteriaValuesByName[name];
};

export const staticCriteriaNames = Object.keys(staticCriteriaValuesByName);
