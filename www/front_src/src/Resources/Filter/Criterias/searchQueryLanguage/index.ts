import {
  any,
  equals,
  filter,
  find,
  flip,
  head,
  includes,
  isEmpty,
  isNil,
  keys,
  last,
  map,
  partition,
  pipe,
  prop,
  propEq,
  propSatisfies,
  reject,
  sortBy,
  split,
  startsWith,
  without,
} from 'ramda';
import pluralize from 'pluralize';

import { SelectEntry } from '@centreon/ui';

import {
  Criteria,
  CriteriaNames,
  criteriaValueNameById,
  selectableCriterias,
  selectableResourceTypes,
  selectableStates,
  selectableStatuses,
} from '../models';
import getDefaultCriterias from '../default';

const isIn = flip(includes);

const criteriaNameSortOrder = {
  [CriteriaNames.hostGroups]: 4,
  [CriteriaNames.monitoringServers]: 6,
  [CriteriaNames.resourceTypes]: 1,
  [CriteriaNames.serviceGroups]: 5,
  [CriteriaNames.states]: 2,
  [CriteriaNames.statuses]: 3,
};

const criteriaKeys = keys(selectableCriterias) as Array<string>;

const isCriteriaPart = pipe(split(':'), head, pluralize, isIn(criteriaKeys));

const parse = (search: string): Array<Criteria> => {
  const parts = search.split(' ');

  const [criteriaParts, rawSearchParts] = partition(isCriteriaPart, parts);

  const criterias: Array<Criteria> = criteriaParts.map((criteria) => {
    const [key, values] = criteria.split(':');
    const pluralizedKey = pluralize(key);

    const defaultCriteria = find(
      propEq('name', pluralizedKey),
      getDefaultCriterias(),
    );

    const objectType = defaultCriteria?.object_type || null;

    return {
      name: pluralizedKey,
      object_type: objectType,
      type: 'multi_select',
      value: values?.split(',').map((value) => {
        const [resourceId, resourceName] = value.split('|');
        const isStaticCriteria = isNil(objectType);

        const id = isStaticCriteria ? value : parseInt(resourceId, 10);
        const name = isStaticCriteria
          ? criteriaValueNameById[id]
          : resourceName;

        return {
          id,
          name,
        };
      }),
    };
  });

  const criteriasWithSearch = [
    ...criterias,
    {
      name: 'search',
      object_type: null,
      type: 'text',
      value: rawSearchParts.join(' '),
    },
  ];

  const toNames = map(prop('name'));
  const criteriaNames = toNames(criteriasWithSearch);

  const defaultCriterias = reject(
    pipe(({ name }) => name, isIn(criteriaNames)),
    getDefaultCriterias(),
  );

  return sortBy(
    ({ name }) => criteriaNameSortOrder[name],
    reject(propEq('name', 'sort'), [
      ...defaultCriterias,
      ...criteriasWithSearch,
    ]),
  );
};

const build = (criterias: Array<Criteria>): string => {
  const nameEqualsSearch = propEq('name', 'search');
  const nameEqualsSort = propEq('name', 'sort');
  const hasEmptyValue = propSatisfies(pipe(isEmpty), 'value');

  const rejectSearch = reject(nameEqualsSearch);
  const rejectSort = reject(nameEqualsSort);
  const rejectEmpty = reject(hasEmptyValue);

  const search = find(nameEqualsSearch, criterias);
  const regularCriterias = pipe(
    rejectSearch,
    rejectSort,
    rejectEmpty,
  )(criterias);

  const builtCriterias = regularCriterias
    .filter(({ value }) => !isNil(value))
    .map(({ name, value, object_type }): string => {
      const values = value as Array<SelectEntry>;
      const isStaticCriteria = isNil(object_type);

      const formattedValues = isStaticCriteria
        ? values.map(prop('id'))
        : values.map(({ id, name: valueName }) => `${id}|${valueName}`);

      return `${pluralize.singular(name)}:${formattedValues.join(',')}`;
    })
    .join(' ');

  if (isEmpty(builtCriterias.trim())) {
    return search?.value as string;
  }

  return [builtCriterias, search?.value].join(' ');
};

const getAutocompleteSuggestionPrefix = (word: string): Array<string> => {
  const singularizedCriteriaKeys = map(
    pluralize.singular,
    criteriaKeys,
  ) as Array<string>;

  if (isEmpty(word)) {
    return [];
  }

  const found = filter(startsWith(word), singularizedCriteriaKeys);

  return found.map((suggestion) => `${suggestion}:`);
};

interface CriteriaValueSuggestionsProps {
  criterias: Array<{ id: string }>;
  selectedValues: Array<string>;
}

const getCriteriaValuesSuggestion = ({
  selectedValues,
  criterias,
}: CriteriaValueSuggestionsProps): Array<string> => {
  const criteriaIds = map(prop('id'), criterias);

  return without(selectedValues, criteriaIds);
};

const getAutocompleteSuggestionSuffix = ({ word, prefix }): Array<string> => {
  if (
    prefix === `${pluralize.singular(CriteriaNames.states)}:` ||
    map(prop('id'), selectableStates).includes(word)
  ) {
    return pipe(map(prop('id')), reject(equals(word)))(selectableStates);
  }

  return [];
};

const mapping = {
  resource_type: selectableResourceTypes,
  state: selectableStates,
  status: selectableStatuses,
};

const getSelectableCriteriasByName = (
  name: string,
): Array<{ id: string; name: string }> => {
  return mapping[name];
};

const getAutocompleteSuggestions = ({
  search,
  cursorPosition,
}): Array<string> => {
  if (isNil(cursorPosition)) {
    return [];
  }

  const searchUntilCursor = search.slice(0, cursorPosition + 1);

  const lastWord = last(searchUntilCursor.split(' ')) || '';

  const lastCriteria = lastWord.split(':');

  const lastCriteriaName = head(lastCriteria) || '';
  const lastValues = (last(lastCriteria) || '').split(',');

  const lastCriteriaRegular = Object.keys(mapping).includes(lastCriteriaName);

  if (isEmpty(lastCriteriaName)) {
    return [];
  }

  if (lastWord.includes(':') && lastCriteriaRegular) {
    const criteriaValueSuggestions = getCriteriaValuesSuggestion({
      criterias: getSelectableCriteriasByName(lastCriteriaName),
      selectedValues: lastValues,
    });

    const isLastValueKnown = getCriteriaValuesSuggestion({
      criterias: getSelectableCriteriasByName(lastCriteriaName),
      selectedValues: [],
    }).includes(last(lastValues) || '');

    const lastValue = last(lastValues) || '';

    return isLastValueKnown
      ? criteriaValueSuggestions.map((v) => `,${v}`)
      : filter(startsWith(lastValue), criteriaValueSuggestions);
  }

  return reject(
    (v) => search.includes(v),
    getAutocompleteSuggestionPrefix(lastCriteriaName),
  );
};

export {
  parse,
  build,
  getAutocompleteSuggestionPrefix,
  getAutocompleteSuggestionSuffix,
  getAutocompleteSuggestions,
};
