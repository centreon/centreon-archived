import { prop, toLower } from 'ramda';

import { CriteriaNames, selectableTypes, selectableStatuses } from '../models';

export interface CriteriaId {
  id: string;
}

export interface CriteriaValueSuggestionsProps {
  criterias: Array<CriteriaId>;
  selectedValues: Array<string>;
}

export const criteriaNameSortOrder = {
  [CriteriaNames.types]: 1,
  [CriteriaNames.statuses]: 2,
};

export interface AutocompleteSuggestionProps {
  cursorPosition: number;
  search: string;
}

const statusNameToQueryLanguageName = selectableStatuses
  .map(prop('id'))
  .reduce((previous, current) => {
    return { ...previous, [current]: toLower(current) };
  }, {});

export const criteriaNameToQueryLanguageName = {
  ...statusNameToQueryLanguageName,
};

const staticCriteriaValuesByName = {
  status: selectableStatuses,
  types: selectableTypes,
};

export const getSelectableCriteriasByName = (
  name: string,
): Array<{ id: string; name: string }> => {
  return staticCriteriaValuesByName[name];
};

export const staticCriteriaNames = Object.keys(staticCriteriaValuesByName);
