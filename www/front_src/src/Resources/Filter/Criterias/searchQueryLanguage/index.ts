import {
  allPass,
  concat,
  endsWith,
  filter,
  find,
  flip,
  head,
  includes,
  invertObj,
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
  slice,
  sortBy,
  split,
  startsWith,
  trim,
  without,
  __,
} from 'ramda';
import pluralize from 'pluralize';
import { compose } from 'redux';

import { SelectEntry } from '@centreon/ui';

import {
  Criteria,
  criteriaValueNameById,
  selectableCriterias,
} from '../models';
import getDefaultCriterias from '../default';

import {
  CriteriaId,
  criteriaNameToQueryLanguageName,
  criteriaNameSortOrder,
  CriteriaValueSuggestionsProps,
  searchableFields,
  AutocompleteSuggestionProps,
  staticCriteriaNames,
  getSelectableCriteriasByName,
} from './models';

const singular = pluralize.singular as (string) => string;

const isIn = flip(includes);

const getCriteriaQueryLanguageName = (name: string): string => {
  return criteriaNameToQueryLanguageName[name] || name;
};

const getCriteriaNameFromQueryLanguageName = (name: string): string => {
  return invertObj(criteriaNameToQueryLanguageName)[name] || name;
};

const selectableCriteriaNames = keys(selectableCriterias) as Array<string>;

const isCriteriaPart = pipe(
  split(':'),
  head,
  getCriteriaNameFromQueryLanguageName,
  pluralize,
  isIn(selectableCriteriaNames),
);
const isFilledCriteria = pipe(endsWith(':'), not);

const parse = (search: string): Array<Criteria> => {
  const [criteriaParts, rawSearchParts] = partition(
    allPass([isCriteriaPart, isFilledCriteria]),
    search.split(' '),
  );

  const criterias: Array<Criteria> = criteriaParts.map((criteria) => {
    const [key, values] = criteria.split(':');
    const unmappedCriteriaKey = getCriteriaNameFromQueryLanguageName(key);
    const pluralizedKey = pluralize(unmappedCriteriaKey);

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

        const id = isStaticCriteria
          ? getCriteriaNameFromQueryLanguageName(value)
          : parseInt(resourceId, 10);
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
      value: rawSearchParts.join(' ').trim(),
    },
  ];

  const toNames = map(prop('name'));
  const criteriaNames = toNames(criteriasWithSearch);

  const defaultCriterias = reject(
    compose(isIn(criteriaNames), prop('name')),
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
  const hasEmptyValue = propSatisfies(isEmpty, 'value');

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
    .filter(compose(not, isNil, prop('value')))
    .map(({ name, value, object_type }): string => {
      const values = value as Array<SelectEntry>;
      const isStaticCriteria = isNil(object_type);

      const formattedValues = isStaticCriteria
        ? values.map(compose(getCriteriaQueryLanguageName, prop('id')))
        : values.map(({ id, name: valueName }) => `${id}|${valueName}`);

      const criteriaName = compose(
        getCriteriaQueryLanguageName,
        singular,
      )(name);

      return `${criteriaName}:${formattedValues.join(',')}`;
    })
    .join(' ');

  if (isEmpty(builtCriterias.trim())) {
    return search?.value as string;
  }

  return [builtCriterias, search?.value].join(' ');
};

const getCriteriaNameSuggestions = (word: string): Array<string> => {
  const criteriaNames = map(
    compose(getCriteriaQueryLanguageName, pluralize.singular),
    selectableCriteriaNames,
  );

  if (isEmpty(word)) {
    return [];
  }

  const suggestions = filter(startsWith(word), [
    ...criteriaNames,
    ...searchableFields,
  ]);

  return map(concat(__, ':'), suggestions);
};

const getCriteriaValueSuggestions = ({
  selectedValues,
  criterias,
}: CriteriaValueSuggestionsProps): Array<string> => {
  const criteriaNames = map<CriteriaId, string>(
    compose(getCriteriaQueryLanguageName, prop('id')),
  )(criterias);

  return without(selectedValues, criteriaNames);
};

const getAutocompleteSuggestions = ({
  search,
  cursorPosition,
}: AutocompleteSuggestionProps): Array<string> => {
  const nextCharacter = search[cursorPosition];
  const isNextCharacterEmpty =
    isNil(nextCharacter) || isEmpty(nextCharacter.trim());

  if (isNil(cursorPosition) || !isNextCharacterEmpty) {
    return [];
  }

  const searchBeforeCursor = slice(0, cursorPosition + 1, search);
  const expressionBeforeCursor = pipe(
    trim,
    split(' '),
    last,
  )(searchBeforeCursor) as string;

  const expressionCriteria = expressionBeforeCursor.split(':');
  const criteriaQueryLanguageName = head(expressionCriteria) as string;
  const criteriaName = getCriteriaNameFromQueryLanguageName(
    criteriaQueryLanguageName,
  );
  const expressionCriteriaValues = pipe(last, split(','))(expressionCriteria);

  const hasCriteriaStaticValues = includes(criteriaName, staticCriteriaNames);

  if (isEmpty(criteriaName)) {
    return [];
  }

  if (includes(':', expressionBeforeCursor) && hasCriteriaStaticValues) {
    const criterias = getSelectableCriteriasByName(criteriaName);
    const lastCriteriaValue = last(expressionCriteriaValues) || '';

    const criteriaValueSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: expressionCriteriaValues,
    });

    const isLastValueInSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: [],
    }).includes(lastCriteriaValue);

    return isLastValueInSuggestions
      ? map(concat(','), criteriaValueSuggestions)
      : filter(startsWith(lastCriteriaValue), criteriaValueSuggestions);
  }

  return reject(includes(__, search), getCriteriaNameSuggestions(criteriaName));
};

export { parse, build, getAutocompleteSuggestions, searchableFields };
