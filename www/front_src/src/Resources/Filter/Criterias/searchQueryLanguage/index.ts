import {
  allPass,
  and,
  any,
  endsWith,
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
  not,
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
  __,
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
const isFilledCriteria = pipe(endsWith(':'), not);

const parse = (search: string): Array<Criteria> => {
  const parts = search.split(' ');

  const [criteriaParts, rawSearchParts] = partition(
    allPass([isCriteriaPart, isFilledCriteria]),
    parts,
  );

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

const getCriteriaNameSuggestions = (word: string): Array<string> => {
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

const getCriteriaValueSuggestions = ({
  selectedValues,
  criterias,
}: CriteriaValueSuggestionsProps): Array<string> => {
  const criteriaIds = map(prop('id'), criterias);

  return without(selectedValues, criteriaIds);
};

const staticCriteriaValuesById = {
  resource_type: selectableResourceTypes,
  state: selectableStates,
  status: selectableStatuses,
};

const getSelectableCriteriasByName = (
  name: string,
): Array<{ id: string; name: string }> => {
  return staticCriteriaValuesById[name];
};

interface AutocompleteSuggestionProps {
  cursorPosition: number;
  search: string;
}

const getAutocompleteSuggestions = ({
  search,
  cursorPosition,
}: AutocompleteSuggestionProps): Array<string> => {
  if (isNil(cursorPosition)) {
    return [];
  }

  const searchUntilCursor = search.slice(0, cursorPosition + 1);
  const lastInputExpression = last(searchUntilCursor.split(' ')) || '';
  const foundCriteria = lastInputExpression.split(':');
  const foundCriteriaName = head(foundCriteria) || '';
  const foundCriteriaValues = last(foundCriteria)?.split(',') || [];

  const hasCriteriaStaticValues = Object.keys(
    staticCriteriaValuesById,
  ).includes(foundCriteriaName);

  if (isEmpty(foundCriteriaName)) {
    return [];
  }

  if (lastInputExpression.includes(':') && hasCriteriaStaticValues) {
    const criterias = getSelectableCriteriasByName(foundCriteriaName);
    const lastFoundCriteriaValue = last(foundCriteriaValues) || '';

    const criteriaValueSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: foundCriteriaValues,
    });

    const isLastValueInSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: [],
    }).includes(lastFoundCriteriaValue);

    return isLastValueInSuggestions
      ? criteriaValueSuggestions.map((v) => `,${v}`)
      : filter(startsWith(lastFoundCriteriaValue), criteriaValueSuggestions);
  }

  return reject(
    includes(__, search),
    getCriteriaNameSuggestions(foundCriteriaName),
  );
};

export { parse, build, getAutocompleteSuggestions };
