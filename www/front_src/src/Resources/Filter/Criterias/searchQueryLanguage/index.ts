import {
  find,
  flip,
  head,
  includes,
  isEmpty,
  isNil,
  keys,
  map,
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
import getDefaultCriterias from '../default';

const isIn = flip(includes);

const criteriaKeys = keys(selectableCriterias) as Array<string>;

const isCriteriaPart = pipe(split(':'), head, isIn(criteriaKeys));

const parse = (search: string): Array<Criteria> => {
  const parts = search.split(' ');

  const [criteriaParts, rawSearchParts] = partition(isCriteriaPart, parts);

  const criterias: Array<Criteria> = criteriaParts.map((criteria) => {
    const [key, value] = criteria.split(':');

    const defaultCriteria = find(propEq('name', key), getDefaultCriterias());
    const objectType = defaultCriteria?.object_type || null;

    return {
      name: key,
      object_type: objectType,
      type: 'multi_select',
      value: value?.split(',').map((laGrosseValue) => {
        const [resourceId, resourceName] = laGrosseValue.split('|');

        const id = isNil(objectType) ? laGrosseValue : parseInt(resourceId, 10);
        const name = isNil(objectType)
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

  return [...defaultCriterias, ...criteriasWithSearch];
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

      const formattedValues = isNil(object_type)
        ? values.map(prop('id'))
        : values.map(({ id, name: valueName }) => `${id}|${valueName}`);

      return `${name}:${formattedValues.join(',')}`;
    })
    .join(' ');

  if (isEmpty(builtCriterias.trim())) {
    return search?.value as string;
  }

  return [builtCriterias, search?.value].join(' ');
};

export { parse, build };
