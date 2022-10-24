import {
  allPass,
  compose,
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
  __
} from 'ramda';
import pluralize from 'pluralize';

import { SelectEntry } from '@centreon/ui';

import {
  Criteria,
  criteriaValueNameById,
  selectableCriterias
} from '../models';
import getDefaultCriterias from '../default';

import {
  CriteriaId,
  criteriaNameToQueryLanguageName,
  criteriaNameSortOrder,
  CriteriaValueSuggestionsProps,
  AutocompleteSuggestionProps,
  getSelectableCriteriasByName
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
  isIn(selectableCriteriaNames)
);
const isFilledCriteria = pipe(endsWith(':'), not);

const parse = (search: string): Array<Criteria> => {
  const [criteriaParts, rawSearchParts] = partition(
    allPass([includes(':'), isCriteriaPart, isFilledCriteria]),
    search.split(' ')
  );

  const criterias: Array<Criteria> = criteriaParts.map((criteria) => {
    const [key, values] = criteria.split(':');
    const unmappedCriteriaKey = getCriteriaNameFromQueryLanguageName(key);
    const pluralizedKey = pluralize(unmappedCriteriaKey);

    return {
      name: pluralizedKey,
      value: values?.split(',').map((value) => {
        const id = getCriteriaNameFromQueryLanguageName(value);

        return {
          id,
          name: criteriaValueNameById[id]
        };
      })
    };
  });

  const criteriasWithSearch = [
    ...criterias,
    {
      name: 'search',
      value: rawSearchParts.join(' ').trim()
    }
  ];

  const toNames = map(prop('name'));
  const criteriaNames = toNames(criteriasWithSearch);

  const isInCriteriaNames = isIn(criteriaNames);

  const nameIsInCriteriaNames = pipe(({ name }) => name, isInCriteriaNames);

  const defaultCriterias = reject(nameIsInCriteriaNames, getDefaultCriterias());

  return sortBy(
    ({ name }) => criteriaNameSortOrder[name],
    [...defaultCriterias, ...criteriasWithSearch]
  );
};

const build = (criterias: Array<Criteria>): string => {
  const nameEqualsSearch = propEq('name', 'search');
  const hasEmptyValue = propSatisfies(isEmpty, 'value');

  const rejectSearch = reject(nameEqualsSearch);
  const rejectEmpty = reject(hasEmptyValue);

  const search = find(nameEqualsSearch, criterias);
  const regularCriterias = pipe(rejectSearch, rejectEmpty)(criterias);

  const builtCriterias = regularCriterias
    .filter(pipe(({ name }) => name, isNil, not))
    .map(({ name, value }): string => {
      const values = value as Array<SelectEntry>;

      const formattedValues = values.map(
        pipe(({ id }) => id as string, getCriteriaQueryLanguageName)
      );
      const criteriaName = compose(
        getCriteriaQueryLanguageName,
        singular
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
    selectableCriteriaNames
  );

  if (isEmpty(word)) {
    return [];
  }

  const suggestions = filter(startsWith(word), [...criteriaNames]);

  return map(concat(__, ':'), suggestions);
};

const getCriteriaValueSuggestions = ({
  selectedValues,
  criterias
}: CriteriaValueSuggestionsProps): Array<string> => {
  const criteriaNames = map<CriteriaId, string>(
    compose(getCriteriaQueryLanguageName, prop('id'))
  )(criterias);

  return without(selectedValues, criteriaNames);
};

const getIsNextCharacterEmpty = ({
  search,
  cursorPosition
}: AutocompleteSuggestionProps): boolean => {
  const nextCharacter = search[cursorPosition];

  return isNil(nextCharacter) || isEmpty(nextCharacter.trim());
};

interface CriteriaExpression {
  criteriaName: string;
  expressionBeforeCursor: string;
  expressionCriteriaValues: Array<string>;
}

const getCriteriaAndExpression = ({
  search,
  cursorPosition
}: AutocompleteSuggestionProps): CriteriaExpression => {
  const searchBeforeCursor = slice(0, cursorPosition + 1, search);
  const expressionBeforeCursor = pipe(
    trim,
    split(' '),
    last
  )(searchBeforeCursor) as string;

  const expressionCriteria = expressionBeforeCursor.split(':');
  const criteriaQueryLanguageName = head(expressionCriteria) as string;
  const criteriaName = getCriteriaNameFromQueryLanguageName(
    criteriaQueryLanguageName
  );
  const expressionCriteriaValues = pipe(last, split(','))(expressionCriteria);

  return {
    criteriaName,
    expressionBeforeCursor,
    expressionCriteriaValues
  };
};

const getAutocompleteSuggestions = ({
  search,
  cursorPosition
}: AutocompleteSuggestionProps): Array<string> => {
  const isNextCharacterEmpty = getIsNextCharacterEmpty({
    cursorPosition,
    search
  });

  if (isNil(cursorPosition) || !isNextCharacterEmpty) {
    return [];
  }

  const { criteriaName, expressionCriteriaValues, expressionBeforeCursor } =
    getCriteriaAndExpression({ cursorPosition, search });

  if (isEmpty(criteriaName)) {
    return [];
  }

  if (includes(':', expressionBeforeCursor)) {
    const criterias = getSelectableCriteriasByName(criteriaName);
    const lastCriteriaValue = last(expressionCriteriaValues) || '';

    const criteriaValueSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: expressionCriteriaValues
    });

    const isLastValueInSuggestions = getCriteriaValueSuggestions({
      criterias,
      selectedValues: []
    }).includes(lastCriteriaValue);

    return isLastValueInSuggestions
      ? map(concat(','), criteriaValueSuggestions)
      : filter(startsWith(lastCriteriaValue), criteriaValueSuggestions);
  }

  return reject(includes(__, search), getCriteriaNameSuggestions(criteriaName));
};

export { parse, build, getAutocompleteSuggestions };
