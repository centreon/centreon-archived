import {
  find,
  flip,
  head,
  includes,
  isEmpty,
  isNil,
  keys,
  partition,
  pipe,
  prop,
  propEq,
  propSatisfies,
  reject,
  split,
} from 'ramda';

import { SelectEntry } from '@centreon/ui';

import {
  Criteria,
  criteriaValueNameById,
  selectableCriterias,
} from '../models';

import { Criteria as ParsedCriteria, Search } from './models';

const isIn = flip(includes);

const criteriaKeys = keys(selectableCriterias) as Array<string>;

const isCriteriaPart = pipe(split(':'), head, isIn(criteriaKeys));

const parse = (search: string): Array<Criteria> => {
  const parts = search.split(' ');

  const [criteriaParts, rawSearchParts] = partition(isCriteriaPart, parts);

  // if (isNil(criteriaParts)) {
  //   return [[], ''];
  // }

  const criterias: Array<Criteria> = criteriaParts.map((criteria) => {
    const [key, value] = criteria.split(':');

    const retrievedCriteria = selectableCriterias[key];

    return {
      name: key,
      object_type: retrievedCriteria.options ? key : null,
      type: 'multi_select',
      value: value.split(',').map((id) => ({
        id,
        name: criteriaValueNameById[id],
      })),
    };
  });

  console.log(criterias);

  return [
    ...criterias,
    {
      name: 'search',
      object_type: null,
      type: 'string',
      value: rawSearchParts.join(' '),
    },
  ];
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
    .map(({ name, value }): string => {
      const val = value as Array<SelectEntry>;
      return `${name}:${val.map(prop('id')).join(',')}`;
    })
    .join(' ');

  return `${builtCriterias} ${search?.value}`;
};

export { parse, build };
